<?php if(!defined('ACCESS_CODE') || intval(ACCESS_CODE+ACCESS_CODE*ACCESS_CODE) != 6) die('Access Error');

class FIELDS {
	var $table;
	var $current;
	var $count;
	var $max_level;
	var $field = array();

	function __construct(){
		$this->table = new Table();
		$this->titles = new TITLES();
		$this->CountFields();
	}

	/**
	* возвращает количество полей
	*
	*/
	function CountFields(){
		$this->count = sizeof($this->field);
		return $this->count;
	}

	/**
	* Создает новое поле (новый объект класса)
	*
	*/
	function NewField(){
		$this->current = $this->CountFields();
		$this->field[$this->current] = new Field();
		$this->count++;
	}

	/**
	* Возвращает номер поля по имени поля
	*
	* @param mixed $name
	*/
	function get_num_type($name){
		$this->CountFields();
		for($i=0,$n=&$this->count;$i<$n;$i++){
			if($this->field[$i]->form->name == $name){
				return $i;
			}
		}
		return -1;
	}

	/**
	* проверяет на наличие значения
	*
	* @param mixed $var
	* @param mixed $val
	* @param mixed $param
	*/
	function field_like($var,$val,$param=false){
		$ar_var = explode('::',$var);
		if($param){
			foreach($ar_var as $var_val){
				if($ar_var == $val){
					return true;
				}
			}
		}elseif($ar_var[0] == $val){
			return true;
		}
		if(mb_strpos($var,$val) !== false){
			return true;
		}

		return false;
	}

	/**
	* возвращает номер поля по имени
	*
	* @param mixed $name
	* @param mixed $param
	* @param mixed $all
	*/
	function get_num_field($name,$param=false,$all=false){
		$this->CountFields();
		$ar_values = array();
		if(is_array($name)){
			$key = key($name);
			$value = $name[$key];
			$ar_key = explode('->',$key);
			$var_1 = null;
			if(!empty($ar_key[0])){
				$var_1 = (string)$ar_key[0];
			}
			$var_2 = null;
			if(!empty($ar_key[1])){
				$var_2 = (string)$ar_key[1];
			}
			$var_3 = null;
			if(!empty($ar_key[2])){
				$var_3 = (string)$ar_key[2];
			}
			$size = sizeof($ar_key);
			if($size == 2){
				for($i=0,$n=&$this->count;$i<$n;$i++){
					if($param){
						if($this->field_like($this->field[$i]->$var_1->$var_2,$value)){
							if($all){
								$ar_values[] = $i;
							}else{
								return $i;
							}
						}
					}elseif(mb_strtolower($this->field[$i]->$var_1->$var_2) == mb_strtolower($value)){
						if($all){
							$ar_values[] = $i;
						}else{
							return $i;
						}
					}
				}
			}elseif($size == 1){
				for($i=0,$n=&$this->count;$i<$n;$i++){
					if($param){
						if($this->field_like($this->field[$i]->$var_1->$var_2,$value)){
							if($all){
								$ar_values[] = $i;
							}else{
								return $i;
							}
						}
					}elseif(mb_strtolower($this->field[$i]->$var_1) == mb_strtolower($value)){
						if($all){
							$ar_values[] = $i;
						}else{
							return $i;
						}
					}
				}
			}elseif($size == 3){
				for($i=0,$n=&$this->count;$i<$n;$i++){
					if($param){
						if($this->field_like($this->field[$i]->$var_1->$var_2,$value)){
							if($all){
								$ar_values[] = $i;
							}else{
								return $i;
							}
						}
					}elseif(mb_strtolower($this->field[$i]->$var_1->$var_2->$var_3) == mb_strtolower($value)){
						if($all){
							$ar_values[] = $i;
						}else{
							return $i;
						}
					}
				}
			}else{
				return -2;
			}
			if(empty($ar_values)){
				return -3;
			}else{
				return $ar_values;
			}
		}else{
			for($i=0,$n=&$this->count;$i<$n;$i++){
				if(mb_strtolower($this->field[$i]->form->name) == mb_strtolower($name)){
					if($all){
						$ar_values[] = $i;
					}else{
						return $i;
					}
				}
			}
		}
		if(empty($ar_values)){
			return -3;
		}else{
			return $ar_values;
		}
	}

