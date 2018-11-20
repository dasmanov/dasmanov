<?
final class FileReader {
	protected $handler = null;
	protected $fbuffer = array();
	protected $count_rows = 0;
	protected $exception = false;

	/**
	* Конструктор класса, открывающий файл для работы
	*
	* @param string $filename
	*/
	public function __construct($filename){
		if (!($this->handler = fopen($filename, "rb"))){
			$this->exception('Cannot open the file '.$filename);
			return false;
		}
	}

	/**
	* Построчное чтение $count_line строк файла с учетом сдвига
	*
	* @param mixed $count_line
	* @param mixed $begin_line
	* @param mixed $continue_read_wthout_begin_line
	*/
	public function Read($count_line = 10, $begin_line = 0,$continue_read_wthout_begin_line = false){
		if($this->exception){
			return false;
		}
		if ($this->handler) {
			$count_line = intval($count_line);
			$begin_line = intval($begin_line);
			if ($begin_line > 0 and empty($continue_read_wthout_begin_line)) {
				$this->SetOffset($begin_line);
			}
			if(!empty($continue_read_wthout_begin_line)){
				$this->fbuffer = array();
			}
			while (!feof($this->handler)) {
				$this->fbuffer[] = fgets($this->handler);
				$count_line--;
				if ($count_line == 0) break;
			}

			return $this->fbuffer;
		}else{
			$this->exception('Invalid file pointer');
			return false;
		}
	}

	/**
	* Установить строку, с которой производить чтение файла
	*
	* @param int $line
	*/
	public function SetOffset($line = 0){
		if($this->exception){
			return false;
		}
		if ($this->handler){
			while (!feof($this->handler) && $line--) {
				fgets($this->handler);
			}
		}else{
			$this->exception('Invalid file pointer');
			return false;
		}
	}

	/**
	* Закрывает открытый файл
	*/
	public function Close(){
		fclose($this->handler);
	}

	/**
	* Очистка буффера строк
	*/
	public function ClearBuffer(){
		$this->fbuffer = array();
	}

	/**
	* Возвращает количество строк в файле
	* @param int $rows_offset
	* @throws Exception
	*/
	public function CountRows($rows_offset = 0){
		if($this->exception){
			return false;
		}
		if ($this->handler){
			if(!empty($rows_offset)){
				$this->SetOffset($rows_offset);
			}
			while (!feof($this->handler)) {
				fgets($this->handler);
				$this->count_rows++;
			}
			return $this->count_rows;
		}else{
			$this->exception('Invalid file pointer');
			return false;
		}
	}

	/**
	* Сбросить указатель на начало
	*/
	public function Reset(){
		rewind($this->handler);
	}

	/**
	* вывод исключительной ситуации
	*
	* @param mixed $msg
	*/
	private function exception($msg){
		$this->exception = true;
		echo $msg.'<br>';
	}

	/**
	* возвращает true в случае успешного считывания файла
	*
	*/
	public function readable(){
		if($this->exception){
			return false;
		}
		return true;
	}

	/**
	* Вытаскиваем содержимое CSV строки
	*
	* @param mixed $string
	* @param mixed $separator
	*/
	function getCSVValues($string, $separator=","){
		$elements = explode($separator, $string);
		for ($i = 0; $i < sizeof($elements); $i++) {
			$nquotes = mb_substr_count($elements[$i], '"');
			if ($nquotes %2 == 1) {
				for ($j = $i+1; $j < sizeof($elements); $j++) {
					if (mb_substr_count($elements[$j], '"') %2 == 1) { // Look for an odd-number of quotes
						// Put the quoted string's pieces back together again
						array_splice($elements, $i, $j-$i+1,
							implode($separator, array_slice($elements, $i, $j-$i+1)));
						break;
					}
				}
			}
			if ($nquotes > 0) {
				// Remove first and last quotes, then merge pairs of quotes
				$qstr =& $elements[$i];
				$qstr = mb_substr_replace($qstr, '', mb_strpos($qstr, '"'), 1);
				$qstr = mb_substr_replace($qstr, '', mb_strrpos($qstr, '"'), 1);
				$qstr = mb_str_replace('""', '"', $qstr);
			}
		}
		return $elements;
	}
}
?>