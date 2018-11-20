<?php if(!defined('ACCESS_CODE') || intval(ACCESS_CODE+ACCESS_CODE*ACCESS_CODE) != 6) die('Access Error');

class URL {
	private $url = RELATIVE;

	function __construct($base_url=''){
		if(empty($base_url)){
			if(isset($_SERVER['REQUEST_URI']) and strlen($_SERVER['REQUEST_URI']) > 1){
				$this->url = $_SERVER['REQUEST_URI'];
			}
		}elseif(strlen($base_url) > 1){
			$this->url = $base_url;
		}
	}

	/**
	* Получение строки GET-запроса.
	*
	*/
	function ggp() {
		preg_match('/^([^?]+)(\?.*?)?(#.*)?$/', $this->url, $matches);
		$gp = (isset($matches[2])) ? $matches[2] : '';
		return $gp;
	}

	/**
	* Исключение GET-запроса из URL.
	*
	* @param mixed $url
	* @return mixed
	*/
	function rgp($url) {
		return preg_replace('/^([^?]+)(\?.*?)?(#.*)?$/', '$1$3', $this->url);
	}

	/**
	* Замена содержимого GET-параметров.
	*
	* @param mixed $varname
	* @param mixed $value
	*/
	function SetValue($varname, $value) {
		$value = rawurlencode($value);
		if($this->GetValue($varname) != $value){
			if($this->url == RELATIVE){
				$this->url.= '?'.$varname.'='.$value;
			}else{
				preg_match('/^([^?]+)(\?.*?)?(#.*)?$/', $this->url, $matches);
				$gp = (isset($matches[2])) ? $matches[2] : ''; // GET-parameters
				if(isset($matches[2])){
					$gp = $matches[2];
					//if ($gp) return $this->url;
					$pattern = "/([?&])$varname=.*?(?=&|#|\z)/";
					if (preg_match($pattern, $gp)) {
						$substitution = ($value == '') ? '' : "\${1}$varname=" . preg_quote($value);
						$newgp = preg_replace($pattern, $substitution, $gp); // new GET-parameters
						$newgp = preg_replace('/^&/', '?', $newgp);
					}else{
						$s = ($gp) ? '&' : '?';
						$newgp = $gp.$s.$varname.'='.$value;
					}
					$anchor = (isset($matches[3])) ? $matches[3] : '';
					$newurl = $matches[1].$newgp.$anchor;
					$this->url = $newurl;
				}else{
					$this->url.= '?'.$varname.'='.$value;
				}
			}
		}
		//return $newurl;
	}

	/**
	* Получение значения переменной $varname
	*
	* @param mixed $varname
	* @return string
	*/
	function GetValue($varname){
		global $DB;
		$arr = parse_url($this->url);
		if (isset($arr['query'])){
			if (preg_match('/'.$varname.'=(.*)(&|$)/U', $arr['query'], $found)){
				return $DB->real_escape_string($found[1]);
			}
		}
		return false;
	}

	/**
	* Получение URL
	*
	* @param mixed $special
	* @return string
	*/
	function GetURL($special=true){
		if($special){
			return htmlspecialchars($this->url);
		}else{
			return $this->url;
		}
	}

	/**
	* Установить URL по умолчанию
	*
	* @param mixed $url
	*/
	function SetURL($url){
		$this->url = $url;
		if(mb_substr($this->url,0,1) != '/'){
			$this->url = '/'.$this->url;
		}
	}
}

?>