	/**
	* Возвращает значения элементов массива fields по значению
	*
	* @param mixed $path
	* @param mixed $val
	*/
	function GetValuesByValue($path,$val){
		$ar_path = explode('::',$path);
		$ar_val = explode('::',$val);
		$path2 = implode('->',$ar_path);
		$ar_i = array();
		for($i=0,$n=&$this->count;$i<$n;$i++){
			eval('$cur_val = $this->field['.$i.']->'.$path2.';');
			$ar_cur_val = explode('::',$cur_val);
			if($cur_val == $val || $ar_cur_val[0] == $ar_val[0]){
				$ar_i[] = $i;
				//echo($i."<br />");
			}
		}
		return $ar_i;
	}

	/**
	* очищает заданные элементы
	*
	*/
	function Clear(){
		unset($this->table);
		unset($this->field);
		unset($this->current);
		unset($this->count);
	}
}

class Table {
	var $name;
	var $title;
	var $multiline;
}

class Field {
	var $db;
	var $form;
	var $show;
	var $edit;
	var $params;
	var $access;

	function __construct(){
		$this->db = new DBField();
		$this->form = new FormField();
		$this->show = new FieldProperties();
		$this->edit = new FieldProperties();
		$this->params = new FieldParams();
		$this->access = new FieldAccess();
	}
}

class DBField {
	var $field;				// название поля в таблице
	var $type;				// тип поля
	var $length;			// длина поля
	var $values;			// значения если тип поля "set" или "enum"
	var $decimals;			// количество десятичных знаков
	var $default;			// значение по умолчанию
	var $attributes;		// (BINARY, UNSIGNED, UNSIGNED ZEROFILL, on update CURRENT_TIMESTAMP)
	var $null;				// NULL (да или нет)
	var $index;				// (PRIMARY, UNIQUE, INDEX, FULLTEXT)
	var $auto_increment;	// автоинкремент (да или нет)
	var $value;				// значение поля
}

class FormField {
	// описание полей формы
	var $name;		// имя
	var $type;		// тип элемента формы
	var $value;		// значение
	var $checked;	// выделен ли для checkbox
	var $selected;	// параметр выделенного элемента в select
	var $rows;		// количество строк
	var $cols;		// количество столбцов
	var $size;		// Ширина текстового поля.
	var $class;		// класс стиля
	var $title;		// заголовок
	var $maxlength;	// Максимальное количество символов разрешенных в тексте.
	var $id;		// идентификатор
	var $for;		// идентификатор к которому принадлежит данный элемент
	var $tabindex;	// Определяет порядок перехода между элементами с помощью клавиши Tab.
	var $disabled;	// Блокирует доступ и изменение элемента
	var $readonly;	// Устанавливает, что поле не может изменяться пользователем.
	var $multiple;	// Позволяет одновременно выбирать сразу несколько элементов списка.
	var $src;		// путь к файлу
	var $alt;		// описание картинки
	var $style;		// стили
	var $autocomplete;		// Автозаполнение поля input (on|off)
	var $step;		// Шаг
	var $max;		// Максимальное значение
	var $min;		// Минимальное значение

	function GetTagParams($class=''){
		if(empty($class)){
			$class_vars = get_object_vars($this);
		}else{
			$class_vars = get_object_vars($class);
		}

		$str = '';
		foreach ($class_vars as $name => $value) {
			if(empty($value)){
				unset($class_vars[$name]);
			}else{
				$str.= ' '.$name.'="'.$value.'"';
			}
		}
		return $str;
	}
}

class FieldProperties {
	// свойства поля для каждого режима
	var $view;		// просмотр
	var $add;		// добавление
	var $edit;		// редактирование
	var $delete;	// удаление

	var $align;		// выравнивание
	var $wrap;		// перенос текста
}

class FieldParams {
	var $label;			// текстовая метка
	var $desrciption;	// описание
	var $hint;			// подсказка
	var $mandatory;		// обязательность к заполнению поля (да, нет)
	var $filtering;		// фильтровать по этому полю (да, нет)
	var $export;		// экспортировать это поле или нет
}

class FieldAccess {
	var $full;		// Полный доступ
	var $view;		// Доступ на просмотр
	var $add;		// Доступ на добавление
	var $edit;		// доступ на редактирование
	var $delete;	// доступ на удаление
}

class TITLES {
	var $view;
	var $add;
	var $edit;
	var $delete;
}

?>