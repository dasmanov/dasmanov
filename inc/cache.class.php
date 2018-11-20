<?php
class Cache {
	private $_salt = "RANDOM TEXT HERE"; // <-- change before usage!
	private $default_dir = '/dasmanov/cache/data/';
	private $_name;
	private $_dir;
	private $_extension;
	private $_path;

	public function __construct($_salt='', $name = "default", $dir = "", $extension = ".cache"){
		if(mb_strlen($dir) > 0){
			if($dir[0] != '/'){
				$dir = '/'.$dir;
			}
		}
		$dir = $this->default_dir.$dir;
		if(empty($_salt)){
			if(isset($_SERVER['SERVER_NAME'])){
				$this->_salt = $_SERVER['SERVER_NAME'];
			}else{
				$this->_salt = 'CRON';
			}
		}else{
			$this->_salt = $_salt;
		}
		if(md5($this->_salt) == "233abed7ee9945c0429047405d864283") throw new Exception("Change _salt value before usage! (line 5)");
		if($name == null) throw new Exception("Invalid name argument (empty or null)");
		if($dir == null) throw new Exception("Invalid dir argument (empty or null)");
		if($extension == null) throw new Exception("Invalid extension argument (empty or null)");
		$dir = str_replace("\\", "/", $dir);
		if(!$this->endsWith($dir, "/"))
		{
			$dir .= "/";
		}
		/*if($dir[0] != '/'){
		$dir = '/'.$dir;
		}*/

		$dir = $_SERVER['DOCUMENT_ROOT'].$dir;
		$this->_name = $name;
		$this->_dir = $dir;
		$this->_extension = $extension;
		$this->_path = $this->getPath();
		//file_put_contents($_SERVER['DOCUMENT_ROOT'].'/cache.log','$this->_salt = '.$this->_salt."\n".'$this->_name = '.$this->_name."\n".'$this->_dir = '.$this->_dir."\n".'$this->_extension = '.$this->_extension."\n".'$this->_path = '.$this->_path."\n"."\n",FILE_APPEND);
		$this->checkDir();
	}

	/**
	* Устанавливает значение ключа кеша
	*
	* @param mixed $key
	* @param mixed $value
	* @param mixed $ttl
	*/
	public function set($key, $value, $ttl = -1){
		$data = [
			"t" => time(),
			"e" => $ttl,
			"v" => serialize($value),
		];
		$cache = $this->getCache();
		if($cache == null)
		{
			$cache = [
				$key => $data,
			];
		}
		else
		{
			$cache[$key] = $data;
		}
		$this->setCache($cache);
	}

	/**
	* получает значение ключа из кеша
	*
	* @param mixed $key
	* @param mixed $out
	*/
	public function get($key, &$out){
		$cache = $this->getCache();
		if(!is_array($cache)) return false;
		if(!array_key_exists($key, $cache)) return false;
		$data = $cache[$key];
		if($this->isExpired($data))
		{
			unset($cache[$key]);
			$this->setCache($cache);
			return false;
		}
		$out = unserialize($data["v"]);
		/*if($_SERVER['REMOTE_ADDR'] == '83.149.46.253'){
		echo $this->_path.'<br>';
		echo("<pre>");print_r($cache);echo("</pre>");
		}*/
		return true;
	}

	/**
	* удаляет значение ключа из кеша
	*
	* @param mixed $key
	* @param mixed $regular
	*/
	public function remove($key,$regular=false,$dontwrite = false){
		$cache = $this->getCache();
		if(empty($regular)){
			if(!is_array($cache)) return false;
			if(!array_key_exists($key, $cache)) return false;
			unset($cache[$key]);
		}else{
			if(is_array($cache)){
				foreach($cache as $key_item=>&$cache_item){
					if(preg_match('~'.preg_quote($key).'~isu',$key_item)){
						$this->remove($key_item,false,true);
					}
				}
			}
		}
		/*if($_SERVER['REMOTE_ADDR'] == '83.149.46.253'){
		echo $this->_path.'<br>';
		echo("<pre>");print_r($cache);echo("</pre>");
		}*/
		if(empty($dontwrite)){
			$this->setCache($cache);
		}
		return true;
	}

	/**
	* проверяет кеш на актуальность по дате
	*
	* @param mixed $data
	*/
	private function isExpired($data){
		if($data["e"] == -1) return false;
		$expiresOn = $data["t"] + $data["e"];
		return $expiresOn < time();
	}

	/**
	* Сохраняет весь кеш в файл
	*
	* @param mixed $json
	*/
	private function setCache($json){
		//if(!is_array($json)) throw new Exception("Invalid cache (not an array?)");
		$content = json_encode($json,JSON_UNESCAPED_UNICODE);
		file_put_contents($this->_path, $content);
	}

	/**
	* считывает весь кеш из файла
	*
	*/
	private function getCache(){
		if(!file_exists($this->_path)) return null;
		$content = file_get_contents($this->_path);
		return json_decode($content, true);
	}

	/**
	* Получает путь к файлу хранения кеша
	*
	*/
	private function getPath(){
		return $this->_dir . md5($this->_name . $this->_salt) . $this->_extension;
	}

	/**
	* проверяет доступность директории для записи или создает директорию для хранения кеша
	*
	*/
	private function checkDir(){
		if(!is_dir($this->_dir) && !mkdir($this->_dir, 0775, true))
		{
			throw new Exception("Unable to create cache directory ($this->_dir)");
		}
		if(!is_readable($this->_dir) || !is_writable($this->_dir))
		{
			if(!chmod($this->_dir, 0775))
			{
				throw new Exception("Cache directory must be readable and writable ($this->_dir)");
			}
		}
		return true;
	}

	/**
	* Проверяет на наличие последовательности символов $needle в начале строки $haystack
	*
	* @param mixed $haystack
	* @param mixed $needle
	*/
	private function startsWith($haystack, $needle){
		$length = strlen($needle);
		return (substr($haystack, 0, $length) === $needle);
	}

	/**
	* Проверяет на наличие последовательности символов $needle в конце строки $haystack
	*
	* @param mixed $haystack
	* @param mixed $needle
	*/
	private function endsWith($haystack, $needle){
		$length = strlen($needle);
		return $length === 0 || (substr($haystack, -$length) === $needle);
	}
	
	/**
	* Очистка всего кеша
	* 
	*/
	public function clear(){
		return DeleteFromDir($this->_dir);
	}
}