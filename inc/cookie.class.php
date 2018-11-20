<?php
abstract class Cookie {
	/**
	* запоменает переменную
	*
	* @param mixed $key
	* @param mixed $value
	* @param mixed $time
	*/
	public static function set($key, $value, $time = 31536000){
		setcookie($key, $value, time() + $time, '/') ;
	}

	/**
	* считывает переменную
	*
	* @param mixed $key
	*/
	public static function get($key){
		if ( isset($_COOKIE[$key]) ){
			return $_COOKIE[$key];
		}
		return null;
	}

	/**
	* удаляет переменную
	*
	* @param mixed $key
	*/
	public static function delete($key){
		if ( isset($_COOKIE[$key]) ){
			self::set($key, '', 1);
			unset($_COOKIE[$key]);
		}
	}

	/**
	* удалить все куки
	*
	*/
	public static function delete_all($pre_var=''){
		if(empty($pre_var)){
			foreach($_COOKIE as $key => $value ){
				self::delete($key);
			}
		}else{
			foreach($_COOKIE as $key => $value ){
				if(preg_match('~^'.$pre_var.'(.*)~isu',$key)){
					self::delete($key);
				}
			}
		}
	}
}
?>
