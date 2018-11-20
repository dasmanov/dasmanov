<?php if(!defined('ACCESS_CODE') || intval(ACCESS_CODE+ACCESS_CODE*ACCESS_CODE) != 6) die('Access Error');

class POST {
	private $array = array();
	private $count = 0;

	function __construct(){
		if(!empty($_POST)){
			$this->array = $_POST;
			$this->clean();
			$_SESSION['post'] = $this->array;
			$this->count = sizeof($this->array);
		}
		//echo("<pre>");print_r($_POST);echo("</pre>");
		//unset($_POST);
	}

	/**
	* возвращает количество значений
	*
	*/
	function GetCount(){
		return $this->count;
	}

	/**
	* Возвращает значение ключа $var
	*
	* @param mixed $var
	*/
	function GetValue($var){
		if($this->IsSetVar($var)){
			return $this->array[$var];
		}
		return false;
	}

	/**
	* устанавливает значение ключа
	*
	* @param mixed $var
	* @param mixed $val
	*/
	function SetValue($var,$val){
		$this->array[$var] = $val;
	}

	/**
	* Проверяет существование переменной
	*
	* @param mixed $var
	*/
	function IsSetVar($var){
		if(isset($this->array[$var])){
			return true;
		}
		return false;
	}

	/**
	* Если не пустое значение ключа $var, то возвращает true
	*
	* @param mixed $var
	*/
	function noempty($var){
		if(!empty($this->array[$var])){
			return true;
		}
		return false;
	}

	/**
	* Выводит текущий массив данных POST запроса
	*
	*/
	function _print(){
		echo("<pre>");print_r($this->array);echo("<pre>");
	}

	/**
	* Сохраняет в сессию весь массив данных POST запроса
	*
	*/
	function CopyToSession(){
		$_SESSION['post'] = $this->array;
	}

	/**
	* Возвращает из сессии сохраненный ранее массив данных POST запроса
	*
	*/
	function CopyFromSession(){
		if(!isset($_SESSION['post'])){
			$_SESSION['post'] = null;
		}
		$this->array = $_SESSION['post'];
	}

	/**
	* Очищает в сессиии массив данных POST запроса
	*
	*/
	function ClearFromSession(){
		unset($_SESSION['post']);
	}

	/**
	* обеззараживает входные данные
	*
	* @param mixed $string
	* @param mixed $flags
	* @param mixed $doubleEncode
	*/
	function htmlspecialcharsdas($string, $flags = ENT_COMPAT, $doubleEncode = true){
		//function for php 5.4 where default encoding is UTF-8
		return htmlspecialchars($string, $flags, "UTF-8", $doubleEncode);
	}

	/**
	* очищает массив входных данных от опасного содержимого
	*
	*/
	function clean(){
		$output_var = array();
		foreach($this->array as $vname=>$vvalue){
			if(!is_array($vvalue)){
				$vvalue = urldecode($vvalue);
				$output_var[$this->htmlspecialcharsdas($vname)] = $this->htmlspecialcharsdas($vvalue);
			}else{
				foreach($vvalue as $k1 => $v1){
					if(is_array($v1)){
						foreach($v1 as $k2 => $v2){
							if(!is_array($v2)){
								$v2 = urldecode($v2);
								$output_var[$this->htmlspecialcharsdas($vname)][$this->htmlspecialcharsdas($k1)][$this->htmlspecialcharsdas($k2)] = $this->htmlspecialcharsdas($v2);
							}
						}
					}else{
						$v1 = urldecode($v1);
						$output_var[$this->htmlspecialcharsdas($vname)][$this->htmlspecialcharsdas($k1)] = $this->htmlspecialcharsdas($v1);
					}
				}
			}
		}
		$this->array = $output_var;
	}
}
?>