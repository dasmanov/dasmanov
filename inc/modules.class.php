<?php if(!defined('ACCESS_CODE') || intval(ACCESS_CODE+ACCESS_CODE*ACCESS_CODE) != 6) die('Access Error');

class MODULES {
	var $count = 0;
	var $dirs = array();					// список папок модулей
	private $file_module = "settings.php";	// файл для подключения модуля
	private $folder_modules;				// папка где расположены модули
	private $excepts = array('.','..');		// исключения (текущий, выше)
	private $level=1;						// максимальный уровень вложенности папки модуля
	private $current;						// текущий элемент
	private $get_cur;
	private $cur_sql_params;
	var $module = array();
	var $DB;

	function __construct($folder){
		//$this->folder_modules = $this->PathCorrect($folder);
		$this->folder_modules = $folder;

		$this->Scan();
		for($i=0,$n=$this->count;$i<$n;$i++){
			$this->module[$i] = new Module();
			$folder = $this->dirs[$i].'/';
			$f_css = $folder.'css/';
			$f_js = $folder.'js/';
			$f_inc = $folder.'inc/';

			$this->module[$i]->name = $this->get_name($folder);
			$this->module[$this->module[$i]->name] = &$this->module[$i];
			$this->module[$i]->path->folder = $folder;
			$this->module[$i]->path->setting = $folder.$this->file_module;
			$this->module[$i]->path->css = $this->FolderScan($f_css,'css');
			$this->module[$i]->path->js = $this->FolderScan($f_js,'js');
			$this->module[$i]->path->inc = $this->FolderScan($f_inc,'php');
		}
		$this->unset_all();
	}

	/**
	* возвращает текущее название модуля
	*
	*/
	function GetCurrnet(){
		$URL = new URL();
		return $URL->GetValue('module');
	}

	/**
	* Инициализация всех модулей
	*
	*/
	function Init(){
		for($i=0,$n=$this->count;$i<$n;$i++){
			$this->current = $i;
			$this->IncludeSettings($this->module[$i]->path->setting);
			$this->IncludeInc($this->module[$i]->path->inc);
		}
	}

	/**
	* сканирование доступных модулей
	*
	*/
	private function Scan(){
		$this->ScanDirs();
		$this->count = sizeof($this->dirs);
		//	return $this->dirs;
	}

	/**
	* корректировка пути
	*
	* @param string $path
	* @return string
	*/
	private function PathCorrect($path){
		$array = explode('/',$path);
		$array = $this->ArrayDeleteEmpty($array);
		$path = implode('/',$array);
		if(!in_array($array[0],$this->excepts)){
			$path = './'.$path;
		}
		return $path;
	}

	/**
	* Удаление пустых элементов
	*
	* @param mixed $array
	* @return string
	*/
	private function ArrayDeleteEmpty($array){
		$ret_arr = array();
		foreach($array as $val){
			if (!empty($val)){
				$ret_arr[] = trim($val);
			}
		}
		return $ret_arr;
	}

	/**
	* сканирует папку с неограниченным уровнем вложенности на предмет нахождения модуля
	*
	* @param mixed $path
	* @param mixed $level
	*/
	private function ScanDirs($path='',$level=0){
		// сканирует папку с неограниченным уровнем вложенности на предмет нахождения модуля
		$level++;
		if(empty($path)){
			$path = $this->folder_modules;
		}
		$dirs = scandir($path);
		foreach($dirs as $value){
			if(!in_array($value,$this->excepts)){
				$cur_path = $path.'/'.$value;
				clearstatcache();
				if(is_dir($cur_path)){
					if($level <= $this->level){
						$this->ScanDirs($cur_path,$level);
					}
				}elseif(is_file($cur_path)){
					if($level > $this->level){
						if($value == $this->file_module){
							$this->dirs[] = $path;
						}
					}
				}
			}
		}
	}

	/**
	* сканирует папку $folder и возвращает список файлов (с путем) заданного типа $ext, в противном случае false
	*
	* @param mixed $folder
	* @param mixed $ext
	*/
	private function FolderScan($folder,$ext){
		$array = array();
		clearstatcache();
		if(is_dir($folder)){
			$files = scandir($folder);
			foreach($files as $value){
				$file = $folder.$value;
				clearstatcache();
				if(is_file($file)){
					if($this->is_ext($file,$ext)){
						$array[] = $file;
					}
				}
			}
			return $array;
		}
		return FALSE;
	}

	/**
	* проверяет, является ли заданный файл $file заданного типа $ext, возвращает (true,false)
	*
	* @param mixed $file
	* @param mixed $ext
	*/
	private function is_ext($file,$ext){
		$array = explode(".",$file);
		$count = sizeof($array);
		if(is_array($ext)){
			if(in_array($array[$count-1],$ext)){
				return TRUE;
			}
		}elseif($array[$count-1] == $ext){
			return TRUE;
		}
		return FALSE;
	}

	/**
	* Возвращает название папки (модуля) из пути
	*
	* @param mixed $path
	* @return mixed
	*/
	private function get_name($path){
		$array = explode('/',$path);
		$array = $this->ArrayDeleteEmpty($array);
		$count = sizeof($array);
		if($count > 0){
			return $array[$count-1];
		}
		return FALSE;
	}

	/**
	* Очистка заданных переменных
	*
	*/
	private function unset_all(){
		unset($this->dirs);
		unset($this->excepts);
		unset($this->file_module);
		unset($this->folder_modules);
		unset($this->level);
	}

	/**
	* Возвращает номер модуля в массивве по имени
	*
	* @param mixed $name
	*/
	function get_num($name){
		for($i=0,$n=&$this->count;$i<$n;$i++){
			if($this->module[$i]->name == $name){
				return $i;
			}
		}
		return -1;
	}

	/**
	* Инсталирует модуль
	*
	* @param mixed $cur_fields
	*/
	private function Install($cur_fields){
		$name_table = &$cur_fields->table->name;
		if($this->create_table($cur_fields)){
			$this->Message("Таблица &quot;".$name_table."&quot; успешно создана.",'success');
			if($this->init_table($cur_fields)){
				$this->Message("Начальные данные в таблицу &quot;".$name_table."&quot; успешно внесены.",'success');
			}else{
				$this->Message("Таблица &quot;".$name_table."&quot; пустая.",'warning');
				return false;
			}
			return true;
		}else{
			$this->Message("Таблица &quot;".$name_table."&quot; не создана.",'warning');
		}
		return false;
	}

	/**
	* Деинсталяция модуля
	*
	*/
	private function uninstall(){
		$name_table = &$this->module[$this->current]->fields->table->name;
		if($this->drop_table()){
			$this->Message("Таблица &quot;".$name_table."&quot; удалена.",'success');
			return true;
		}else{
			$this->Message("Таблица &quot;".$name_table."&quot; не удалена.",'warning');
			return false;
		}
	}

	/**
	* проверяет существование таблицы
	*
	* @param mixed $table
	* @param mixed $mode
	*/
	private function isset_table($table,$mode = 0){
		if($mode == 0 && $this->DB->Query("SELECT * FROM `".$table."` LIMIT 1;")){
			return true;
			//}elseif(mysql_num_rows($this->DB->Query("SHOW TABLES LIKE `".$table."`;"))) {
			//	return true;
		}
		return false;
	}

	/**
	* Создает таблицу модуля
	*
	* @param mixed $cur_fields
	*/
	private function create_table($cur_fields){
		$name_table = &$cur_fields->table->name;
		$field = &$cur_fields->field;
		$count = $cur_fields->count;
		if(!empty($name_table)){
			$two = false;
			$j = 0;
			$sql = 'CREATE TABLE `'.$name_table.'` (';
			for($i=0; $i < $count; $i++){
				if(!empty($field[$i]->db->field)){
					if($two){
						$sql.= ', ';
					}else{
						$j=$i;
					}
					$sql.= '`'.$field[$i]->db->field.'` ';
					$sql.= $this->field_type($field[$i]->db);
					$two = true;
				}
			}
			if($two){
				if(empty($this->cur_sql_params['PRIMARY_KEY'])){
					$name_index = &$field[$j]->db->field;
				}else{
					$name_index = $this->cur_sql_params['PRIMARY_KEY'];
				}
				$sql.= ", PRIMARY KEY (`".$name_index."`)";

				if(isset($this->cur_sql_params['UNIQUE'])){
					$unique = $this->cur_sql_params['UNIQUE'];
					if(is_array($unique)){
						for($i=0,$n=sizeof($unique); $i < $n; $i++){
							$sql.= ", UNIQUE KEY `".$unique[$i]."` (`".$unique[$i]."`)";
						}
					}
				}
			}
			$sql.= ') DEFAULT CHARSET='.$this->DB->enc.';';
			$this->cur_sql_params = '';
			if($this->DB->Query($sql)){
				return true;
			}
		}
		return false;
	}

	/**
	* Удаляет таблицу модуля
	*
	*/
	private function drop_table(){
		$name_table = &$this->module[$this->current]->fields->table->name;
		$sql = "DROP TABLE `".$name_table."`;";
		if($this->DB->Query($sql)){
			return true;
		}
		return false;
	}

	/**
	* Добавляет начальные данные в таблицу модуля (одна строка)
	*
	* @param mixed $cur_fields
	*/
	private function init_table($cur_fields){
		$name_table = &$cur_fields->table->name;
		$field = &$cur_fields->field;
		$count = &$cur_fields->count;
		if(!empty($name_table)){
			$vars = array();
			$values = array();
			$init = false;
			for($i=0; $i < $count; $i++){
				if(!empty($field[$i]->db->field)){
					$vars[] = $field[$i]->db->field;
					$values[] = $field[$i]->db->value;
					if(!$init){
						if(!empty($field[$i]->db->value)){
							$init = true;
						}
					}
				}
			}
			if($init){
				$sql = "INSERT INTO `".$name_table."` (`".implode("`,`",$vars)."`) ";
				$sql.= "VALUES ('".implode("','",$values)."');";
				if($this->DB->Query($sql)){
					return true;
				}
			}
		}
		return false;
	}

	/**
	* корректирует таблицу (добавляет или удаляет поле)
	*
	* @param mixed $name_table
	* @param mixed $fields
	*/
	private function table_correct($name_table,$fields){
		$sizeof_fields = $fields->count;
		if($sizeof_fields > 1 && $name_table != ''){
			$table_fields_name = $this->DB->list_fields($name_table);
			//$table_fields = mysql_list_fields($this->DB->name, $name_table);
			//$columns = mysql_num_fields($table_fields);

			//$table_fields_name = array();
			//for ($i = 0; $i < $columns; $i++) {
			//	$table_fields_name[$i] = strtolower(mysql_field_name($table_fields, $i));
			//}

			$fields_add = array();
			$fields_del = array();

			$k=0;
			for($i=0; $i < $sizeof_fields; $i++){
				$field = &$fields->field[$i]->db->field;
				if($field){
					$field_lower = strtolower($field);
					if(!in_array($field_lower,$table_fields_name)){
						$fields_add[$k]['name'] = $field;
						$fields_add[$k]['num'] = $i;
						$k++;
					}
				}
			}

			$sizeof_table_fields_name = sizeof($table_fields_name);
			for($i=0; $i < $sizeof_table_fields_name; $i++){
				$one = true;
				for($j=0; $j < $sizeof_fields; $j++){
					if(strtolower($table_fields_name[$i]) == strtolower($fields->field[$j]->db->field)){
						$one = false;
						break;
					}
				}
				if($one){
					$fields_del[] = $table_fields_name[$i];
				}
			}

			$sizeof_fields_add = sizeof($fields_add);
			for($i = 0; $i < $sizeof_fields_add; $i++){
				$field_add = $fields_add[$i]['name'];
				$field_add_num = $fields_add[$i]['num'];

				$sql = "ALTER TABLE `".$name_table."` ";
				$sql.= "ADD `".$field_add."` ".$this->field_type($fields->field[$field_add_num]->db)." ";
				if($field_add_num > 0){
					$sql.= "AFTER `".$fields->field[$field_add_num-1]->db->field."` ";
				}else{
					$sql.= "FIRST ";
				}

				if($this->DB->Query($sql)){
					$msg = 'Успех: в таблице <b>'.$name_table.'</b> столбец <b>'.$field_add.'</b> добавлен!';
					$this->Message($msg,'success');
				}else{
					$msg = 'Ошибка: в таблице <b>'.$name_table.'</b> столбец <b>'.$field_add.'</b> не добавлен!';
					$this->Message($msg,'error');
				}
			}

			foreach($fields_del as $field_del){
				$sql = "ALTER TABLE `".$name_table."` DROP `".$field_del."`;";
				if($this->DB->Query($sql)){
					$msg = 'Успех: в таблице <b>'.$name_table.'</b> столбец <b>'.$field_del.'</b> удалён!';
					$this->Message($msg,'success');
				}else{
					$msg = 'Ошибка: в таблице <b>'.$name_table.'</b> столбец <b>'.$field_del.'</b> не удалён!';
					$this->Message($msg,'error');
				}
			}
		}
	}

	/**
	* Подключение настроек модуля, их установка и корректировка если требуется
	*
	* @param mixed $path
	*/
	private function IncludeSettings($path){
		include($path);
		if(defined('UPDATE_CONFIGURATION') && UPDATE_CONFIGURATION > 0){
			$cur_fields = &$this->module[$this->current]->fields;
			$table = $cur_fields->table->name;
			if(empty($table)){
				// нет названия таблицы
			}elseif($this->isset_table($table)){
				// таблица существует
				$this->table_correct($table,$cur_fields);
			}else{
				$this->Install($cur_fields);
			}

			$cur_fields = &$this->module[$this->current]->categories;
			$table = $cur_fields->table->name;
			if(empty($table)){
				// нет названия таблицы
			}elseif($this->isset_table($table)){
				// таблица существует
				$this->table_correct($table,$cur_fields);
			}else{
				$this->Install($cur_fields);
			}
		}
	}

	/**
	* Подключает дополнительные файлы
	*
	* @param mixed $path
	*/
	private function IncludeInc($path){
		if($path){
			if(is_array($path)){
				foreach($path as $inc){
					include($inc);
				}
			}else{
				include($path);
			}
		}
	}

	/**
	* выводит сообщение заданного типа
	*
	* @param mixed $text
	* @param mixed $type
	*/
	static function Message($text,$type=''){
		ob_start();
		switch($type){
			case '':
			case '0':
			case 'empty':
				echo $text;
				break;

			case '1':
			case 'success':
				?><div class="message_success"><?=$text;?></div><?
				break;

			case '2':
			case 'error':
				?><div class="message_error"><?=$text;?></div><?
				break;

			case '3':
			case 'warning':
				?><div class="message_warning"><?=$text;?></div><?
				break;

			default:
				?><div class="message"><?=$text;?></div><?
				break;
		}
		$_REQUEST['MESSAGES'].= ob_get_contents();
		ob_get_clean();
	}

	/**
	* возвращает тип поля в виде строки для SQL запроса на добавление
	*
	* @param mixed $db
	* @return string
	*/
	private function field_type($db){
		$sql = array(); $i=0;

		$attributes = false;
		$decimals = false;
		$length = false;

		$db->type = strtoupper($db->type);
		switch($db->type){
			case 'TINYINT':
			case 'SMALLINT':
			case 'MEDIUMINT':
			case 'INT':
			case 'BIGINT':
				$sql[$i++] = $db->type;
				$attributes = true;
				$length = true;
				break;

			case 'FLOAT':
			case 'DOUBLE':
			case 'REAL':
			case 'DECIMAL':
			case 'NUMERIC':
				$sql[$i++] = $db->type;
				$length = true;
				$decimals = true;
				$attributes = true;
				break;

			case 'CHAR':
			case 'VARCHAR':
				$sql[$i++] = $db->type;
				$length = true;
				$attributes = true;
				break;

			case 'TINYTEXT':
			case 'TEXT':
			case 'MEDIUMTEXT':
			case 'LONGTEXT':
				$sql[$i++] = $db->type;
				$attributes = true;
				break;

			case 'TINYBLOB':
			case 'BLOB':
			case 'MEDIUMBLOB':
			case 'LONGBLOB':
				$sql[$i++] = $db->type;
				break;

			case 'DATETIME':
			case 'TIME':
				$sql[$i++] = $db->type;
				break;
		}

		if($length){
			if(!empty($db->length)){
				$sql[--$i].= '('.$db->length;
				if($decimals){
					if(!empty($db->decimals)){
						$sql[$i].= ','.$db->decimals;
					}
				}
				$sql[$i++].= ')';
			}
		}

		if($attributes){
			$db->attributes = strtoupper($db->attributes);
			switch($db->attributes){
				case 'BINARY':
				case 'UNSIGNED':
				case 'UNSIGNED ZEROFILL':
				case 'ON UPDATE CURRENT_TIMESTAMP':
					$sql[$i++] = $db->attributes;
					break;
			}
		}

		if($this->is_false($db->null)){
			$sql[$i++] = 'NOT NULL';
		}else{
			$sql[$i++] = 'NULL';
		}

		if($this->is_true($db->auto_increment)){
			$sql[$i++] = 'AUTO_INCREMENT';
		}

		$db->index = strtoupper($db->index);
		switch($db->index){
			case 'PRIMARY':
				if(empty($this->cur_sql_params['PRIMARY_KEY'])){
					$sql[$i++] = 'PRIMARY KEY';
					$this->cur_sql_params['PRIMARY_KEY'] = &$db->field;
				}
				break;

			case 'UNIQUE':
				$this->cur_sql_params['UNIQUE'][] = &$db->field;
				break;
		}

		$sql = implode(' ',$sql);

		return $sql;
	}

	/**
	* является ли значение переменной не пустой
	*
	* @param mixed $var
	*/
	private function is_true($var){
		if($var !== FALSE && !empty($var)){
			return TRUE;
		}
		return FALSE;
	}

	/**
	* является ли значение переменной пустой
	*
	* @param mixed $var
	*/
	private function is_false($var){
		if($var === FALSE || !empty($var)){
			return TRUE;
		}
		return FALSE;
	}
}

class Module {
	var $name;
	var $title;
	var $path;
	var $fields;
	var $categories;
	private $max_rows = 1000;				// максимально число строк для вывода списка

	/**
	* Инициализация класса
	*
	*/
	function __construct(){
		$this->path = new ModulePaths();
		$this->fields = new FIELDS();
		$this->categories = new FIELDS();
	}

	/**
	* Выводит содержимое модуля (таблицы)
	*
	* @param mixed $cat
	*/
	function View($cat=0){
		global $DB, $URL, $BREADCRUMB, $POST, $ACCESS, $Modules;
		$DB2 = new DB();

		$parent = false;

		////////////////////////////////////////////////////////////////////////
		// получаем переменную, по которой будем переходить в разделы
		$num_section_id = $this->fields->get_num_field(array('form->type'=>'select::'.$this->categories->table->name.'::'),true);
		if($num_section_id < 0){
			$field_section_id = 'pid';
		}else{
			$field_section_id = $this->fields->field[$num_section_id]->db->field;
		}

		$num_cat_pid = $this->categories->get_num_field(array('form->type'=>'select::'.$this->categories->table->name.'::'),true);
		if($num_cat_pid < 0){
			$num_cat_pid = $this->categories->get_num_field(array('form->type'=>'select::parent::'),true);
		}
		if($num_cat_pid < 0){
			$field_cat_pid = 'pid';
		}else{
			$field_cat_pid = $this->categories->field[$num_cat_pid]->db->field;
		}

		$cat_field_pid = null;
		if($num_cat_pid >= 0){
			$cat_field_pid = $this->categories->field[$num_cat_pid]->db->field;
			/*$ar_field_in_cat = explode('::',$fild_in_cat);
			if(!empty($ar_field_in_cat[2])){
			$ar_field_in_cat = explode('->',$ar_field_in_cat[2]);
			if(!empty($ar_field_in_cat[0])){
			$cat_field_pid = $ar_field_in_cat[0];
			}
			}*/
		}

		////////////////////////////////////////////////////////////////////////
		$pid = intval($URL->GetValue($field_section_id));

		$cur_fields = &$this->fields;
		if($cat){
			$cur_fields = &$this->categories;
			if(!empty($cur_fields->count)){
				$id_field = $this->get_field_id($cur_fields);
				if($this->name == 'sales' && !empty($pid)){
					/*$sql = "SELECT * ";
					$sql.= "FROM `".$cur_fields->table->name."` ";
					$sql.= "WHERE `".$id_field."` = '".$pid."' ";
					$sql.= "LIMIT 1";//*/
					$DB->select();
					$DB->from($cur_fields->table->name);
					$DB->where(array($id_field=>$pid));
					if($DB->execute()){
						if($DB->Next()){
							for($i=0,$n=$cur_fields->count;$i<$n;$i++){
								$cur_field = $cur_fields->field[$i];
								if($cur_field->show->view){
									$value = $DB->Value($cur_field->db->field);
									$ar_type = explode('::',$cur_field->form->type);
									$sizeof_ar_type = sizeof($ar_type);
									if($sizeof_ar_type > 1){
										switch($ar_type[0]){
											case 'select':
												if(!empty($ar_type[1])){
													if(!empty($ar_type[2])){
														$_ar = explode('->',$ar_type[2]);
														if(sizeof($_ar) > 1){
															$f_key = $_ar[0];
															$f_value = $_ar[1];
															$DB2->select(array($f_key,$f_value));
															$DB2->from($ar_type[1]);
															$DB2->where(array($f_key=>$value));
															//$sql = "SELECT `".$f_key."`, `".$f_value."` FROM `".$ar_type[1]."` WHERE `".$f_key."`='".$value."' LIMIT 1";
															if($DB2->execute()){
																if($DB2->Next()){
																	$row2 = $DB2->GetRecord();
																	$value = $row2[$f_value];
																}
																$DB2->ClearDataSet();
															}
														}
													}
												}
												break;
										}
									}
									?><p><strong><?=$cur_field->params->label;?></strong>: <?=$value;?></p><?
								}
							}
						}
						$DB->ClearDataSet();
					}
				}
			}
		}else{
			$this->View(1);
		}
		$n = $cur_fields->count;
		if($n > 0){
			$this->ControlPanel($cat);
			$fields = array();
			$titles = array();
			$types = array();
			for($i=0;$i<$n;$i++){
				$cur_field = $cur_fields->field[$i];
				if($cur_field->show->view && $ACCESS->FieldIsAccess($cur_field->access)){
					$titles[] = $cur_field->params->label;
					$fields[] = $cur_field->db->field;
					$types[] = $cur_field->form->type;
				}
				if($cur_field->db->field == $field_cat_pid){
					$parent = true;
				}
			}

			$sizeof_fields = sizeof($fields);

			$table = $cur_fields->table->name;
			$sql = "SELECT * ";
			$sql.= "FROM `".$table."` ";

			$sql_where = array();


			if($parent && (!empty($pid) || $URL->GetValue('cat') > 0 || $this->name == 'pages' || $this->name == 'catalog')){
				if(!empty($cat_field_pid) and $URL->GetValue('cat') > 0){
					$sql_where[] = " `".$cat_field_pid."` = '".$pid."'";
				}else{
					$sql_where[] = " `".$field_section_id."` = '".$pid."'";
				}
			}else{
				if($table == 'sys_users' and !empty($pid)){
					$field = 'groups_id';
					$num_field = $cur_fields->get_num_field($field);
					if($num_field > 0){
						$sql_where[] = " (`".$field."` LIKE '%".'"'.$pid.'"'."%' OR `".$field."` LIKE '[".$pid."]' OR `".$field."` LIKE '[".$pid.",%' OR `".$field."` LIKE '%,".$pid."]')";
					}
				}
			}

			/// этот блок нужно доделать
			/*$field = 'company_id';
			$num_field = $cur_fields->get_num_field($field);
			if(!empty($_SESSION[$field])){
			if($URL->GetValue('cat') > 0 and $num_field > 0){
			$sql_where[] = " `".$field."` = '".$_SESSION[$field]."'";
			}else{
			$_SESSION[$field];
			}
			}*/

			if(!empty($_SESSION[$table][$pid])){
				foreach($_SESSION[$table][$pid] as $key=>$value){
					if($value == 'all'){
						$value = '';
					}
					if(!empty($value)){
						switch($key){
							case 'performer':
							case 'seo':
							case 'account':
								$sql_seo = "SELECT `id` ";
								$sql_seo.= "FROM `".$this->categories->table->name."` ";
								$sql_seo.= "WHERE `".$key."` = '".$value."' ";
								$DB_seo = new DB();
								$ar_values = array();
								if($DB_seo->Query($sql_seo)){
									while($DB_seo->Next()){
										$ar_values[] = $DB_seo->Value('id');
									}
									$DB_seo->ClearDataSet();
								}
								$sql_where[] = " `pid` IN ('".implode("','",$ar_values)."') ";
								break;

							default:
								if(is_array($value)){
									foreach($value as $k_val=>$ar_value){
										if($ar_value['value'] == 'all'){
											$ar_value['value'] = '';
										}
										if(!empty($ar_value['value'])){
											$sql_where[] = " `".$key."` ".$ar_value['sign']." '".$ar_value['value']."'";
										}
									}
								}else{
									$sql_where[] = " `".$key."` = '".$value."'";
								}
								break;
						}
					}
				}
			}
			for($j=0,$k=sizeof($sql_where);$j<$k;$j++){
				if($j > 0){
					$sql.= " AND";
				}else{
					$sql.= " WHERE";
				}
				$sql.= $sql_where[$j];
			}

			$ar_order_by = array();

			$field = 'order';
			$num_field = $cur_fields->get_num_field($field);
			if($num_field > 0){
				$ar_order_by[] = array($field=>'ASC');
			}

			$array = $cur_fields->get_num_field(array('form->type'=>'datetime'),true,true);
			if(is_array($array)){
				foreach($array as $num_field){
					$field = $cur_fields->field[$num_field]->db->field;
					$sort = 'DESC';
					if($field == 'date_end'){
						$sort = 'ASC';
					}
					$ar_order_by[] = array($field=>$sort);
				}
			}

			/*$field = 'priority';
			$num_field = $cur_fields->get_num_field($field);
			if($num_field > 0){
			$ar_order_by[] = array($field=>'ASC');
			}*/

			/*$field = 'status';
			$num_field = $cur_fields->get_num_field($field);
			if($num_field > 0){
			$ar_order_by[] = array($field=>'ASC');
			}*/

			if(empty($ar_order_by)){
				$field = $this->get_field_id($cur_fields);
				$ar_order_by[] = array($field=>'ASC');
			}


			if(sizeof($ar_order_by) > 0){
				$sql.= " ORDER BY";
				$one = false;
				$has_field_order_id = false;
				foreach($ar_order_by as $array){
					foreach($array as $field=>$ord){
						if($one){
							$sql.= ",";
						}else{
							$one = true;
						}
						if(mb_strtolower($field) == 'id'){
							$has_field_order_id = true;
						}
						$sql.= " `".$field."` ".$ord;
					}
				}
				if(!$has_field_order_id){
					if($one){
						$sql.= ",";
					}else{
						$one = true;
					}
					$sql.= " `id` ASC";
				}
			}
			//echo $sql;

			//////////////////////////
			$limit_maximum = true;
			if(!$cat){
				$count_per_page = 50;
				$max_count = $limit_count = $count_per_page;
				$limit_from = 0;

				$sql_max = str_ireplace('SELECT *','SELECT COUNT(*) AS `count`',$sql);
				if($DB->Query($sql_max)){
					if($DB->Next()){
						$max_count = $DB->Value('count');
					}
					$DB->ClearDataSet();
				}
				?><div>Всего записей: <strong><?=number_format($max_count,0,'.',' ');?></strong></div><?
				$page_max = @ceil($max_count/$limit_count);

				$page = $URL->GetValue('page');
				//echo("<pre>");print_r($_SESSION);echo("</pre>");
				if(empty($page) && isset($_SESSION[$table]['params']['page'])){
					$page = $_SESSION[$table]['params']['page'];
				}else{
					$_SESSION[$table]['params']['page'] = $page;
				}
				$navigation = false;
				if($page == 'all'){
					$limit_count = $max_count;
				}else{
					$page = intval($page);
					if($page < 1) $page = 1;
					elseif($page > $page_max){
						$page = 1;
						$URL->SetValue('action','view');
						$URL->SetValue('page','');
						unset($_SESSION[$table]['params']['page']);
						header('Location: '.$URL->GetURL(false));
						exit();
						//MODULES::Message('<meta http-equiv="refresh" content="0; url='.$URL->GetURL().'" />',0);
						return true;
					}
					$limit_from = $page * $limit_count-$limit_count;
					$navigation = true;
				}

				if($navigation){
					$sql.= " LIMIT ".$limit_from.",".$limit_count;
					$limit_maximum = false;
				}else{
					if($max_count < $this->max_rows){
						$limit_maximum = false;
					}
				}
				if($limit_maximum){
					$sql.= " LIMIT ".$this->max_rows;
					MODULES::Message('Ограничение по выводу в '.$this->max_rows.' строк для таблицы `'.$table.'`',3);
				}
			}
			//////////////////////////
			$sql.= ";";
			//echo $sql."<br>";

			if($DB->Query($sql)){
				if($DB->NumRows() > 0){

					$save_in_csv = '';
					if($cat == 0){
						$save_in_csv = $POST->GetValue('save_in_csv');
						if($save_in_csv){
							$ar_csv = array();
							$ar_csv[] = $titles;
						}
						$this->ShowNavigator($page,$page_max,$count_per_page);
					}

					$title = $cur_fields->titles->view;
					?><div class="<?if($cat){?>w2<?}else{?>w1<?}?>">
						<div class="header"><div class="r"><div class="c"><div class="text"><div class="title"><?=$title;?></div></div></div></div></div>
						<div class="body"><div class="r"><div class="c"><table class="<?if($cat){?>t2<?}else{?>t1<?}?>" cellpadding="0" cellspacing="0">
										<thead>
											<tr><?if(!empty($ACCESS->ar_value[$this->name]['delete'])){?><td><input class="check_all" name="check_all_<?=$cat;?>" type="checkbox" /></td><?}?>
												<?
												for($i=0,$n=sizeof($titles);$i<$n;$i++){
													?><td id="column_<?=$cat;?>_<?=$i;?>"><?=$titles[$i];?></td><?
												}
												?>
												<?if(!empty($ACCESS->ar_value[$this->fields->table->name]['edit'])){?><td>&nbsp;</td><?}?><?if(!empty($ACCESS->ar_value[$this->fields->table->name]['delete'])){?><td>&nbsp;</td><?}?>
												<td class="hidden"></td>
											</tr>
										</thead>
										<tbody>
											<?
											$i_row = 0;
											$date_current = date("YmdHis");
											$id_field = $this->get_field_id($cur_fields);
											while($DB->Next()){

												$i_row++;
												$light_mode = '';
												$date_end = 0;
												$row = $DB->GetRecord();
												/*$id_field = 'id';
												if(!isset($row[$id_field])){
												$id_field = key($row);
												}*/

												$cur_id = $row[$id_field];

												$tr_link = '';
												if($cat){
													//$tr_class[] = 'click';

													$tr_url = new URL();
													$tr_url->SetValue($field_section_id,$cur_id);
													$tr_link = $tr_url->GetURL();
												}
												?><tr><?if(!empty($ACCESS->ar_value[$this->name]['delete'])){?><td align="center"><input class="check_item" name="<?=$id_field;?>[]" value="<?=$cur_id;?>" type="checkbox" /></td><?}?><?
													if($save_in_csv){
														$ar_row_csv = array();
													}
													for($i=0;$i<$sizeof_fields;$i++){
														//echo("<pre>");print_r($fields[$i]);echo("</pre>");
														//echo("<pre>");print_r($cur_fields->get_num_field($fields[$i]));echo("</pre>");
														$cur_field = &$cur_fields->field[$cur_fields->get_num_field($fields[$i])];
														//echo("<pre>");print_r($cur_field);echo("</pre>");
														$params = $cur_field->params;
														$show = $cur_field->show;
														//$td_value = $row[$fields[$i]];
														$type = $types[$i];
														$ar_type = explode('::',$type);
														$sizeof_ar_type = sizeof($ar_type);
														$json = false;
														if($sizeof_ar_type < 2){
															$json_type = json_decode($type,true);
															if(!empty($json_type)){
																$json = true;
																if(!empty($json_type['type'])){
																	$type = $json_type['type'];
																}
															}
														}
														if(!$json){
															$type = $ar_type[0];
														}
														$td_class = array();
														$_link = 0;
														$td_link = '';
														if($cat && $i < 2){
															$td_class[] = 'click';
															$_link = 1;
														}elseif(isset($ar_type[1]) && $ar_type[1] == 'categories_projects'){
															$td_class[] = 'click';
															$_link = 2;
															$TD_url = new URL();
															$TD_url->SetValue($field_cat_pid,$row[$fields[$i]]);
															$td_link = $TD_url->GetURL();
														}
														if(!empty($show->align)){
															$td_class[] = $show->align;
														}
														if(empty($show->wrap)){
															$td_class[] = 'nowrap';
														}else{
															if($show->wrap == 1){
																$td_class[] = 'wrap_1';
															}elseif($show->wrap == 2){
																$td_class[] = 'wrap_2';
															}else{
																$td_class[] = 'wrap';
															}
														}
														$td_class = implode(' ',$td_class);
														?><td<?if($_link == 1){?> onclick="window.location.href='<?=$tr_link;?>'"<?}elseif($_link == 2){?> onclick="window.location.href='<?=$td_link;?>'"<?} if(!empty($td_class)){?> class="<?=$td_class;?>"<?} if(!empty($show->align)){?> align="<?=$show->align;?>"<?}?>><?
															//echo '$type = '.$type.'<br>';
															ob_start();
															switch($type){

																case 'select':
																	ob_start();
																	if(!empty($ar_type[1])){
																		switch($ar_type[1]){
																			case 'parent':
																				$_ar = explode('->',$ar_type[2]);
																				$f_key = $_ar[0];
																				$f_value = $_ar[1];
																				$sql = "SELECT `".$f_key."`, `".$f_value."` FROM `".$cur_fields->table->name."` WHERE `".$f_key."`='".$row[$fields[$i]]."' LIMIT 1";
																				if($DB2->Query($sql)){
																					if($DB2->Next()){
																						$row2 = $DB2->GetRecord();
																						$val = $row2[$f_value];
																						echo $val;
																					}
																					$DB2->ClearDataSet();
																				}
																				break;

																			case 'array':
																				eval('$_ar = array'.$ar_type[2].';');
																				if(isset($_ar[$row[$fields[$i]]])){
																					echo $_ar[$row[$fields[$i]]];
																				}
																				break;

																			case 'sys_users':
																				if(($fields[$i] == 'performer' or $fields[$i] == 'seo') and empty($td_value)){
																					$sql = "SELECT `".$fields[$i]."` FROM `".$this->categories->table->name."` WHERE `id` LIKE '".$row['pid']."' LIMIT 1";
																					if($DB2->Query($sql)){
																						if($DB2->NumRows() > 0){
																							if($DB2->Next()){
																								$td_value = $DB2->Value($fields[$i]);
																							}
																						}
																						$DB2->ClearDataSet();
																					}
																				}
																				if(!empty($ar_type[2])){
																					$_ar = explode('->',$ar_type[2]);
																					$f_key = $_ar[0];
																					$f_value = $_ar[1];
																					$sql = "SELECT `".$f_key."`, `".$f_value."`, `email` FROM `".$ar_type[1]."` WHERE `".$f_key."`='".$row[$fields[$i]]."' LIMIT 1";
																					if($DB2->Query($sql)){
																						if($DB2->Next()){
																							$row2 = $DB2->GetRecord();
																							$val = $row2[$f_value];
																							echo $val.' ('.$row2['email'].')';
																						}
																						$DB2->ClearDataSet();
																					}
																				}
																				break;

																			default:
																				$select_table_name = $ar_type[1];
																				//echo("<pre>");print_r($Modules);echo("</pre>\n");
																				if(!empty($ar_type[2])){
																					$_ar = explode('->',$ar_type[2]);
																					$f_key = $_ar[0];
																					$f_value = $_ar[1];
																					//$sql = "SELECT `".$f_key."`, `".$f_value."` FROM `".$ar_type[1]."` WHERE `".$f_key."`='".$row[$fields[$i]]."' LIMIT 1";
																					$DB2->select(array($f_key,$f_value));
																					$DB2->from($select_table_name);
																					$DB2->where(array($f_key=>$row[$fields[$i]]));
																					$DB2->limit(1);
																					if($DB2->execute()){
																						if($DB2->Next()){
																							$row2 = $DB2->GetRecord();
																							$val = $row2[$f_value];
																							if(isset($Modules->module[$select_table_name])){
																								$num = $Modules->module[$select_table_name]->fields->get_num_type($f_value);
																								if($num > 0){
																									$select_field_type = $Modules->module[$select_table_name]->fields->field[$num]->form->type;

																									$select_ar_type = explode('::',$select_field_type);
																									$sizeof_ar_type = sizeof($select_ar_type);
																									$json = false;
																									$json_type = array();
																									if($sizeof_ar_type < 2){
																										$json_type = json_decode($select_field_type,true);
																										if(!empty($json_type)){
																											$json = true;
																											if(!empty($json_type['type'])){
																												$select_field_type = $json_type['type'];
																											}
																										}
																									}
																									if(!$json){
																										$select_field_type = $select_ar_type[0];
																									}
																									if($select_field_type == 'json'){
																										$val = json_decode($val,true);
																										if(is_array($val)){
																											if(isset($json_type['view'])){
																												if($json_type['view'] == 'table'){
																													ob_start();
																													?><table><?
																														$first_iteration = true;
																														foreach($val as $num_row=>$row_item){
																															if($first_iteration){
																																?><thead><?
																																	foreach($row_item as $column_name=>$column_item){
																																		?><th><?=$column_name;?></th><?
																																	}
																																?></thead><?
																																?><tbody><?
																																	$first_iteration = false;
																																}
																																?><tr><?
																																	foreach($row_item as $column_item){
																																		?><td><?=$column_item;?></td><?
																																	}
																																?></tr><?
																															}
																															if(!$first_iteration){
																															?></tbody><?
																														}
																													?></table><?
																													$val = ob_get_contents();ob_get_clean();
																												}
																											}
																										}
																									}
																								}
																							}
																							echo $val;
																							if($select_table_name == 'project_status'){
																								switch($row2[$f_key]){

																									case 5:
																										$light_mode = 'complete';
																										break;

																									case 6:
																										$light_mode = 'not_full_complete';
																										break;

																									default:
																										$light_mode = 'date_out';
																										break;
																								}
																							}
																						}
																						$DB2->ClearDataSet();
																					}
																				}
																				break;
																		}
																	}
																	$select_val = ob_get_contents();ob_get_clean();
																	//if(!isset($row[$fields[$i]]) or mbstrlen(strval($row[$fields[$i]])) <= 0) echo '&mdash;';
																	//echo strval($row[$fields[$i]]);
																	if(empty($select_val)){
																		$select_val = '&mdash;';
																	}
																	echo $select_val;
																	break;

																case 'list':
																	if($json){
																		$from = '';
																		if(!empty($json_type['from'])){
																			$from = $json_type['from'];
																		}
																		if($from == 'table'){
																			$table = '';
																			if(!empty($json_type['table'])){
																				$table = $json_type['table'];
																			}
																			if(!empty($table)){
																				$f_value = '';
																				if(!empty($json_type['field']['value'])){
																					$f_value = $json_type['field']['value'];
																				}

																				$f_label = '';
																				if(!empty($json_type['field']['label'])){
																					$f_label = $json_type['field']['label'];
																				}


																				if(!empty($f_value) and !empty($f_label)){
																					$sql = "SELECT `".$f_value."`, `".$f_label."` FROM `".$table."`";

																					$module_name = preg_replace('~^categories_(.*)$~','$1',$table);
																					if(isset($Modules->module[$module_name])){
																						$field_active = 'active';
																						$num = $Modules->module[$module_name]->fields->get_num_type($field_active);
																						if($num > 0){
																							$sql.= " WHERE `".$field_active."` > 0";
																						}
																					}

																					if($DB2->Query($sql)){
																						$ar_value = json_decode($row[$fields[$i]],true);
																						if(empty($ar_value)){
																							$ar_value = array();
																						}
																						$ar_label = array();
																						while($DB2->Next()){
																							$value = $DB2->Value($f_value);
																							$label = $DB2->Value($f_label);
																							if(in_array($value,$ar_value)){
																								$ar_label[] = $label;
																							}
																						}
																						echo implode(',<br>',$ar_label);
																						$DB2->ClearDataSet();
																					}
																				}
																			}
																		}
																	}
																	break;

																case 'file':
																	$file_type = '';
																	if(!empty($ar_type[1])){
																		$file_type = $ar_type[1];
																	}elseif($json){
																		if(!empty($json_type['file_type'])){
																			$file_type = $json_type['file_type'];
																		}
																	}

																	if(!empty($file_type)){
																		switch($file_type){
																			case 'name_to':
																				if(empty($_file_name)){
																					if($sizeof_ar_type > 2){
																						$_file_name = $cur_fields->field[$cur_fields->get_num_field($ar_type[2])]->db->field;
																					}
																				}
																				break;

																			case 'image':
																				if(!empty($row[$fields[$i]])){
																					$img = $row[$fields[$i]];
																					$img_src = '';
																					if($json_img = json_decode($img,true)){
																						if(!empty($json_img['thumb']['src'])){
																							$img_src = $json_img['thumb']['src'];
																						}
																					}else{
																						$img_src = $img;
																					}
																					$url_img = '';
																					if(!empty($img_src)){
																						$url_img = "https://".$_SERVER['HTTP_HOST'].$img_src;
																					}
																					if(IMAGE::exist_url($url_img)){
																						?><img src="<?=$img_src;?>" alt=""><?
																					}else{
																						echo 'Файл "'.$img_src.'" не существует';
																					}
																				}
																				break 2;
																		}
																	}
																	$a_href = $row[$fields[$i]];
																	$a_title = $row[$_file_name];
																	$file_url = new URL();
																	$file_url->SetValue('file',$a_href);
																	//$a_href = '/uploads/'.
																	//$td_value = '<a href="'.$file_url->GetURL().'">'.$a_title.'</a>';
																	?><a href="<?=$file_url->GetURL();?>"><?=$a_title;?></a><?
																	break;

																case 'checkbox':
																	if($row[$fields[$i]] > 0){
																		echo 'да';
																	}else{
																		echo 'нет';
																	}
																	break;

																case 'price':
																	$price = $row[$fields[$i]];
																	$price = number_format($price,0,'.',' ');
																	$price = str_replace(' ','&nbsp;',$price);
																	echo $price;
																	break;

																case 'datetime':
																	$date = $row[$fields[$i]];
																	ob_start();
																	if($sizeof_ar_type > 1){
																		switch($ar_type[1]){

																			case 'add':
																				$date_end = str_replace(array(' ','-',':'),'',$date);
																				break;
																		}
																	}else{
																		if($fields[$i] == 'date_end'){
																			$date_end = str_replace(array(' ','-',':'),'',$date);
																			if($cat){
																				if($this->name == 'sales'){
																					$sql = "SELECT * ";
																					$sql.= "FROM `".$this->fields->table->name."` ";
																					$sql.= "WHERE `pid` = '".$cur_id."' ";
																					$sql.= "AND `status` NOT IN ('2','4') ";
																					$sql.= "ORDER BY `date_end` ASC ";
																					$sql.= "LIMIT 1 ";
																					if($DB2->Query($sql)){
																						if($DB2->NumRows() <= 0){
																							$date_end = $date_current+1000000;
																						}
																						$DB2->ClearDataSet();
																					}
																				}
																			}
																		}
																	}
																	echo $date;
																	$td_buff = ob_get_contents();
																	ob_get_clean();
																	echo $td_buff;
																	break;

																case 'time':
																	if(!empty($ar_type[1])){
																		$_ar = explode(':',$ar_type[1]);
																		$sizeof_ar = sizeof($_ar);
																		$ar_value = explode(':',$row[$fields[$i]]);
																		$ar_value['HH'] = &$ar_value[0];
																		$ar_value['MM'] = &$ar_value[1];
																		$ar_value['SS'] = &$ar_value[2];
																		$str = array();
																		foreach($_ar as $val){
																			$str[] = $ar_value[$val];
																		}
																		$str = implode(':',$str);
																		echo $str;
																	}
																	break;

																case 'json':
																	$val = json_decode($row[$fields[$i]],true);
																	if(is_array($val)){
																		if(isset($json_type['view'])){
																			if($json_type['view'] == 'text'){
																				print_r($val);
																				/*?><table><?
																					$first_iteration = true;
																					foreach($val as $num_row=>$row_item){
																						if($first_iteration){
																							?><thead><?
																								foreach($row_item as $column_name=>$column_item){
																									?><th><?=$column_name;?></th><?
																								}
																							?></thead><?
																							?><tbody><?
																								$first_iteration = false;
																							}
																							?><tr><?
																								foreach($row_item as $column_item){
																									?><td><?=$column_item;?></td><?
																								}
																							?></tr><?
																						}
																						if(!$first_iteration){
																						?></tbody><?
																					}
																				?></table><?//*/
																			}
																		}
																	}
																	break;

																default:
																	echo $row[$fields[$i]];
																	break;
															}
															$td_value = ob_get_contents();
															ob_get_clean();
															echo $td_value;
															if(!empty($params->description)){
																echo '&nbsp;';
																echo $params->description;
															}
															if($save_in_csv){
																$ar_row_csv[] = $td_value;
															}
														?></td><?
													}
													if(!empty($ACCESS->ar_value[$this->name]['edit'])){
														?><th><?
															$URL->SetValue('action','edit');
															?><form id="button_edit_<?=$cat;?>_<?=$cur_id;?>" class="button_edit" action="<?=$URL->GetURL();?>" method="post"><fieldset><input type="hidden" name="<?=$id_field;?>" value="<?=$cur_id;?>" /><input type="submit" name="edit" class="btn_edit" value="" title="Редактировать" /></fieldset></form></th><?
													}
													if(!empty($ACCESS->ar_value[$this->name]['delete'])){?><th><?$URL->SetValue('action','delete');?><form id="button_delete_<?=$cat;?>_<?=$cur_id;?>" class="button_delete" action="<?=$URL->GetURL();?>" method="post"><fieldset><input type="hidden" name="<?=$id_field;?>" value="<?=$cur_id;?>" /><input type="submit" name="delete" class="btn_delete" value="" title="Удалить" /></fieldset></form></th><?}
													?>
													<td class="hidden">
														<!--<td>-->
														<?
														if($this->name == 'sales'){
															$day_date_current = floor($date_current/1000000);
															$day_date_end = floor($date_end/1000000);
															if($day_date_current == $day_date_end){
																$light_mode = 'date_today';
															}
															if($date_end<$date_current){
																$light_mode = 'date_out';
															}
														}else{
															if($date_current<$date_end){
																if($light_mode == 'date_out'){
																	$light_mode = '';
																}
															}
														}
														$form2 = new FormField();
														$form2->id = 'light_mode_'.$cat.'_'.$i_row;
														$form2->type = 'hidden';
														//$form2->type = 'text';
														$form2->name = 'light_mode';
														$form2->value = $light_mode;
														echo FORM::GetElement('input',$form2);
														?>
													</td>
												</tr><?
												if($save_in_csv){
													$ar_csv[] = $ar_row_csv;
												}
											}
											?>
										</tbody>
									</table>
								</div></div></div>
						<div class="footer"><div class="r"><div class="c"></div></div></div>
					</div>
					<?if(!empty($ACCESS->ar_value[$this->name]['delete'])){?><?$URL->SetValue('action','delete');?><form style="display: none" class="checked_delete" name="<?=$cur_fields->table->name;?>_checked_delete" action="<?=$URL->GetURL();?>" method="post"><input type="hidden" name="table_name" value="<?=$cur_fields->table->name;?>" /><input type="submit" name="delete" class="btn_delete" value="Удалить выбранные" title="Удалить выбранные" /></form><?}?><?
					if($save_in_csv){
						$this->array_to_CSV($ar_csv);
					}
				}
				$DB->ClearDataSet();
			}
		}
	}

	/**
	* двухмерный массив в форме строк и столбцов
	* выводит для скачивания в формате CSV,
	* в кодировке для Windows
	*
	* @param mixed $data
	*/
	function array_to_CSV($data,$file_name = null){
		foreach($data as &$row_data){
			foreach($row_data as &$column_data){
				$column_data = TEXT::html_del_tags($column_data);
			}
			$row_data = TEXT::utf8_to_cp1251($row_data);
			$row_data = implode(';',$row_data);
		}
		if(empty($file_name)){
			$file_name = $_SESSION['user_id'].'_'.$this->name.'_'.date("Y-m-d");
		}
		$file_name.= '.csv';
		ob_clean();
		header('Content-Disposition: attachment; filename="'.$file_name.'"');
		echo implode("\n",$data);
		exit();
	}

	/**
	* выводит постраничную навигацию
	*
	* @param mixed $page
	* @param mixed $page_max
	* @param mixed $count_per_page
	*/
	function ShowNavigator($page,$page_max,$count_per_page){
		global $URL;
		$first_page = '1';	// '1' или ''
		$URL->SetValue('action','view');
		if($page == 'all'){
			$a_text = "Вывести постранично";
			$URL->SetValue('page',$first_page);
			?><div class="navigation"><a href="<?=$URL->GetURL();?>" class="all"><?=$a_text;?></a></div><?
		}elseif($page_max > 1) {
			if(empty($page)){
				$page = 1;
			}
			?><div class="navigation"><div class="label">Страницы:</div><?
				//если <= 5 страниц, то выводим все
				if ($page_max <= 5) {
					//выводим первую страницу
					for ($count = 1; $count <= $page_max; $count++) {
						if ($page == $count) {
							?><a class="page_active"><?=$count;?></a><?
						} else {
							if ($count == 1){
								$URL->SetValue('page',$first_page);
							}else{
								$URL->SetValue('page',$count);
							}
							?><a href="<?=$URL->GetURL();?>"><?=$count;?></a><?
						}
					}
				} else {
					if ($page != 1){
						$URL->SetValue('page',$first_page);
						$a_text = 'начало';
						?><a href="<?=$URL->GetURL();?>"><?=$a_text;?></a><?
					}
					$prev = $page - 1;
					$a_text = 'предыдущая';
					if ($prev == 1){
						$URL->SetValue('page',$first_page);
						?><a class="prev" href="<?=$URL->GetURL();?>"><?=$a_text;?></a><?
					}elseif ($prev != 0){
						$URL->SetValue('page',$prev);
						?><a class="prev" href="<?=$URL->GetURL();?>"><?=$a_text;?></a><?
					}
					//если у нас больше одной страницы
					if ($page_max > 1) {
						if ($page < 4) {
							//выводим первую страницу
							for ($count = 1; $count <= 5; $count++) {
								if ($page == $count) {
									?><a class="page_active"><?=$count;?></a><?
								} else {
									if ($count == 1){
										$URL->SetValue('page',$first_page);
									}else{
										$URL->SetValue('page',$count);
									}
									?><a href="<?=$URL->GetURL();?>"><?=$count;?></a><?
								}
							}
						} else {
							$prev = $page - 1;
							$prev2 = $page - 2;
							$next = $page + 1;
							$next2 = $page + 2;

							if ($page == $page_max - 1) {
								$prev4 = $prev2 - 1;
								$URL->SetValue('page',$prev4);
								?><a href="<?=$URL->GetURL();?>"><?=$prev4;?></a><?
							}

							if ($page == $page_max) {
								$prev3 = $prev2 - 1;
								$prev4 = $prev3 - 1;
								$URL->SetValue('page',$prev4);
								?><a href="<?=$URL->GetURL();?>"><?=$prev4;?></a><?
								$URL->SetValue('page',$prev3);
								?><a href="<?=$URL->GetURL();?>"><?=$prev3;?></a><?
							}
							$URL->SetValue('page',$prev2);
							?><a href="<?=$URL->GetURL();?>"><?=$prev2;?></a><?
							$URL->SetValue('page',$prev);
							?><a href="<?=$URL->GetURL();?>"><?=$prev;?></a><?

							?><a class="page_active"><?=$page;?></a><?

							if ($next <= $page_max){
								$URL->SetValue('page',$next);
								?><a href="<?=$URL->GetURL();?>"><?=$next;?></a><?
							}
							if ($next < $page_max){
								$URL->SetValue('page',$next2);
								?><a href="<?=$URL->GetURL();?>"><?=$next2;?></a><?
							}
						}
					}
					$next = $page + 1;;
					if ($next <= $page_max){
						$URL->SetValue('page',$next);
						$a_text = 'следующая';
						?><a class="next" href="<?=$URL->GetURL();?>"><?=$a_text;?></a><?
					}
					if ($page != $page_max){
						$URL->SetValue('page',$page_max);
						?><a href="<?=$URL->GetURL();?>">конец</a><?
					}
				}
				if ($page_max > 1) {
					$URL->SetValue('page','all');
					$a_text = "Показать все";
					?><a href="<?=$URL->GetURL();?>" class="all"><?=$a_text;?></a><?
					/*$counts = $count_per_page*$page_max;
					$ar_page_per_page = array(10,20,50,100);
					$sizeof_ar_page_per_page = sizeof($ar_page_per_page);
					if($sizeof_ar_page_per_page > 1){
					if($counts > $ar_page_per_page[0]){
					?><select class="count_per_page"><?
					for($i=0;$i<$sizeof_ar_page_per_page;$i++){
					$value = $label = $ar_page_per_page[$i];
					if($counts > $value){
					$active = '';
					if($value == $count_per_page){
					$active = ' class="active';
					}
					?><option value="<?=$value;?>"<?=$active;?>><?=$label;?></option><?
					}
					}
					?></select><?
					}
					}*/
				}
			?></div><?
		}
		$URL->SetValue('page','');
	}

	/**
	* Выводит форму для добавления/редактирования существующей записи
	*
	*/
	function FormSave(){
		global $DB, $URL, $POST, $ACCESS;

		$action = $URL->GetValue('action');

		if($URL->GetValue('cat')){
			$cur_fields = $this->categories;
		}else{
			$cur_fields = $this->fields;
		}

		if($action == 'add'){
			$title = $cur_fields->titles->add;
		}elseif($action == 'edit'){
			$title = $cur_fields->titles->edit;
		}

		$FORM = new FORM();

		$f_url = new URL();
		if($action == 'edit'){
			$f_url->SetValue('action','check_edit');
			if($POST->GetCount() < 1){
				$URL->SetValue('action','view');
				//MODULES::Message('<meta http-equiv="refresh" content="0; url='.$URL->GetURL().'" />',0);
				header("Location: ".$URL->GetURL(false));
				return true;
			}
		}elseif($action == 'add'){
			$f_url->SetValue('action','check_add');
		}

		$n = $cur_fields->count;
		$fields = array();
		for($i=0;$i<$n;$i++){
			$cur_field = $cur_fields->field[$i];
			if((($cur_field->show->edit && $action == 'edit') || ($cur_field->show->add && $action == 'add')) && $ACCESS->FieldIsAccess($cur_field->access)){
				$fields[$cur_field->db->field] = $i;
			}
		}

		if (sizeof($fields) > 1){
			$id_field = $this->get_field_id($cur_fields);
			$sql = "SELECT * ";
			$sql.= "FROM `".$cur_fields->table->name."` ";
			$sql.= "WHERE `".$id_field."` = '".$POST->GetValue($id_field)."' ";
			if($DB->Query($sql)){
				if($DB->Next()){
					$row = $DB->GetRecord();
					foreach($row as $key=>$value){
						if(isset($fields[$key])){
							$cur_fields->field[$fields[$key]]->form->value = $value;
						}
					}
				}
			}
		}else{
			return false;
		}

		//echo("<pre>");print_r($cur_fields);echo("</pre>");

		define('SLOT_JS_1', ADMINISTRATOR_PATH.'js/form_add.min.js');
		$mandatory = array();
		ob_start();
		?><table cellpadding="0" cellspacing="0" border="0"><tbody><tr><td>
						<div class="w1">
							<div class="header"><div class="r"><div class="c"><div class="text"><div class="title"><?=$title;?></div></div></div></div></div>
							<div class="body">
								<div class="r">
									<div class="c">
										<form id="f_add" action="<?=$f_url->GetURL();?>" method="post" enctype="multipart/form-data">
											<fieldset>
												<table id="t_add" class="t3" cellpadding="0" cellspacing="0">
													<tfoot>
														<tr><td></td><td><? if($action == 'add'){?><input type="submit" value="Добавить" title="Добавить" tabindex="{TSUB}" /><?}else{?><input type="submit" value="Изменить" title="Изменить" tabindex="{TSUB}" /><?}?></td><td></td></tr>
													</tfoot>
													<tbody>
														<?
														$i = 0;
														foreach($fields as $num_field){
															$_id = 'field_'.$i;
															//echo $_id;
															$cur_field = $cur_fields->field[$num_field];
															$cur_field->form->id = $_id;
															$this_form = $cur_field->form;
															?><tr><td class="label"><label for="<?=$_id;?>"><?if(isset($cur_field->params->label)){echo $cur_field->params->label;};?></label></td><td class="data"><?=$FORM->html_form_element($this_form);?></td><td><?if(isset($cur_field->params->description)){echo $cur_field->params->description;};?></td></tr><?
															if($cur_field->params->mandatory){
																$is_m = true;
																if($action == 'edit'){
																	$ar_type = explode('::',$cur_field->form->type);
																	$sizeof_ar_type = sizeof($ar_type);
																	if($sizeof_ar_type > 0){
																		switch($ar_type[0]){
																			case 'password':
																				$is_m = false;
																				break;
																			case 'md5':
																				$is_m = false;
																				break;
																		}
																		if($sizeof_ar_type > 1){
																			switch($ar_type[1]){
																				case 'password':
																					$is_m = false;
																					break;
																			}
																		}
																	}
																}
																if($is_m){
																	$mandatory[] = $FORM->GetTabindex(false);
																}
															}
															$i++;
														}
														?>
													</tbody>
												</table>
												<input type="hidden" name="tab_index" value="<?=$FORM->GetTabindex(false);?>" />
												<input type="hidden" name="mandatory_fields" value="{MANDATORY}" />
											</fieldset>
										</form>
									</div>
								</div>
							</div>
							<div class="footer"><div class="r"><div class="c"></div></div></div>
						</div>
					</td></tr></tbody></table><?
		$_html = ob_get_contents();
		ob_end_clean();
		$_html = str_replace('{TSUB}',$FORM->GetTabindex(),$_html);
		$_html = str_replace('{MANDATORY}',implode(',',$mandatory),$_html);
		echo $_html;
	}

	/**
	* добавление/сохранение записи
	*
	*/
	function Save(){
		global $DB, $POST, $URL, $ACCESS, $CACHE, $USER;

		$action = $URL->GetValue('action');
		$URL->SetValue('action','');
		if($POST->GetCount() > 0){
			if($URL->GetValue('cat')){
				$cur_fields = &$this->categories;
			}else{
				$cur_fields = &$this->fields;
			}

			$n = $cur_fields->count;
			$fields = array();
			$table = $cur_fields->table->name;
			$unique = true;
			$error = false;
			for($i=0;$i<$n;$i++){
				$cur_field = &$cur_fields->field[$i];
				//echo("<pre>");print_r($cur_field);echo("</pre>");
				$cur_field->db->value = $POST->GetValue($cur_field->form->name);

				$field = &$cur_field->db->field;
				$value = &$cur_field->db->value;
				$index = &$cur_field->db->index;
				$index = strtoupper($index);
				if($index == 'UNIQUE'){
					if($this->is_notunique($table,$field,$value,$POST->GetValue('id'))){
						$unique = false;
						break;
					}
				}

				$cur_type = $cur_field->form->type;
				$ar_type = explode('::',$cur_type);
				$sizeof_ar_type = sizeof($ar_type);
				$json = false;
				if($sizeof_ar_type < 2){
					$json_type = json_decode($cur_type,true);
					if(!empty($json_type)){
						$json = true;
						if(!empty($json_type['type'])){
							$cur_type = $json_type['type'];
						}
						//echo("<pre>");print_r($json_type);echo("</pre>");
					}
				}
				if(!$json){
					$cur_type = $ar_type[0];
				}
				switch($cur_type){
					case 'price':
						$value = str_replace(' ','',$value);
						break;

					case 'md5':
						$field2 = $ar_type[1];
						$i_field2 = $cur_fields->get_num_field($field2);
						$value = $cur_fields->field[$i_field2]->db->value;
						$value = md5($value);
						break;

					case 'access':
						$value = $ACCESS->GetValueFromForm();
						break;

					case 'calculate':
						if($json){
							$expression = null;
							if(!empty($json_type['expression'])){
								$expression = $json_type['expression'];
							}
							//echo("<pre>");print_r($json_type);echo("</pre>");
							if(!empty($expression)) {
								if (preg_match_all('|\[(.*?)\]|mi', $expression, $vars)) {
									if (!empty($vars[1])) {
										$array_replacement = array();
										foreach ($vars[1] as $field2) {
											$i_field2 = $cur_fields->get_num_field($field2);
											$value = $cur_fields->field[$i_field2]->db->value;
											$db_type = $cur_fields->field[$i_field2]->db->type;
											$db_type = strtoupper($db_type);
											switch ($db_type) {
												case 'TIME':
													$ar_value = explode(':', $value);
													$value = 0;
													for ($i_time = 0, $n_time = sizeof($ar_value); $i_time < $n_time; $i_time++) {
														$temp = intval($ar_value[$i_time]);
														if ($i_time > 0) {
															$temp = floatval($temp / (pow(60, $i_time)));
														}
														$value += $temp;
													}
											}
											$array_replacement['[' . $field2 . ']'] = $value;
										}
										$value = strtr($expression, $array_replacement);
										eval('$value = floatval(' . $value . ');');
										if(!empty($json_type['round'])){
											$value = round($value);
										}
									}
								}
							}
						}else{
							if(!empty($ar_type[1])) {
								$type = $ar_type[1];
								if (preg_match_all('|\[(.*?)\]|mi', $type, $vars)) {
									if (!empty($vars[1])) {
										$array_replacement = array();
										foreach ($vars[1] as $field2) {
											$i_field2 = $cur_fields->get_num_field($field2);
											$value = $cur_fields->field[$i_field2]->db->value;
											$db_type = $cur_fields->field[$i_field2]->db->type;
											$db_type = strtoupper($db_type);
											switch ($db_type) {
												case 'TIME':
													$ar_value = explode(':', $value);
													$value = 0;
													for ($i_time = 0, $n_time = sizeof($ar_value); $i_time < $n_time; $i_time++) {
														$temp = intval($ar_value[$i_time]);
														if ($i_time > 0) {
															$temp = floatval($temp / (pow(60, $i_time)));
														}
														$value += $temp;
													}
											}
											$array_replacement['[' . $field2 . ']'] = $value;
										}
										$value = strtr($type, $array_replacement);
										eval('$value = round(' . $value . ');');
									}
								}
							}
						}
						break;

					case 'list':
						//echo("<pre>");print_r($value);echo("</pre>");
						$ar_value = array();
						if(is_array($value)){
							$array_values = $POST->GetValue('hidden_'.$cur_field->form->name);
							if(is_array($array_values)){
								foreach($value as $key=>$val){
									$ar_value[] = $array_values[$key];
								}
								$value = json_encode($ar_value);
							}
						}
						break;

					case 'file':
						$width = 0;
						$height = 0;
						$file_type = '';
						$resize_mode = '';
						$sizes = array();
						$original_width = $original_height = 0;
						if($json){
							if(!empty($json_type['file_type'])){
								$file_type = $json_type['file_type'];
							}
							if(!empty($json_type['resize_mode'])){
								$resize_mode = $json_type['resize_mode'];
							}
							if(!empty($json_type['width'])){
								$width = $json_type['width'];
							}
							if(!empty($json_type['height'])){
								$height = $json_type['height'];
							}

							if(!empty($json_type['sizes'])){
								$sizes = $json_type['sizes'];
							}

							if(!empty($sizes['original']['width'])){
								$original_width = intval($sizes['original']['width']);
							}
							if(!empty($sizes['original']['height'])){
								$original_height = intval($sizes['original']['height']);
							}
						}else{
							if(!empty($ar_type[1])){
								$file_type = $ar_type[1];
							}
							if(!empty($ar_type[4])){
								$resize_mode = $ar_type[4];
							}
							if(!empty($ar_type[2])){
								$width = $ar_type[2];
							}
							if(!empty($ar_type[3])){
								$height = $ar_type[3];
							}
						}

						$value = $POST->GetValue('hidden_'.$cur_field->form->name);
						if(!empty($_SESSION['temp'][$value])){
							$value = $_SESSION['temp'][$value];
						}
						if($json_value = json_decode($value,true)){
							//echo("<pre>");print_r($json_value);echo("</pre>");
						}

						$width = intval($width);
						$height = intval($height);

						if(empty($_FILES[$cur_field->form->name]['name'])){
							$val_del = $POST->GetValue('delete_'.$cur_field->form->name);
							if(empty($val_del)){
								if(!empty($file_type)){
									switch($file_type){
										case 'image':
											if(!empty($json_value)){
												$image = new IMAGE();

												$destfile = array();
												if(!empty($json_value)){
													foreach($json_value as $name_type_file=>$file){
														if(!empty($file['src'])){
															$file_src = $file['src'];
															if($file_src[0] == '/'){
																$file_src = substr($file_src,1);
															}
															$destfile[$name_type_file] = DOCROOT.$file_src;
														}
													}
												}
												$img_name_sizes = array('original','normal','thumb');
												$additional_img_name_sizes = array();
												if(!empty($json_type['sizes'])){
													foreach($json_type['sizes'] as $img_name_size=>$item_value){
														if(!in_array($img_name_size,$img_name_sizes)){
															$img_name_sizes[] = $img_name_size;
															$additional_img_name_sizes[] = $img_name_size;
														}
													}
												}

												if(!empty($json_type['sizes']['normal'])){
													$watermark = '';
													if(!empty($json_type['sizes']['normal']['watermark'])){
														$watermark = $json_type['sizes']['normal']['watermark'];
													}
													$image->get_image_params($destfile['original']);
													$image->new_size($json_type['sizes']['normal']['width'],$json_type['sizes']['normal']['height']);
													$image->set_scale_size($json_type['sizes']['normal']['resize_mode']);
													if(!empty($watermark)){
														$watermark_src = $watermark['src'];
														if($watermark_src[0] == '/'){
															$watermark_src = substr($watermark_src,1);
														}
														$watermark['src'] = DOCROOT.$watermark_src;
														$stamp = new IMAGE();
														$width = $watermark['width'];
														$height = $watermark['height'];
														$stamp->get_image_params($watermark['src']);
														$watermark['width'] = $stamp->get_width();
														$watermark['height'] = $stamp->get_height();
														$stamp->new_size($width,$height);
														$stamp->set_scale_size('fit');
														$watermark['new_width'] = $stamp->get_width();
														$watermark['new_height'] = $stamp->get_height();

														$watermark['type'] = $stamp->get_file_type();
														$image->add_watermark($watermark);
													}
													$image->resize($destfile['normal']);
													$json_value['normal']['width'] = $image->get_width();
													$json_value['normal']['height'] = $image->get_height();
												}

												if(!empty($json_type['sizes']['thumb'])){
													$image->get_image_params($destfile['normal']);
													$image->new_size($json_type['sizes']['thumb']['width'],$json_type['sizes']['thumb']['height']);
													$image->set_scale_size($json_type['sizes']['thumb']['resize_mode']);
													$image->resize($destfile['thumb']);
													$json_value['thumb']['width'] = $image->get_width();
													$json_value['thumb']['height'] = $image->get_height();
												}

												if(!empty($additional_img_name_sizes)){
													foreach($additional_img_name_sizes as $img_name_size){
														$watermark = '';
														if(!empty($json_type['sizes'][$img_name_size]['watermark'])){
															$watermark = $json_type['sizes'][$img_name_size]['watermark'];
														}
														$image->get_image_params($destfile['original']);
														$image->new_size($json_type['sizes'][$img_name_size]['width'],$json_type['sizes'][$img_name_size]['height']);
														$image->set_scale_size($json_type['sizes'][$img_name_size]['resize_mode']);
														if(!empty($watermark)){
															$watermark_src = $watermark['src'];
															if($watermark_src[0] == '/'){
																$watermark_src = substr($watermark_src,1);
															}
															$watermark['src'] = DOCROOT.$watermark_src;
															$stamp = new IMAGE();
															$width = $watermark['width'];
															$height = $watermark['height'];
															$stamp->get_image_params($watermark['src']);
															$watermark['width'] = $stamp->get_width();
															$watermark['height'] = $stamp->get_height();
															$stamp->new_size($width,$height);
															$stamp->set_scale_size('fit');
															$watermark['new_width'] = $stamp->get_width();
															$watermark['new_height'] = $stamp->get_height();

															$watermark['type'] = $stamp->get_file_type();
															$image->add_watermark($watermark);
														}
														$image->resize($destfile[$img_name_size]);
														$json_value[$img_name_size]['width'] = $image->get_width();
														$json_value[$img_name_size]['height'] = $image->get_height();
													}
												}//*/
												$value = json_encode($json_value);
											}elseif(!empty($value)){
												if(!empty($resize_mode)){
													$maxwidth = intval($width);
													$maxheight = intval($height);
													switch($resize_mode){
														case 'adapt':	// may be resized
															$destfile = $POST->GetValue('hidden_'.$cur_field->form->name);
															if($destfile[0] == '/'){
																$destfile = DOCROOT.substr($destfile,1);
															}
															if(chmod($destfile,0777)){
																//Ok
															}
															$size = GetImageSize($destfile);  // thumbnail size
															$src_w = $size[0];
															$src_h = $size[1];
															if(($src_w > 0 or $src_h > 0) and ($maxwidth > 0 or $maxheight > 0)){
																if($maxwidth != $src_w or $maxheight != $src_h){

																	### Версия GD ###
																	$gdselected = 2;

																	// GD Function List
																	$gd_function_suffix = array(
																		'image/pjpeg' => 'JPEG',
																		'image/jpeg'  => 'JPEG',
																		'image/gif'   => 'GIF',
																		'image/bmp'   => 'WBMP',
																		'image/x-png' => 'PNG',
																		'image/png'   => 'PNG'
																	);

																	$ar_img = explode('/',$destfile);
																	$sizeof_ar_img = sizeof($ar_img)-1;
																	$ar_img[$sizeof_ar_img] = 'big_'.$ar_img[$sizeof_ar_img];
																	$sourcefile = implode('/',$ar_img);


																	if (preg_match("/\.gif/i",$sourcefile)) {$exp=".gif";}
																	if (preg_match("/\.png/i",$sourcefile)) {$exp=".png";}
																	if (preg_match("/\.jpg/i",$sourcefile)) {$exp=".jpg";}
																	if (preg_match("/\.jpeg/i",$sourcefile)) {$exp=".jpg";}
																	if (isset($exp)){
																		$size = GetImageSize($sourcefile);  // thumbnail size
																		$src_w = $size[0];
																		$src_h = $size[1];
																		if($src_w > 0 and $src_h > 0){
																			$filetype = $size['mime'];

																			if($maxwidth == 0 or $maxheight == 0){
																				if($maxheight == 0){
																					$maxheight = round($src_h*$maxwidth/$src_w);
																				}else{
																					$maxwidth = round($src_w*$maxheight/$src_h);
																				}
																			}

																			$dst_w = $maxwidth;
																			$dst_h = (int)($maxwidth * $src_h / $src_w);
																			if($dst_h < $maxheight){
																				$dst_h = $maxheight;
																				$dst_w = (int)($maxheight * $src_w / $src_h);
																			}

																			$function_suffix   = $gd_function_suffix[$filetype];
																			$function_to_read  = "ImageCreateFrom".$function_suffix;
																			$function_to_write = "Image".$function_suffix;
																			$img_quality = 100;
																			if($function_suffix == 'PNG'){
																				$img_quality = 9;
																			}

																			// read the source file
																			if (empty($sourcefile) || empty($function_suffix)) break;
																			$src_image = $function_to_read ($sourcefile);

																			$dst_x = ($maxwidth - $dst_w) / 2;
																			$dst_y = ($maxheight - $dst_h) / 2;

																			$src_x = 0;
																			$src_y = 0;

																			// Build Thumbnail with GD2
																			// create a blank image for the thumbnail
																			$dst_image = ImageCreateTrueColor($maxwidth, $maxheight );
																			$bg = imagecolorallocate ( $dst_image, 255, 255, 255 );
																			imagecolortransparent($dst_image, $bg);
																			imagefill ( $dst_image, 0, 0, $bg );
																			imagealphablending($dst_image,false);
																			imagesavealpha($dst_image, true);
																			// resize it
																			ImageCopyResampled($dst_image, $src_image, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h );
																			$function_to_write( $dst_image, $destfile, $img_quality );
																			ImageDestroy($src_image);
																			ImageDestroy($dst_image);
																			MODULES::Message("Изображение пересоздалось успешно",1);
																		}
																	}
																	//echo("<pre>");print_r($value);echo("</pre>");
																}
															}
															break;
														case 'fit':
															break;
														case 'fit_width':
															$destfile = $POST->GetValue('hidden_'.$cur_field->form->name);
															if($destfile[0] == '/'){
																$destfile = DOCROOT.substr($destfile,1);
															}
															chmod($destfile,0777);
															$size = GetImageSize($destfile);  // thumbnail size
															$resized_w = $src_w = $size[0];
															$resized_h = $src_h = $size[1];
															if(($src_w > 0 or $src_h > 0) and ($maxwidth > 0 or $maxheight > 0)){
																if($src_w > $maxwidth){

																	### Версия GD ###
																	$gdselected = 2;

																	// GD Function List
																	$gd_function_suffix = array(
																		'image/pjpeg' => 'JPEG',
																		'image/jpeg'  => 'JPEG',
																		'image/gif'   => 'GIF',
																		'image/bmp'   => 'WBMP',
																		'image/x-png' => 'PNG',
																		'image/png'   => 'PNG'
																	);

																	$ar_img = explode('/',$destfile);
																	$sizeof_ar_img = sizeof($ar_img)-1;
																	$ar_img[$sizeof_ar_img] = 'big_'.$ar_img[$sizeof_ar_img];
																	$sourcefile = implode('/',$ar_img);


																	if (preg_match("/\.gif/i",$sourcefile)) {$exp=".gif";}
																	if (preg_match("/\.png/i",$sourcefile)) {$exp=".png";}
																	if (preg_match("/\.jpg/i",$sourcefile)) {$exp=".jpg";}
																	if (preg_match("/\.jpeg/i",$sourcefile)) {$exp=".jpg";}
																	if (isset($exp)){
																		$size = GetImageSize($sourcefile);  // thumbnail size
																		$src_w = $size[0];
																		$src_h = $size[1];
																		if(($src_w > 0 and $src_h > 0) and (($resized_w != $src_w and $resized_h != $src_h) or ($resized_w > $maxwidth))){
																			$filetype = $size['mime'];

																			if($maxwidth == 0 or $maxheight == 0){
																				if($maxheight == 0){
																					$maxheight = round($src_h*$maxwidth/$src_w);
																				}else{
																					$maxwidth = round($src_w*$maxheight/$src_h);
																				}
																			}

																			if($src_w > $maxwidth){
																				$dst_w = $maxwidth;
																				$dst_h = (int)($maxwidth * $src_h / $src_w);
																			}else{
																				$maxwidth = $dst_w = $src_w;
																				$maxheight = $dst_h = $src_h;
																			}

																			$function_suffix   = $gd_function_suffix[$filetype];
																			$function_to_read  = "ImageCreateFrom".$function_suffix;
																			$function_to_write = "Image".$function_suffix;
																			$img_quality = 100;
																			if($function_suffix == 'PNG'){
																				$img_quality = 9;
																			}

																			// read the source file
																			if (empty($sourcefile) || empty($function_suffix)) break;
																			$src_image = $function_to_read ($sourcefile);

																			$dst_x = ($maxwidth - $dst_w) / 2;
																			$dst_y = ($maxheight - $dst_h) / 2;

																			$src_x = 0;
																			$src_y = 0;

																			// Build Thumbnail with GD2
																			// create a blank image for the thumbnail
																			$dst_image = ImageCreateTrueColor($maxwidth, $maxheight );
																			$bg = imagecolorallocate ( $dst_image, 255, 255, 255 );
																			imagecolortransparent($dst_image, $bg);
																			imagefill ( $dst_image, 0, 0, $bg );
																			imagealphablending($dst_image,false);
																			imagesavealpha($dst_image, true);
																			// resize it
																			ImageCopyResampled($dst_image, $src_image, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h );
																			$function_to_write( $dst_image, $destfile, $img_quality );
																			ImageDestroy($src_image);
																			ImageDestroy($dst_image);
																			MODULES::Message("Изображение пересоздалось успешно",1);
																		}
																	}
																	//echo("<pre>");print_r($value);echo("</pre>");
																}
															}
															break;
														case 'fit_height':
															break;
														case 'repeat':
															break;
														case 'center':
															break;
													}
												}
											}
											break;
									}
								}
							}else{	// удаление файла
								if(!empty($json_value)){
									foreach($json_value as $file){
										if(!empty($file['src'])){
											$file_src = $file['src'];
											if($file_src[0] == '/'){
												$file_src = substr($file_src,1);
											}
											unlink(DOCROOT.$file_src);
										}
									}
								}else{
									$ar_value = explode('/',$POST->GetValue('hidden_'.$cur_field->form->name));
									$ar_value2 = array();
									for($i_ar=0,$n_ar=sizeof($ar_value);$i_ar<$n_ar;$i_ar++){
										if(empty($ar_value[$i_ar])) unset($ar_value[$i_ar]);
										else $ar_value2[$i_ar] = $ar_value[$i_ar];
										if($i_ar+1>=$n_ar){
											$ar_value2[$i_ar] = 'big_'.$ar_value2[$i_ar];
										}
									}
									@unlink(DOCROOT.implode('/',$ar_value));
									@unlink(DOCROOT.implode('/',$ar_value2));
								}
								//if($ar_type[1] == 'name_to'){
								if($file_type == 'name_to'){
									if(!empty($ar_type[2])){
										$cur_fields->field[$cur_fields->get_num_field($ar_type[2])]->db->value = '';
									}
								}
								$value = '';
							}
						}else{
							// загрузка нового файла
							$uploaddir = DOCROOT.'uploads/';
							$file_name = basename($_FILES[$cur_field->form->name]['name']);

							if(!empty($file_type)){
								switch($file_type){
									case 'name_to':
										if($sizeof_ar_type > 2){
											$cur_fields->field[$cur_fields->get_num_field($ar_type[2])]->db->value = $file_name;
										}
										break;
									case 'image':
										$relative_path ='userfiles/images/'.$cur_fields->table->name.'/';
										$uploaddir = DOCROOT.$relative_path;
										$image = new IMAGE();
										$image->upload_dir_isset($uploaddir);

										if(!empty($json_value)){
											foreach($json_value as $image_file){
												if(!empty($image_file['src'])){
													$image_src = $image_file['src'];
													if($image_src[0] == '/'){
														$image_src = substr($image_src,1);
													}
													unlink(DOCROOT.$image_src);
												}
											}
										}

										$relative_path = '/'.$relative_path;
										$img_file_name = md5(date("His").$file_name);

										$file_name = "";
										$img = $_FILES[$cur_field->form->name]['name'];
										if(!empty($img)){
											$exp = $image->get_exp($img);

											$ar_file_name = array();
											$img_name_sizes = array('original','normal','thumb');
											$additional_img_name_sizes = array();
											if(!empty($json_type['sizes'])){
												foreach($json_type['sizes'] as $img_name_size=>$item_value){
													if(!in_array($img_name_size,$img_name_sizes)){
														$img_name_sizes[] = $img_name_size;
														$additional_img_name_sizes[] = $img_name_size;
													}
												}
											}
											foreach($img_name_sizes as $img_name_size){
												$ar_file_name[$img_name_size]['file_name'] = md5($img_name_size."_".$img_file_name).$exp;
												$ar_file_name[$img_name_size]['file_path'] = $img_name_size.'/';
												$ar_file_name[$img_name_size]['upload_dir'] = $uploaddir.$ar_file_name[$img_name_size]['file_path'];
												$ar_file_name[$img_name_size]['dest_file'] = $ar_file_name[$img_name_size]['upload_dir'].$ar_file_name[$img_name_size]['file_name'];
												$ar_file_name[$img_name_size]['relative_path'] = $relative_path.$ar_file_name[$img_name_size]['file_path'].$ar_file_name[$img_name_size]['file_name'];
											}

											$ar_value = array();
											$image->upload_dir_isset($ar_file_name['original']['upload_dir']);
											if($image->MoveUploadedFile($_FILES[$cur_field->form->name],$ar_file_name['original']['dest_file'])){
												$image->get_image_params($ar_file_name['original']['dest_file']);
												if(!empty($json_type['sizes']['original'])){
													$image->new_size($json_type['sizes']['original']['width'],$json_type['sizes']['original']['height']);
													$image->set_scale_size($json_type['sizes']['original']['resize_mode']);
												}
												$image->resize($ar_file_name['original']['dest_file']);
												$ar_value['original']['src'] = $ar_file_name['original']['relative_path'];
												$ar_value['original']['width'] = $image->get_width();
												$ar_value['original']['height'] = $image->get_height();

												if(!empty($json_type['sizes']['normal'])){
													$watermark = '';
													if(!empty($json_type['sizes']['normal']['watermark'])){
														$watermark = $json_type['sizes']['normal']['watermark'];
													}
													$image->upload_dir_isset($ar_file_name['normal']['upload_dir']);
													$ar_value['normal'] = array('src'=>$ar_file_name['normal']['relative_path']);
													$image->get_image_params($ar_file_name['original']['dest_file']);
													$image->new_size($json_type['sizes']['normal']['width'],$json_type['sizes']['normal']['height']);
													$image->set_scale_size($json_type['sizes']['normal']['resize_mode']);
													if(!empty($watermark)){
														$watermark_src = $watermark['src'];
														if($watermark_src[0] == '/'){
															$watermark_src = substr($watermark_src,1);
														}
														$watermark['src'] = DOCROOT.$watermark_src;
														$stamp = new IMAGE();
														$width = $watermark['width'];
														$height = $watermark['height'];
														$stamp->get_image_params($watermark['src']);
														$watermark['width'] = $stamp->get_width();
														$watermark['height'] = $stamp->get_height();
														$stamp->new_size($width,$height);
														$stamp->set_scale_size('fit');
														$watermark['new_width'] = $stamp->get_width();
														$watermark['new_height'] = $stamp->get_height();

														$watermark['type'] = $stamp->get_file_type();
														$image->add_watermark($watermark);
													}
													$image->resize($ar_file_name['normal']['dest_file']);
													$ar_value['normal']['src'] = $ar_file_name['normal']['relative_path'];
													$ar_value['normal']['width'] = $image->get_width();
													$ar_value['normal']['height'] = $image->get_height();
												}

												if(!empty($json_type['sizes']['thumb'])){
													$image->upload_dir_isset($ar_file_name['thumb']['upload_dir']);
													$image->get_image_params($ar_file_name['normal']['dest_file']);
													$image->new_size($json_type['sizes']['thumb']['width'],$json_type['sizes']['thumb']['height']);
													$image->set_scale_size($json_type['sizes']['thumb']['resize_mode']);
													$image->resize($ar_file_name['thumb']['dest_file']);
													$ar_value['thumb']['src'] = $ar_file_name['thumb']['relative_path'];
													$ar_value['thumb']['width'] = $image->get_width();
													$ar_value['thumb']['height'] = $image->get_height();
												}

												if(!empty($additional_img_name_sizes)){
													foreach($additional_img_name_sizes as $img_name_size){
														$watermark = '';
														if(!empty($json_type['sizes'][$img_name_size]['watermark'])){
															$watermark = $json_type['sizes'][$img_name_size]['watermark'];
														}
														$image->upload_dir_isset($ar_file_name[$img_name_size]['upload_dir']);
														$image->get_image_params($ar_file_name['original']['dest_file']);
														$image->new_size($json_type['sizes'][$img_name_size]['width'],$json_type['sizes'][$img_name_size]['height']);
														$image->set_scale_size($json_type['sizes'][$img_name_size]['resize_mode']);
														if(!empty($watermark)){
															$watermark_src = $watermark['src'];
															if($watermark_src[0] == '/'){
																$watermark_src = substr($watermark_src,1);
															}
															$watermark['src'] = DOCROOT.$watermark_src;
															$stamp = new IMAGE();
															$width = $watermark['width'];
															$height = $watermark['height'];
															$stamp->get_image_params($watermark['src']);
															$watermark['width'] = $stamp->get_width();
															$watermark['height'] = $stamp->get_height();
															$stamp->new_size($width,$height);
															$stamp->set_scale_size('fit');
															$watermark['new_width'] = $stamp->get_width();
															$watermark['new_height'] = $stamp->get_height();

															$watermark['type'] = $stamp->get_file_type();
															$image->add_watermark($watermark);
														}
														$image->resize($ar_file_name[$img_name_size]['dest_file']);
														$ar_value[$img_name_size]['src'] = $ar_file_name[$img_name_size]['relative_path'];
														$ar_value[$img_name_size]['width'] = $image->get_width();
														$ar_value[$img_name_size]['height'] = $image->get_height();
													}
												}
											}
											$value = json_encode($ar_value);
										}
										break 2;
								}
							}

							$file_name = date("His").$file_name;
							$file_name = md5($file_name);
							$uploadfile = $uploaddir . $file_name;
							$value = $file_name;

							if (move_uploaded_file($_FILES[$cur_field->form->name]['tmp_name'], $uploadfile)) {
								MODULES::Message("Файл загружен успешно",'success');

								if($action == 'check_edit'){
									$sql = "SELECT * ";
									$sql.= "FROM `".$cur_fields->table->name."` ";
									$id_num = $cur_fields->get_num_field(array('db->auto_increment'=>true));
									$sql.= "WHERE `".$cur_fields->field[$id_num]->db->field."` = '".$cur_fields->field[$id_num]->db->value."' ";
									if($DB->Query($sql)){
										if($DB->Next()){
											$old_file = $DB->Value($cur_field->form->name);
											if(!empty($old_file)){
												if(@unlink($uploaddir.$old_file)){
													MODULES::Message("Старый файл удален успешно",'success');
												}else{
													MODULES::Message("Старый файл не удален",'warning');
												}
											}
										}
									}
								}
							}else{
								MODULES::Message("Ошибка загрузки файла, код ошибки: ".$_FILES[$cur_field->form->name]['error'],'error');
								switch($_FILES[$cur_field->form->name]['error']){
									case '1':
										MODULES::Message("Размер принятого файла превысил максимально допустимый размер, который задан директивой upload_max_filesize конфигурационного файла php.ini.",'error');
										break;
									case '2':
										MODULES::Message("Размер загружаемого файла превысил значение MAX_FILE_SIZE, указанное в HTML-форме.",'error');
										break;
									case '3':
										MODULES::Message("Загружаемый файл был получен только частично.",'error');
										break;
									case '4':
										MODULES::Message("Файл не был загружен.",'error');
										break;
									case '6':
										MODULES::Message("Отсутствует временная папка.",'error');
										break;
									case '7':
										MODULES::Message("Не удалось записать файл на диск.",'error');
										break;
									case '8':
										MODULES::Message("PHP-расширение остановило загрузку файла. PHP не предоставляет способа определить какое расширение остановило загрузку файла; в этом может помочь просмотр списка загруженных расширений из phpinfo().",'error');
										break;
								}
							}
						}
						break;

					default:
						if($sizeof_ar_type > 1){
							switch($ar_type[1]){
								case 'md5':
									if(empty($value)){
										$value = null;
									}else{
										$value = md5($value);
									}
									break;
							}
						}
						break;
				}
			}
			$cur_module = $URL->GetValue('module');
			switch($cur_module){
				case 'projects':
				case 'projects_copywriting':
					$pid = $POST->GetValue('pid');
					$sql = "SELECT * ";
					$sql.= "FROM `categories_".$cur_module."` ";
					$sql.= "WHERE `id` = '".$pid."' ";
					if($DB->Query($sql)){
						if($DB->Next()){
							$for_email = array();
							$seo_user_id = $DB->Value('seo');
							$account_user_id = $DB->Value('account');
							$performer_user_id = $DB->Value('performer');
							//$concerned_user_id = $this->fields->field[$this->fields->get_num_field('concerned')]->db->value;
							$for_email[0]['id'] = $seo_user_id;			// СЕО специалист
							$for_email[1]['id'] = $account_user_id;		// Аккаунт менеджер
							$for_email[2]['id'] = null;
							//$for_email[2]['id'] = $concerned_user_id;	// заинтересованный
							$for_email[3]['id'] = $_SESSION['user_id'];	// текущий пользователь
							$for_email[4]['id'] = $performer_user_id;	// пользователь исполнитель

							$categories_seo_field = $this->categories->field[$this->categories->get_num_field('seo')]->form->type;
							$categories_seo_field = explode('pid=',$categories_seo_field);
							$categories_seo_pid = 0;
							if(sizeof($categories_seo_field) > 1 && empty($seo_user_id)){
								$categories_seo_pid = intval($categories_seo_field[1]);
							}

							//echo("<pre>");print_r($categories_seo_field);echo("</pre>");

							if((!empty($for_email[0]['id']) && !empty($for_email[1]['id'])) || !empty($for_email[2]['id'])){
								for($i=0,$n=sizeof($for_email);$i<$n;$i++){
									if(!empty($for_email[$i]['id'])){
										$sql = "SELECT * ";
										$sql.= "FROM `sys_users` ";
										$sql.= "WHERE `id` = '".$for_email[$i]['id']."'";
										if($DB->Query($sql)){
											if($DB->Next()){
												$for_email[$i]['email'] = $DB->Value('email');
												$for_email[$i]['name'] = $DB->Value('name');
											}
										}
									}
								}

								$i_status = $cur_fields->get_num_field('status');
								$_status = $cur_fields->field[$i_status]->db->value;
								$sql = "SELECT * ";
								$sql.= "FROM `project_status` ";
								$sql.= "WHERE `id` = '".$_status."'";
								$status_name = '';
								if($DB->Query($sql)){
									if($DB->Next()){
										$status_name = $DB->Value('name');
									}
								}

								$sql = "SELECT * ";
								$sql.= "FROM `sys_users` ";
								$sql.= "WHERE `active` > 0 ";
								$sql.= "AND (`notice_status` LIKE '%(".$_status."=>1%' ";
								$sql.= "OR `notice_status` LIKE '%,".$_status."=>1%') ";
								if(!empty($categories_seo_pid)){
									$sql.= "AND `pid` <> '".$categories_seo_pid."'";
								}
								$list_users = array();
								if($DB->Query($sql)){
									while($DB->Next()){
										$list_users[$DB->Value('pid')][$DB->Value('id')] = $DB->GetRecord();
									}
								}
								$notice_users = array();
								foreach($list_users as $user_pid=>$group_list_user){
									foreach($group_list_user as $id_user=>$item_user){
										if($id_user == $seo_user_id || $id_user == $account_user_id){
											$notice_users[$id_user] = $item_user;
											unset($list_users[$user_pid]);
										}
									}
								}
								foreach($list_users as $user_pid=>$group_list_user){
									foreach($group_list_user as $id_user=>$item_user){
										$notice_users[$id_user] = $item_user;
									}
								}

								if($_status == 1 || $_status == 9){							// 'Для согласования' или 'Для написания'
									unset($notice_users[$account_user_id]);
									$ToName		= $for_email[1]['name'];
									$ToEmail	= $for_email[1]['email'];
									$FromName	= $for_email[0]['name'];
									$FromEmail	= $for_email[0]['email'];
								}elseif($_status == 5 || $_status == 6 || $_status == 8){	// 'Выполнено' или 'Не полностью выполнено' или 'Не согласовано'
									unset($notice_users[$seo_user_id]);
									//unset($notice_users[$concerned_user_id]);
									$ToName		= $for_email[0]['name'];
									$ToEmail	= $for_email[0]['email'];
									$FromName	= $for_email[1]['name'];
									$FromEmail	= $for_email[1]['email'];
									/*$BccName	= $for_email[2]['name'];
									$BccEmail	= $for_email[2]['email'];
									if(empty($ToEmail)){
									$ToName		= $for_email[2]['name'];
									$ToEmail	= $for_email[2]['email'];
									$BccEmail	= '';
									}*/
									if(empty($FromEmail)){
										$FromName	= $for_email[3]['name'];
										$FromEmail	= $for_email[3]['email'];
									}
									if(!isset($notice_users[$account_user_id])){
										$notice_users[$for_email[1]['id']]['name'] = $for_email[1]['name'];
										$notice_users[$for_email[1]['id']]['email'] = $for_email[1]['email'];
									}
								}elseif($_status == 3){										// 'согласовано'
									//unset($notice_users[$concerned_user_id]);
									unset($notice_users[$performer_user_id]);
									$ToName		= $for_email[0]['name'];
									$ToEmail	= $for_email[0]['email'];
									$FromName	= $for_email[1]['name'];
									$FromEmail	= $for_email[1]['email'];
									if(empty($ToEmail)){
										$ToName		= $for_email[4]['name'];
										$ToEmail	= $for_email[4]['email'];
									}else{
										$BccName	= $for_email[4]['name'];
										$BccEmail	= $for_email[4]['email'];
									}
								}

								$name_tz = $cur_fields->field[$cur_fields->get_num_field('name')]->db->value;

								$Subject	= $name_tz.' ['.$status_name.']';
								ob_start();
								?><p>Здравствуйте.</p>
								<p>&nbsp;</p>
								<p>Название файла ТЗ: <?=$name_tz;?></p>
								<p>Статус: <?=$status_name;?></p>
								<p>&nbsp;</p>
								<p>----------</p>
								<p>С уважением, <?=$FromName;?>!</p><?
								$Message = ob_get_contents();
								ob_get_clean();
								$this->send_mail($Subject,$Message,$FromName,$FromEmail,$ToName,$ToEmail,$BccName,$BccEmail);

								foreach($notice_users as $notice_user){
									$ToName		= $notice_user['name'];
									$ToEmail	= $notice_user['email'];
									$FromName	= $ToName;
									$FromEmail	= $ToEmail;
									$Subject	= $name_tz.' ['.$status_name.']';
									ob_start();
									?><p>Здравствуйте, <?=$ToName;?>!</p>
									<p>&nbsp;</p>
									<p><strong>Название файла ТЗ</strong>: <?=$name_tz;?></p>
									<p><strong>Статус</strong>: <?=$status_name;?></p><?
									$Message = ob_get_contents();
									ob_get_clean();
									$this->send_mail($Subject,$Message,$FromName,$FromEmail,$ToName,$ToEmail);
								}
							}
						}
					}
					break;

				case 'sales':
					if($URL->GetValue('cat')){
						$send_mail = false;
						$who_processes = $POST->GetValue('who_processes');
						if($action == 'check_edit'){
							$sql = "SELECT * ";
							$sql.= "FROM `categories_".$cur_module."` ";
							$sql.= "WHERE `id` = '".$POST->GetValue('id')."' ";
							if($DB->Query($sql)){
								if($DB->Next()){
									if($DB->Value('who_processes') != $who_processes){
										$send_mail = true;
									}
								}
							}
						}elseif($action == 'check_add'){
							if(!empty($who_processes)){
								$send_mail = true;
							}
						}
						if($send_mail){
							$for_email = array();
							$for_email[0]['id'] = $who_processes;
							$for_email[1]['id'] = $_SESSION['user_id'];

							if(!empty($for_email[0]['id']) && !empty($for_email[1]['id'])){
								for($i=0,$n=sizeof($for_email);$i<$n;$i++){
									if(!empty($for_email[$i]['id'])){
										$sql = "SELECT * ";
										$sql.= "FROM `sys_users` ";
										$sql.= "WHERE `id` = '".$for_email[$i]['id']."'";
										if($DB->Query($sql)){
											if($DB->Next()){
												$for_email[$i]['email'] = $DB->Value('email');
												$for_email[$i]['name'] = $DB->Value('name');
											}
										}
									}
								}

								$ToName		= $for_email[0]['name'];
								$ToEmail	= $for_email[0]['email'];
								$FromName	= $for_email[1]['name'];
								$FromEmail	= $for_email[1]['email'];

								$Subject = 'Клиент из "отдела продаж" с сайта '.$_SERVER['HTTP_HOST'];
								ob_start();
								?><p>Здравствуйте, <?=$ToName;?>.</p>
								<p>&nbsp;</p><?
								for($i=0,$n=$this->fields->count;$i<$n;$i++){
									$cur_field = $this->categories->field[$i]->form->name;
									$cur_label = $this->categories->field[$i]->params->label;
									$cur_value = $POST->GetValue($cur_field);
									if(!empty($cur_value) && $cur_field != 'who_processes'){
										$cur_value = TEXT::html_del_tags($cur_value);
										?><p><strong><?=$cur_label;?></strong>: <?=$cur_value;?></p><?
									}
								}
								?>
								<p>&nbsp;</p>
								<p><a href="<?=$URL->GetURL();?>">Перейти в систему</a></p>
								<p>&nbsp;</p>
								<p>----------</p>
								<p>С уважением, <?=$FromName;?>!</p><?
								$Message = ob_get_contents();
								ob_get_clean();
								//echo $Message;
								if(!empty($ToName)){
									$FromName = "=?utf-8?B?".base64_encode($FromName)."?=";
									$From = $FromName . " <" . $FromEmail . ">";
									$ToName = "=?utf-8?B?".base64_encode($ToName)."?=";
									$To = $ToName . " <" . $ToEmail . ">";
									$Subject = "=?utf-8?B?".base64_encode($Subject)."?=";
									$Headers  = 'MIME-Version: 1.0' . "\n";
									$Headers .= 'Content-type: text/html; charset=UTF-8' . "\n";
									$Headers .= 'From: ' . $From . "\n";
									if (mail($To, $Subject, $Message, $Headers)){
										MODULES::Message("Сообщение успешно отправлено",'success');
									}else{
										MODULES::Message("Ошибка отправки сообщения",'error');
									}
								}
							}
						}
					}else{
						$pid = intval($POST->GetValue('pid'));
						$cur_date_end = $POST->GetValue('date_end');

						$sql = "SELECT * ";
						$sql.= "FROM `".$cur_module."` ";
						$sql.= "WHERE `pid` = '".$pid."' ";
						$sql.= "AND `status` NOT IN ('2','4') ";
						$sql.= "ORDER BY `date_end` ASC ";
						$sql.= "LIMIT 1 ";
						if($DB->Query($sql)){
							if($DB->Next()){
								if($cur_date_end > $DB->Value('date_end')){
									$cur_date_end = $DB->Value('date_end');
								}
							}
						}

						$sql = "UPDATE `categories_".$cur_module."` ";
						$sql.= "SET `date_end` = '".$cur_date_end."' ";
						$sql.= "WHERE `id` = '".$pid."' ";
						$DB->Query($sql);
					}
					break;

				default:
					// обнуление кеша по текущему модулю и всем модулям по регулярному вырожению названия модуля
					$CACHE->remove($cur_module,true);
					$DB->select();
					$DB->from('domains');
					if($DB->execute()){
						if($DB->NumRows() > 0){
							while($ar_domain = $DB->Next()){
								$current_domain = $ar_domain['name'];
								/*if($current_domain == $_SERVER['SERVER_NAME']){
								continue;
								}else{*/
								$new_cache = new Cache($current_domain);
								$new_cache->remove($cur_module);
								$new_cache->remove($cur_module,true);
								unset($new_cache);
								//}
							}
						}
						$DB->ClearDataSet();
					}
					break;
			}
			if($unique){
				$success = false;
				if(!$error){
					if($action == 'check_add'){
						if($this->InsertRowInTable($cur_fields)){
							MODULES::Message("Запись добавлена успешно",1);
							$success = true;
						}
					}elseif($action == 'check_edit'){
						if($this->UpdateRowInTable($cur_fields)){
							MODULES::Message("Запись изменена успешно",1);
							$success = true;
						}
					}
				}
				if($success){
					// Записывает в лог
					if($cur_module == 'projects'){
						$table_cat = 'categories_logs_projects';
						$name_tz = $cur_fields->field[$cur_fields->get_num_field('name')]->db->value;
						$sql = "SELECT * ";
						$sql.= "FROM `".$cur_module."` ";
						$sql.= "WHERE `name` = '".$name_tz."' ";
						if($DB->Query($sql)){
							$tz_elem = array();
							if($DB->Next()){
								$tz_elem = $DB->GetRecord();
								$DB->ClearDataSet();

								$sql = "SELECT * ";
								$sql.= "FROM `".$table_cat."` ";
								$sql.= "WHERE `id_elem` = '".$tz_elem['id']."' ";
								if($DB->Query($sql)){
									if($DB->NumRows() < 1){

										function GetTree($id,&$array,&$i=0){
											$DB = new DB();
											$sql = "SELECT * ";
											$sql.= "FROM `categories_projects` ";
											$sql.= "WHERE `id` = '".$id."' ";
											if($DB->Query($sql)){
												if($DB->Next()){
													$pid = $DB->Value('pid');
													$array[$i]['id'] = $id;
													$array[$i]['pid'] = $pid;
													$array[$i]['name'] = $DB->Value('name');
													$array[$i]['seo'] = $DB->Value('seo');
													$array[$i]['account'] = $DB->Value('account');
													if(!empty($pid)){
														GetTree($pid,$array,++$i);
													}
												}
											}
										}

										$ar_cats = $ar_ids = array();
										GetTree($tz_elem['pid'],$ar_ids);
										for($i=sizeof($ar_ids)-1,$n=0;$i>=$n;$i--){
											$ar_cats[] = $ar_ids[$i];
										}


										for($i=0,$n=sizeof($ar_cats);$i<$n;$i++){
											$el_id  = $ar_cats[$i]['id'];
											$el_pid = $ar_cats[$i]['pid'];
											$sql = "SELECT * ";
											$sql.= "FROM `".$table_cat."` ";
											$sql.= "WHERE `id_elem` = '".$el_id."' ";
											$sql.= "AND `cat` > '0' ";
											if($DB->Query($sql)){
												if($DB->NumRows() < 1){
													$vars = array();

													$vars['id'] = $DB->get_table_auto_increment($table_cat);

													$vars['id_elem'] = $el_id;

													$cat_pid = 0;
													$sql = "SELECT * ";
													$sql.= "FROM `".$table_cat."` ";
													$sql.= "WHERE `id_elem` = '".$ar_cats[$i]['pid']."' ";
													if($DB->Query($sql)){
														if($DB->Next()){
															$cat_pid = $DB->Value('id');
														}
													}
													$vars['pid'] = $cat_pid;

													$vars['name'] = $ar_cats[$i]['name'];

													$vars['seo'] = $ar_cats[$i]['seo'];

													$vars['account'] = $ar_cats[$i]['account'];

													$vars['cat'] = 1;

													if($DB->InsertRow($table_cat,$vars)){
														MODULES::Message("Новая запись в лог добавлена",1);
													}else{
														MODULES::Message("Ошибка добавления записи в лог",2);
													}
												}
											}
										}

										$vars = array();

										$vars['id'] = $DB->get_table_auto_increment($table_cat);

										$vars['id_elem'] = $tz_elem['id'];

										$cat_pid = 0;
										$sql = "SELECT * ";
										$sql.= "FROM `".$table_cat."` ";
										$sql.= "WHERE `id_elem` = '".$tz_elem['pid']."' ";
										if($DB->Query($sql)){
											if($DB->Next()){
												$cat_pid = $DB->Value('id');
											}
										}
										$vars['pid'] = $cat_pid;

										$vars['name'] = $tz_elem['name'];

										if($DB->InsertRow($table_cat,$vars)){
											MODULES::Message("Новая запись в лог добавлена",1);
										}else{
											MODULES::Message("Ошибка добавления записи в лог",2);
										}
									}
								}
							}
						}

						$table_item = 'logs_projects';
						$vars = array();

						$vars['id'] = $DB->get_table_auto_increment($table_item);

						$vars['id_elem'] = $tz_elem['id'];

						$cat_pid = 0;
						$sql = "SELECT * ";
						$sql.= "FROM `".$table_cat."` ";
						$sql.= "WHERE `id_elem` = '".$tz_elem['id']."' ";
						if($DB->Query($sql)){
							if($DB->Next()){
								$cat_pid = $DB->Value('id');
							}
						}
						$vars['pid'] = $cat_pid;

						$vars['name'] = $tz_elem['name'];

						$vars['file'] = $tz_elem['file'];

						$vars['user'] = $_SESSION['user_id'];

						$vars['date_begin'] = date("Y-m-d H:i:s");

						$vars['status'] = $tz_elem['status'];

						if(!$DB->UpdateRow($table_item,"`date_end` IS NULL",array('date_end'=>$vars['date_begin']))){
							MODULES::Message(" Ошибка обновления даты окончания предыдущего лога",2);
						}

						if($DB->InsertRow($table_item,$vars)){
							MODULES::Message("Новая запись в лог добавлена",1);
						}else{
							MODULES::Message("Ошибка добавления записи в лог",2);
						}
					}

					MODULES::Message('<meta http-equiv="refresh" content="0; url='.$URL->GetURL().'" />',0);
					unset($_SESSION['temp']);
					$POST->ClearFromSession();//*/
				}elseif($error){
					MODULES::Message("Исправте ошибки для полноценного функционирования",2);
				}else{
					MODULES::Message("По непонятным причинам запись не обновилась: проверьте настройки модуля",2);
				}
			}else{
				MODULES::Message("Поле `".$field."=".$value."` не уникально",2);
			}
		}else{
			MODULES::Message("Внимание!!! Изменения не были внесены, так как вы долго не проявляли активность.",'warning');
			MODULES::Message('<meta http-equiv="refresh" content="5; url='.$URL->GetURL().'" />',0);
		}
	}

	/**
	* Функция отправки почтового сообщения
	*
	* @param mixed $Subject
	* @param mixed $Message
	* @param mixed $FromName
	* @param mixed $FromEmail
	* @param mixed $ToName
	* @param mixed $ToEmail
	* @param mixed $BccName
	* @param mixed $BccEmail
	*/
	private function send_mail($Subject,$Message,$FromName,$FromEmail,$ToName,$ToEmail,$BccName=null,$BccEmail=null){
		if(!empty($ToEmail) && !empty($ToName)){
			$_FromName = "=?utf-8?B?".base64_encode($FromName)."?=";
			$From = $_FromName . " <" . $FromEmail . ">";
			$_ToName = "=?utf-8?B?".base64_encode($ToName)."?=";
			$To = $_ToName . " <" . $ToEmail . ">";
			$Subject = "=?utf-8?B?".base64_encode($Subject)."?=";
			$Headers  = 'MIME-Version: 1.0' . "\n";
			$Headers .= 'Content-type: text/html; charset=UTF-8' . "\n";
			$Headers .= 'From: ' . $From . "\n";
			if(!empty($BccEmail)){
				$Bcc_Name = "=?utf-8?B?".base64_encode($BccName)."?=";
				$Bcc = $Bcc_Name . " <" . $BccEmail . ">";
				$Headers .= 'Cc: ' . $Bcc . "\n";
			}
			if (mail($To, $Subject, $Message, $Headers)){
				MODULES::Message("Сообщение успешно отправлено от ".$FromName." к ".$ToName." ",'success');
				if(!empty($BccEmail)){
					MODULES::Message("Скрытая копия так-же отправлена от ".$FromName." к ".$BccName." ",'success');
				}
			}else{
				MODULES::Message("Ошибка отправления сообщения",'error');
			}
			return true;
		}
		return false;
	}

	/**
	* Массив типов данных для которых если не заполнено поле не изменяется
	*
	*/
	private function GetArraySpecialTypes(){
		$ar_types = array();

		$ar_types[] = 'file';
		$ar_types[] = 'password';

		return $ar_types;
	}

	/**
	* проверяет, не является ли поле специального типа
	*
	* @param mixed $type
	* @param mixed $value
	*/
	private function IsNotSpecialType($type,$value){
		$ar_types = $this->GetArraySpecialTypes();
		$ar_type = explode('::',$type);
		$special = false;
		for($i=0,$n=sizeof($ar_type);$i<$n;$i++){
			if(in_array($ar_type[$i],$ar_types)){
				$special = true;
			}
		}
		if(!isset($value) && $special){
			return false;
		}
		return true;
	}

	/**
	* добавляет новую строку в таблицу из данных $cur_fields
	*
	* @param mixed $cur_fields
	*/
	private function InsertRowInTable($cur_fields){
		global $DB;
		$n = $cur_fields->count;
		if ($n > 1){
			$vars = array();
			$values = array();
			for($i=0;$i<$n;$i++){
				$cur_field = $cur_fields->field[$i]->db;
				if(!empty($cur_field->field)){
					if($this->IsNotSpecialType($cur_fields->field[$i]->form->type,$cur_field->value)){
						$vars[] = $cur_field->field;
						$values[] = $DB->real_escape_string($cur_field->value);
					}
				}
			}
			$name_table = $cur_fields->table->name;
			$sql = "INSERT INTO `".$name_table."` (`".implode('`,`',$vars)."`) VALUES ('".implode("','",$values)."')";
			if($DB->Query($sql)){
				return TRUE;
			}
		}
		return FALSE;
	}

	/**
	* обновляет строку в таблице данными из $cur_fields
	*
	* @param mixed $cur_fields
	*/
	private function UpdateRowInTable($cur_fields){
		global $DB, $ACCESS;
		$n = $cur_fields->count;
		if ($n > 1){
			$vars = array();
			$values = array();
			for($i=0;$i<$n;$i++){
				$cur_field = $cur_fields->field[$i];
				$cur_db_field = $cur_field->db;
				if(isset($cur_db_field->field)){
					if($cur_fields->field[$i]->edit->edit && $ACCESS->FieldIsAccess($cur_field->access)){
						if($this->IsNotSpecialType($cur_field->form->type,$cur_db_field->value)){
							$vars[] = $cur_db_field->field;
							$values[] = $DB->real_escape_string($cur_db_field->value);
						}
					}
				}
			}
			$name_table = $cur_fields->table->name;
			$sql = "UPDATE `".$name_table."` SET ";
			for($i=0,$n=sizeof($vars);$i<$n;$i++){
				if($i > 0){
					$sql.= ", ";
				}
				$sql.= "`".$vars[$i]."` = '".$values[$i]."'";
			}
			$num_field_id = $cur_fields->get_num_field('id');
			if($num_field_id < 0){
				$num_field_id = 0;
			}
			$sql.= " WHERE `".$cur_fields->field[$num_field_id]->db->field."` = '".$cur_fields->field[$num_field_id]->db->value."'";
			//echo $sql;
			//die();
			if($n > 0){
				if($DB->Query($sql)){
					return TRUE;
				}
			}
		}
		return FALSE;
	}

	/**
	* выводит предупреждение и подготавливает удаление выбранного элемента
	*
	*/
	function Delete(){
		global $DB, $POST, $URL;
		$cur_fields = &$this->fields;
		if($URL->GetValue('cat')){
			$cur_fields = &$this->categories;
		}

		$URL->SetValue('action','check_delete');
		$id_field = $this->get_field_id($cur_fields);
		$id = $POST->GetValue($id_field);
		if(!empty($id)){
			$table = $cur_fields->table->name;
			if(is_array($id)){
				$sql = "SELECT * FROM `".$table."` WHERE `".$id_field."` IN ('".implode("','",$id)."');";
				//echo $sql.'<br>';
				if($DB->Query($sql)){
					if($DB->NumRows() > 0){
						$ar_show = $cur_fields->GetValuesByValue('show::delete',true);
						$msg = $cur_fields->titles->delete;
						$hidden_inputs = '';
						?><form id="f_check_delete" action="<?=$URL->GetURL();?>" method="post">
							<fieldset>
								<table>
									<thead><tr><th colspan="2"><?=$msg;?></th></tr></thead>
									<tfoot><tr><th colspan="2"><input type="submit" value="Удалить" /></th></tr></tfoot>
									<tbody><?
										while($DB->Next()){
											$row = $DB->GetRecord();
											ob_start();
											?><input type="hidden" name="<?=$id_field;?>[]" value="<?=$row[$id_field];?>" /><?
											$hidden_inputs.= ob_get_contents(); ob_get_clean();
											?><?
											for($i=0,$n=sizeof($ar_show);$i<$n;$i++){
												$cur_field = $cur_fields->field[$ar_show[$i]];
												$label = $cur_field->params->label;
												$value = $row[$cur_field->db->field];
												?><tr><th><?=$label;?>:</th><td><?=$value;?></td></tr><?
											}
											?><tr><td colspan="2"><hr /></td></tr><?
										}
									?></tbody>
								</table><?
								echo $hidden_inputs;
							?></fieldset>
						</form><?
					}
				}
			}else{
				$sql = "SELECT * FROM `".$table."` WHERE `".$id_field."` = '".$id."';";
				if($DB->Query($sql)){
					if($DB->Next()){
						$row = $DB->GetRecord();
						$ar_show = $cur_fields->GetValuesByValue('show::delete',true);
						$msg = $cur_fields->titles->delete;
						?><form id="f_check_delete" action="<?=$URL->GetURL();?>" method="post">
							<fieldset>
								<table>
									<thead><tr><th colspan="2"><?=$msg;?></th></tr></thead>
									<tfoot><tr><th colspan="2"><input type="hidden" name="<?=$id_field;?>" value="<?=$row[$id_field];?>" /><input type="submit" value="Удалить" /></th></tr></tfoot>
									<tbody>
										<?
										for($i=0,$n=sizeof($ar_show);$i<$n;$i++){
											$cur_field = $cur_fields->field[$ar_show[$i]];
											$label = $cur_field->params->label;
											$value = $row[$cur_field->db->field];
											?><tr><th><?=$label;?>:</th><td><?=$value;?></td></tr><?
										}
										?>
									</tbody>
								</table>
							</fieldset>
						</form><?
					}
				}
			}
		}
	}

	/**
	* получение иерархии дочерних элементов неограниченной вложенности
	*
	* @param mixed $table
	* @param mixed $pid
	* @param mixed $array
	*/
	private function GetTree($table,$pid,&$array){
		$DB = new DB();
		$sql = "SELECT * ";
		$sql.= "FROM `".$table."` ";
		$sql.= "WHERE `pid` = '".$pid."' ";
		if($DB->Query($sql)){
			while($DB->Next()){
				$id = $DB->Value($this->categories->field[0]->db->field);
				$array[] = $id;
				if(!empty($id)){
					$this->GetTree($table,$id,$array);
				}
			}
			$DB->ClearDataSet();
		}
	}

	/**
	* удаляет элемент без предупреждения
	*
	*/
	function CheckDelete(){
		global $DB, $POST, $URL;

		$DB2 = new DB();

		$cur_fields = &$this->fields;
		if($URL->GetValue('cat')){
			$cur_fields = &$this->categories;
		}

		$URL->SetValue('action','');
		$id_field = $this->get_field_id($cur_fields);
		$id = $POST->GetValue($id_field);
		if(!empty($id)){
			if(is_array($id)){	// выделено несколько элементов для удаления
				$cur_fields = $this->fields;
				if($URL->GetValue('cat')){
					$ar_cats = $ar_ids = array();
					$ar_cats = $id;
					foreach($id as $id_item){
						$this->GetTree($this->categories->table->name,$id_item,$ar_cats);
					}

					$ar_elements = array();
					$cur_fields = $this->fields;
					$table_fields = $cur_fields->table->name;
					$sql = "SELECT * FROM `".$table_fields."` WHERE `pid` IN ('".implode("','",$ar_cats)."')";
					$field_file = '';
					$num_field = $cur_fields->get_num_field(array('form->type'=>'file'),true);
					if($num_field >= 0){
						$field_file = $cur_fields->field[$num_field]->db->field;
					}

					$field_id = $this->get_field_id($cur_fields);

					$ar_files = array();
					if($DB->Query($sql)){
						while($DB->Next()){
							$id = $DB->Value($field_id);
							$ar_elements[] = $id;
							$ar_files[] = $DB->Value($field_file);
						}
						$DB->ClearDataSet();
					}
					$table = '';
					if($this->name == 'projects'){
						$table = 'logs_projects';
					}elseif($this->name == 'logs_projects'){
						$table = 'projects';
					}
					if(!empty($table)){
						$field_file = '';
						$num_field = $this->fields->get_num_field(array('form->type'=>'file'),true);
						if($num_field >= 0){
							$field_file = $this->fields->field[$num_field]->db->field;
						}
						for($i=0,$n=sizeof($ar_files);$i<$n;$i++){
							if(empty($ar_files[$i])){
								unset($ar_files[$i]);
							}else{
								$sql = "SELECT * FROM `".$table."` WHERE `".$field_file."` LIKE '".$ar_files[$i]."'";
								if($DB->Query($sql)){
									if($DB->NumRows() > 0){
										unset($ar_files[$i]);
									}
								}
							}
						}
					}

					$success = true;
					$uploaddir = DOCROOT.'uploads/';
					for($i=0,$n=sizeof($ar_files);$i<$n;$i++){
						if(!empty($ar_files[$i])){
							if($json_old_file = json_decode($ar_files[$i],true)){
								foreach($json_old_file as $file_name){
									$file = '';
									if(!empty($file_name['src'])){
										$file = $file_name['src'];
									}
									if(!empty($file)){
										if($file[0] == '/'){
											$file = substr($file,1);
										}
										$upload_file = DOCROOT.$file;
										if(unlink($upload_file)){
											MODULES::Message("Связанный файл удален успешно",'success');
										}else{
											MODULES::Message("Связанный файл не удален",'warning');
										}
									}
								}
							}else{
								if(@unlink($uploaddir.$ar_files[$i])){
									MODULES::Message("Файл удален успешно",'success');
								}else{
									MODULES::Message("Ошибка удаления файла",'warning');
								}
							}
						}
					}

					if(!empty($ar_elements) && $success){
						$cur_fields = $this->fields;
						$field_id = $this->get_field_id($cur_fields);
						$sql = "DELETE FROM `".$cur_fields->table->name."` WHERE `".$field_id."` IN ('".implode("','",$ar_elements)."')";
						if($DB->Query($sql)){
							MODULES::Message("Записи в заданной категории удалены успешно",'success');
						}else{
							MODULES::Message("Ошибка удаления записей в заданной категории",'warning');
							$success = false;
						}
					}

					if(!empty($ar_cats) && $success){
						$cur_fields = $this->categories;
						$field_id = $this->get_field_id($cur_fields);
						$sql = "DELETE FROM `".$cur_fields->table->name."` WHERE `".$field_id."` IN ('".implode("','",$ar_cats)."')";
						if($DB->Query($sql)){
							MODULES::Message("Категории удалены успешно",'success');
						}else{
							MODULES::Message("Ошибка удаления категории",'warning');
						}
					}
					//$success = false;
					if($success){
						MODULES::Message('<meta http-equiv="refresh" content="0; url='.$URL->GetURL().'" />',0);
						$POST->ClearFromSession();
					}//*/
				}else{
					$table = $cur_fields->table->name;
					$field_id = $this->get_field_id($cur_fields);
					if(!empty($field_id)){
						$sql = "SELECT * FROM `".$table."` WHERE `".$field_id."` IN ('".implode("','",$id)."');";
						//echo $sql."<br>";
						if($DB->Query($sql)){
							$field_file = '';
							$num_field = $cur_fields->get_num_field(array('form->type'=>'file'),true);
							if($num_field >= 0){
								$field_file = $cur_fields->field[$num_field]->db->field;
							}
							while($DB->Next()){
								$row = $DB->GetRecord();
								if(!empty($field_file)){
									$old_file = $row[$field_file];
									if(!empty($old_file)){
										$may_delete = true;

										$table2 = '';
										if($this->name == 'projects'){
											$table2 = 'logs_projects';
										}elseif($this->name == 'logs_projects'){
											$table2 = 'projects';
										}
										if(!empty($table2)){
											$sql = "SELECT * FROM `".$table2."` WHERE `".$field_file."` LIKE '".$old_file."'";
											if($DB2->Query($sql)){
												if($DB2->NumRows() > 0){
													$may_delete = false;
												}
											}
										}

										if($may_delete){
											if($json_old_file = json_decode($old_file,true)){
												foreach($json_old_file as $file_name){
													$file = '';
													if(!empty($file_name['src'])){
														$file = $file_name['src'];
													}
													if(!empty($file)){
														if($file[0] == '/'){
															$file = substr($file,1);
														}
														$upload_file = DOCROOT.$file;
														if(unlink($upload_file)){
															MODULES::Message("Связанный файл удален успешно",'success');
														}else{
															MODULES::Message("Связанный файл не удален",'warning');
														}
													}
												}
											}else{
												$uploaddir = DOCROOT.'uploads/';
												if(@unlink($uploaddir.$old_file)){
													MODULES::Message("Старый файл удален успешно",'success');
												}else{
													MODULES::Message("Старый файл не удален",'warning');
												}
											}
										}
									}
								}
							}
						}
						//echo 123;
						$sql = "DELETE FROM `".$table."` WHERE `".$field_id."` IN ('".implode("','",$id)."');";
						if($DB->Query($sql)){
							MODULES::Message("Запись удалена успешно",1);
							MODULES::Message('<meta http-equiv="refresh" content="0; url='.$URL->GetURL().'" />',0);
							$POST->ClearFromSession();
						}
					}
				}
			}else{// выделен только один элемент
				$cur_fields = &$this->fields;
				if($URL->GetValue('cat')){
					$ar_cats = $ar_ids = array();
					$ar_cats[] = $id;
					$this->GetTree($this->categories->table->name,$id,$ar_cats);

					$ar_elements = array();
					$cur_fields = &$this->fields;
					$table_fields = $cur_fields->table->name;
					$sql = "SELECT * FROM `".$table_fields."` WHERE `pid` IN ('".implode("','",$ar_cats)."')";
					$field_file = '';
					$num_field = $cur_fields->get_num_field(array('form->type'=>'file'),true);
					if($num_field >= 0){
						$field_file = $cur_fields->field[$num_field]->db->field;
					}

					$field_id = $this->get_field_id($cur_fields);
					$ar_files = array();
					if($DB->Query($sql)){
						while($DB->Next()){
							$id = $DB->Value($field_id);
							$ar_elements[] = $id;
							$ar_files[] = $DB->Value($field_file);
						}
						$DB->ClearDataSet();
					}
					$table = '';
					if($this->name == 'projects'){
						$table = 'logs_projects';
					}elseif($this->name == 'logs_projects'){
						$table = 'projects';
					}
					if(!empty($table)){
						$field_file = '';
						$num_field = $this->fields->get_num_field(array('form->type'=>'file'),true);
						if($num_field >= 0){
							$field_file = $this->fields->field[$num_field]->db->field;
						}
						for($i=0,$n=sizeof($ar_files);$i<$n;$i++){
							if(empty($ar_files[$i])){
								unset($ar_files[$i]);
							}else{
								$sql = "SELECT * FROM `".$table."` WHERE `".$field_file."` LIKE '".$ar_files[$i]."'";
								if($DB->Query($sql)){
									if($DB->NumRows() > 0){
										unset($ar_files[$i]);
									}
								}
							}
						}
					}

					$success = true;
					$uploaddir = DOCROOT.'uploads/';
					for($i=0,$n=sizeof($ar_files);$i<$n;$i++){
						if(!empty($ar_files[$i])){
							$old_file = $ar_files[$i];
							if($json_old_file = json_decode($old_file,true)){
								foreach($json_old_file as $file_name){
									$file = '';
									if(!empty($file_name['src'])){
										$file = $file_name['src'];
									}
									if(!empty($file)){
										if($file[0] == '/'){
											$file = substr($file,1);
										}
										$upload_file = DOCROOT.$file;
										if(unlink($upload_file)){
											MODULES::Message("Связанный файл удален успешно",'success');
										}else{
											MODULES::Message("Связанный файл не удален",'warning');
										}
									}
								}
							}else{
								if(@unlink($uploaddir.$old_file)){
									MODULES::Message("Файл удален успешно",'success');
								}else{
									MODULES::Message("Ошибка удаления файла",'warning');
								}
							}
						}
					}

					if(!empty($ar_elements) && $success){
						$cur_fields = $this->fields;

						$field_id = $this->get_field_id($cur_fields);
						$sql = "DELETE FROM `".$cur_fields->table->name."` WHERE `".$field_id."` IN ('".implode("','",$ar_elements)."')";
						if($DB->Query($sql)){
							MODULES::Message("Записи в заданной категории удалены успешно",'success');
						}else{
							MODULES::Message("Ошибка удаления записей в заданной категории",'warning');
							$success = false;
						}
					}

					if(!empty($ar_cats) && $success){
						$cur_fields = $this->categories;

						$field_id = $this->get_field_id($cur_fields);
						$sql = "DELETE FROM `".$cur_fields->table->name."` WHERE `".$field_id."` IN ('".implode("','",$ar_cats)."')";
						if($DB->Query($sql)){
							MODULES::Message("Категории удалены успешно",'success');
						}else{
							MODULES::Message("Ошибка удаления категории",'warning');
						}
					}
					//$success = false;
					if($success){
						MODULES::Message('<meta http-equiv="refresh" content="0; url='.$URL->GetURL().'" />',0);
						$POST->ClearFromSession();
					}
				}else{
					$table = $cur_fields->table->name;

					$sql = "SELECT * FROM `".$table."` WHERE `".$id_field."` = '".$id."';";
					if($DB->Query($sql)){
						if($DB->Next()){
							$row = $DB->GetRecord();
							$field_file = '';
							$num_field = $cur_fields->get_num_field(array('form->type'=>'file'),true);
							if($num_field >= 0){
								$field_file = $cur_fields->field[$num_field]->db->field;
								$old_file = $row[$field_file];
								if(!empty($old_file)){
									$may_delete = true;

									$table2 = '';
									if($this->name == 'projects'){
										$table2 = 'logs_projects';
									}elseif($this->name == 'logs_projects'){
										$table2 = 'projects';
									}
									if(!empty($table2)){
										$sql = "SELECT * FROM `".$table2."` WHERE `".$field_file."` LIKE '".$old_file."'";
										if($DB->Query($sql)){
											if($DB->NumRows() > 0){
												$may_delete = false;
											}
										}
									}
									if($may_delete){
										if($json_old_file = json_decode($old_file,true)){
											foreach($json_old_file as $file_name){
												$file = '';
												if(!empty($file_name['src'])){
													$file = $file_name['src'];
												}
												if(!empty($file)){
													if($file[0] == '/'){
														$file = substr($file,1);
													}
													$upload_file = DOCROOT.$file;
													if(unlink($upload_file)){
														MODULES::Message("Связанный файл удален успешно",'success');
													}else{
														MODULES::Message("Связанный файл не удален",'warning');
													}
												}
											}
										}else{
											$uploaddir = DOCROOT.'uploads/';
											if(@unlink($uploaddir.$old_file)){
												MODULES::Message("Старый файл удален успешно",'success');
											}else{
												MODULES::Message("Старый файл не удален",'warning');
											}
										}
									}
								}
							}
						}
					}

					$sql = "DELETE FROM `".$table."` WHERE `".$id_field."` = '".$id."';";
					if($DB->Query($sql)){
						MODULES::Message("Запись удалена успешно",1);
						MODULES::Message('<meta http-equiv="refresh" content="0; url='.$URL->GetURL().'" />',0);
						$POST->ClearFromSession();
					}//*/
				}
			}
		}
	}

	/**
	* возвращает true если значение $value уникально в поле $field в таблице $table
	* исключая элемент с заданным $id (для текущего элемента)
	*
	* @param mixed $table
	* @param mixed $field
	* @param mixed $value
	* @param mixed $id
	*/
	private function is_notunique($table,$field,$value,$id=''){
		global $DB;
		$sql = "SELECT `".$field."` ";
		$sql.= "FROM `".$table."` ";
		$sql.= "WHERE `".$field."` = '".$value."' ";
		if(!empty($id)){
			$sql.= "AND `id` <> ".$id." ";
		}
		$sql.= "LIMIT 1;";
		if($DB->Query($sql)){
			if($DB->Next()){
				return true;
			}
		}
		return false;
	}

	/**
	* выводит панель управления:
	* фильтр, кнопки добавления
	*
	* @param mixed $cat
	*/
	private function ControlPanel($cat=0){
		global $POST, $URL, $ACCESS;
		$num_cat_pid = $this->fields->get_num_field(array('form->type'=>'select::'.$this->categories->table->name.'::'),true);
		if($num_cat_pid < 0){
			$field_section_id = 'pid';
		}else{
			$field_section_id = $this->fields->field[$num_cat_pid]->db->field;
		}

		$num_section_id = $this->categories->get_num_field(array('form->type'=>'select::'.$this->categories->table->name.'::'),true);
		if($num_section_id < 0){
			$num_section_id = $this->categories->get_num_field(array('form->type'=>'select::parent::'),true);
		}
		if($num_section_id < 0){
			$field_cat_pid = 'pid';
		}else{
			$field_cat_pid = $this->categories->field[$num_section_id]->db->field;
		}

		$pid = intval($URL->GetValue($field_section_id));
		$URL_sort = new URL();
		?><div class="control_panel"><?

			////////////////////////////////////////////////
			$show = false;
			$URL->SetValue('action','add');
			$URL->SetValue('cat',$cat);
			if($cat > 0){
				$cur_fields = &$this->categories;
			}else{
				$cur_fields = &$this->fields;
			}
			$_pref = $cur_fields->table->name.'_';

			$table = $cur_fields->table->name;

			$multiline = $cur_fields->table->multiline;
			$title = $cur_fields->titles->add;
			if($multiline){
				$show = true;
			}

			$max_level = $cur_fields->max_level;
			$level = 0;
			if(!empty($pid) && $cat){
				$this->GetLevel($table,'id',$field_cat_pid,$pid,$level);
			}
			//echo("<pre>");print_r($ACCESS->ar_value[$this->name]);echo("</pre>");
			$show_filter = true;
			if($max_level > 0 && $max_level <= $level){
				$show = false;
				$show_filter = false;
			}elseif(!$ACCESS->ar_value[$this->name]['add']){
				$show = false;
			}elseif($show){
				?><form action="<?=$URL->GetURL();?>" method="post"><fieldset style="border: none; margin: 0px; padding: 0px;"><input type="submit" name="add" value="<?=$title;?>" /></fieldset></form><?
			}
			///////////////////////////////////////////////////////////////////
			//$this->button_add($cat);
			/*if($cat > 0){
			$cur_fields = $this->categories;
			}else{
			$cur_fields = $this->fields;
			}*/
			if($show_filter){
				$only_one = true;
				$date_item = 0;
				ob_start();
				for($i_f=0,$n_f=$cur_fields->count;$i_f<$n_f;$i_f++){
					$cur_field = $cur_fields->field[$i_f];
					if($cur_field->params->filtering){
						//echo $cur_field->form->name."<br>";
						switch(strtoupper($cur_field->db->type)){
							case 'DATETIME':
							case 'DATE':
								$sql = "SELECT MIN(`".$cur_field->db->field."`) AS `min` FROM `".$cur_fields->table->name."` ";
								$DB = new DB();
								$year_cur = date("Y");
								$year_min = $year_cur;
								if($DB->Query($sql)){
									if($DB->Next()){
										$year_min_in_base = $DB->Value('min');
										$year_min_in_base = explode('-',$year_min_in_base);
										$year_min_in_base = $year_min_in_base[0];
										if($year_min_in_base > 0){
											$year_min = $year_min_in_base;
										}
									}
									$DB->ClearDataSet();
								}
								//echo $year_min;
								$ar_month = array(1=>"Январь","Февраль","Март","Апрель","Май","Июнь","Июль","Август","Сентябрь","Октябрь","Ноябрь","Декабрь");
								//$ar_date = array("begin","end");
								$ar_date = array(
									0=>array(
										'name'=>'begin',
										'label'=>$cur_field->params->label.' с :',
										'sign'=>'>='
									),
									1=>array(
										'name'=>'end',
										'label'=>'по :',
										'sign'=>'<='
									)
								);
								$form_value_null_begin = '0000-00-00 00:00:00';
								$form_value_null_end = '0000-00-00 23:59:59';
								for($i_date = 0, $n_date = sizeof($ar_date); $i_date < $n_date; $i_date++){
									$form_name = $_pref.$cur_field->db->field."_".$ar_date[$i_date]['name']."_".$date_item;
									$selected = $POST->GetValue($form_name);
									$form_value = $selected;
									if(empty($selected) && isset($_SESSION[$table][$pid][$cur_field->db->field][$ar_date[$i_date]['name']]['value'])){
										$selected = $_SESSION[$table][$pid][$cur_field->db->field][$ar_date[$i_date]['name']]['value'];
										$form_value = $selected;
										if(empty($selected)){
											if ($ar_date[$i_date]['name'] == 'begin')
												$selected = $form_value_null_begin;
											elseif ($ar_date[$i_date]['name'] == 'end')
												$selected = $form_value_null_end;
										}
									}else{
										if($only_one){
											unset($_SESSION[$table][$pid]);
											$only_one = false;
										}
										$_SESSION[$table][$pid][$cur_field->db->field][$ar_date[$i_date]['name']]['value'] = $selected;
										$_SESSION[$table][$pid][$cur_field->db->field][$ar_date[$i_date]['name']]['sign'] = $ar_date[$i_date]['sign'];
									}
									if($form_value == $form_value_null_begin || $form_value == $form_value_null_end || empty($form_value)){
										$form_value = 'all';
									}
									$ar_selected = explode(' ',$selected);
									$ar_selected = $ar_selected[0];
									$ar_selected = explode('-',$ar_selected);
									foreach($ar_selected as &$val_select){
										$val_select = intval($val_select);
									}

									?><label for="<?=$_pref;?>id_<?=$form_name;?>_year"><?=$ar_date[$i_date]['label'];?></label>
									<select id="<?=$_pref;?>id_<?=$form_name;?>_year" name="<?=$form_name;?>_year" title="Год">
										<option value="all">-------</option><?
										for($i_year=$year_min;$i_year<=$year_cur;$i_year++){
											?><option value="<?=$i_year;?>"<?if(isset($ar_selected[0]) && $ar_selected[0] == $i_year){?> selected="selected"<?}?>><?=$i_year;?></option><?
										}
									?></select>&nbsp;-&nbsp;
									<select id="<?=$_pref;?>id_<?=$form_name;?>_month" name="<?=$form_name;?>_month" title="Месяц">
										<option value="all">-------</option><?
										for($i_month=1,$n_month=sizeof($ar_month);$i_month<=$n_month;$i_month++){
											?><option value="<?=$i_month;?>"<?if(isset($ar_selected[1]) && $ar_selected[1] == $i_month){?> selected="selected"<?}?>><?=$ar_month[$i_month];?></option><?
										}
									?></select>&nbsp;-&nbsp;
									<select id="<?=$_pref;?>id_<?=$form_name;?>_day" name="<?=$form_name;?>_day" title="День">
										<option value="all">-------</option><?
										for($i_day=1;$i_day<=31;$i_day++){
											?><option value="<?=$i_day;?>"<?if(isset($ar_selected[2]) && $ar_selected[2] == $i_day){?> selected="selected"<?}?>><?=$i_day;?></option><?
										}
									?></select><?
									?><input name="<?=$form_name;?>" type="hidden" value="<?=$form_value;?>" /><?
								}
								$date_item++;
								break;

							default:
								$sql = "SELECT `".$cur_field->db->field."` ";
								$sql.= "FROM `".$table."` ";
								$num_field = $cur_fields->get_num_field('pid');
								if($num_field > 0){
									if($pid){
										$sql.= "WHERE `pid` = '".$pid."' ";
									}
								}
								$sql.= "GROUP BY `".$cur_field->db->field."` ";
								//echo $sql."<br />";
								$DB = new DB();
								$ar_values = array();
								//echo $sql;
								if($DB->Query($sql)){
									if($DB->NumRows() > 0){
										while($ar_item = $DB->Next()){
											$ar_values[] = $ar_item[$cur_field->db->field];
										}
									}
									$DB->ClearDataSet();
								}

								//echo("<pre>");print_r($ar_values);echo("</pre>");
								//$POST->_print();

								$selected = $POST->GetValue($cur_field->db->field);
								//echo("<pre>");print_r($POST);echo("</pre>");
								//echo "'".$cur_field->db->field."'<br />";
								//echo "'".$pid."'<br />";
								//echo "'".$selected."'<br />";
								/*if(isset($_SESSION[$table][$pid][$cur_field->db->field])){
								echo "'not selected'<br />";
								//$selected = $_SESSION[$table][$pid][$cur_field->db->field];
								}else{
								if($only_one){
								unset($_SESSION[$table][$pid]);
								$only_one = false;
								}
								$_SESSION[$table][$pid][$cur_field->db->field] = $selected;
								}*/
								if($POST->IsSetVar($cur_field->db->field)){
									//if(isset($_SESSION[$table][$pid][$cur_field->db->field])){
									if($only_one){
										unset($_SESSION[$table][$pid]);
										$only_one = false;
									}
									if(!isset($_SESSION[$table][$pid][$cur_field->db->field])){
										$_SESSION[$table][$pid][$cur_field->db->field] = null;
									}
									if($selected != $_SESSION[$table][$pid][$cur_field->db->field]){
										$_SESSION[$table][$pid][$cur_field->db->field] = $selected;
									}
								}else{
									if(isset($_SESSION[$table][$pid][$cur_field->db->field])){
										$selected = $_SESSION[$table][$pid][$cur_field->db->field];
									}
								}

								if($selected == 'all'){
									$selected = '';
								}
								if(!empty($selected)){
									if(!in_array($selected,$ar_values)){
										$ar_values[] = $selected;
									}
								}
								//echo '$selected = '.$selected.'<br>';
								//echo("<pre>");print_r($ar_values);echo("</pre>");
								$n = sizeof($ar_values);
								if($n > 1){
									foreach($ar_values as &$value_item){
										$value_item = $DB->real_escape_string($value_item);
									}
									$cur_type = $cur_field->form->type;
									$ar_cur_type = explode('::',$cur_type);
									switch($ar_cur_type[0]){
										case 'select':
											if($ar_cur_type[1] == 'array'){
												eval('$array = array'.$ar_cur_type[2].';');
												if(!empty($array)) {
													//$form->type = null;
													//$form->value = null;
													$form2 = new FormField();
													$form2->for = $_pref.'select_'.$cur_field->db->field;
													$form2->value = $cur_field->params->label.":&nbsp;";
													echo FORM::GetElement('label',$form2);

													$form = new FormField();
													$form->id = $form2->for;
													$form->name = $cur_field->db->field;
													//$form->tabindex = $this->GetTabindex();
													echo FORM::GetElement('select',$form);
													foreach($array as $key=>$val){
														$form2 = new FormField();
														$form2->value = $key;
														if($selected == $form2->value){
															$form2->selected = 'selected';
														}
														echo FORM::GetElement('option',$form2);
														echo $val;
														echo FORM::GetElement('option');
													}
													echo FORM::GetElement('select');
												}
											}else{
												$cur_table = $ar_cur_type[1];
												$ar_cur_type[2] = explode('->',$ar_cur_type[2]);
												$cur_key = $ar_cur_type[2][0];
												$cur_val = $ar_cur_type[2][1];

												$form2 = new FormField();
												$form2->for = $_pref.'select_'.$cur_field->db->field;
												$form2->value = $cur_field->params->label.":&nbsp;";
												echo FORM::GetElement('label',$form2);

												$form = new FormField();
												$form->id = $form2->for;
												$form->name = $cur_field->db->field;
												echo FORM::GetElement('select',$form);
												$form2 = new FormField();
												$form2->value = 'all';
												echo FORM::GetElement('option',$form2);
												echo '-------';
												echo FORM::GetElement('option');

												$sql = "SELECT * ";
												$sql.= "FROM `".$cur_table."` ";
												$sql.= "WHERE `".$cur_key."` IN ('".implode("','",$ar_values)."') ";
												if($cur_table == 'categories_projects'){
													$sql.= "ORDER BY `name` ";
												}
												if($DB->Query($sql)){
													while($DB->Next()){
														$form2 = new FormField();
														$form2->value = $DB->Value($cur_key);
														if($selected == $form2->value){
															$form2->selected = 'selected';
														}
														echo FORM::GetElement('option',$form2);
														echo $DB->Value($cur_val);
														echo FORM::GetElement('option');
													}
												}
												echo FORM::GetElement('select');
												//echo $sql;
											}
											break;
									}
								}
								break;
						}
					}
				}
				if(empty($pid) && $table == 'projects'){
					// seo & account user filter
					$ar_fields = array('seo','account');
					foreach($ar_fields as $pr_field){
						$sql = "SELECT `".$pr_field."` ";
						$sql.= "FROM `categories_projects` ";
						$sql.= "GROUP BY `".$pr_field."` ";
						$DB = new DB();
						$ar_values = array();
						if($DB->Query($sql)){
							while($DB->Next()){
								$ar_values[] = $DB->Value($pr_field);
							}
							$DB->ClearDataSet();
						}
						//echo $sql."<br>";

						$selected = $POST->GetValue($pr_field);
						if(empty($selected) && isset($_SESSION[$table][$pid][$pr_field])){
							$selected = $_SESSION[$table][$pid][$pr_field];
						}else{
							if($only_one){
								unset($_SESSION[$table][$pid]);
								$only_one = false;
							}
							$_SESSION[$table][$pid][$pr_field] = $selected;
						}
						if($selected == 'all'){
							$selected = '';
						}
						if(!empty($selected)){
							if(!in_array($selected,$ar_values)){
								$ar_values[] = $selected;
							}
						}

						//echo("<pre>");print_r($ar_values);echo("</pre>");

						$n = sizeof($ar_values);
						if($n > 1){
							$sql = "SELECT * ";
							$sql.= "FROM `sys_users` ";
							$sql.= "WHERE `id` IN ('".implode("','",$ar_values)."') ";
							//echo $sql;
							?><label for="select_<?=$pr_field;?>"><?=$this->categories->field[$this->categories->get_num_field($pr_field)]->params->label;?>:</label>
							<select id="select_<?=$pr_field;?>" name="<?=$pr_field;?>">
								<option value="all">-------</option><?
								if($DB->Query($sql)){
									while($DB->Next()){
										?><option value="<?=$DB->Value('id');?>"<?if($DB->Value('id') == $selected){?> selected="selected"<?}?>><?=$DB->Value('name');?></option><?
									}
								}
							?></select><?
						}
					}
				}
				$el_form_sorting = ob_get_contents();
				ob_get_clean();
				if(!empty($el_form_sorting)){
					?><form id="<?=$_pref;?>filtering" class="filtering" method="post" action="">
						<fieldset><?=$el_form_sorting;?>
							<input type="hidden" name="cat" value="<?=$cat;?>" />
							<input type="button" class="clear" value="Очистить" />
							<input type="submit" value="Фильтровать" />
						</fieldset>
					</form><?
				}
				if($cat == 0 && $URL->GetValue('module') == 'logs_projects'){
					?><div class="button_save">
						<form id="save_list" method="post" action="<?=$URL_sort->GetURL();?>">
							<fieldset>
								<input name="save_in_csv" type="hidden" value="1" />
								<input type="submit" value="Сохранить в CSV" />
							</fieldset>
						</form>
					</div><?
				}
			}
		?></div><?
	}

	/**
	* кнопка добавления
	*
	* @param mixed $cat
	*/
	private function button_add($cat=0){
		global $URL, $ACCESS;
		$show = false;
		$URL->SetValue('action','add');
		$URL->SetValue('cat',$cat);
		$cur_fields = &$this->fields;
		if($cat){
			$cur_fields = &$this->categories;
		}

		$multiline = $cur_fields->table->multiline;
		$title = $cur_fields->titles->add;
		if($multiline){
			$show = true;
		}

		$pid = $URL->GetValue('pid');

		$max_level = $cur_fields->max_level;
		$level = 0;
		if(!empty($pid) && $cat){
			$cur_table = $cur_fields->table->name;
			$this->GetLevel($cur_table,'id','pid',$pid,$level);
		}

		if($max_level > 0 && $max_level <= $level){
			$show = false;
		}elseif(!$ACCESS->ar_value[$this->name]['add']){
			$show = false;
		}elseif($show){
			?><form action="<?=$URL->GetURL();?>" method="post"><fieldset style="border: none; margin: 0px; padding: 0px;"><input type="submit" name="add" value="<?=$title;?>" /></fieldset></form><?
		}
	}

	/**
	* определяет уровень вложения,
	* стоит ограничение на 1000 вложений
	*
	* @param mixed $table
	* @param mixed $pid
	* @param mixed $level
	*/
	private function GetLevel($table,$field_id='id',$field_pid='pid',$field_pid_value=0,&$level=1){
		$DB = new DB();
		$DB->select();
		$DB->from($table);
		$DB->where(array($field_id=>$field_pid_value));
		if($DB->execute()){
			if($DB->NumRows() > 0 && $level < 999){
				$level++;
				if($DB->Next()){
					$id = $DB->Value($field_pid);
					if(!empty($id) && $id != $field_pid_value){
						$this->GetLevel($table,$field_id,$field_pid,$id,$level);
					}
				}
			}
		}
	}

	/**
	* Возращает название поля id для проведения операций со строкой в таблице
	*
	* @param mixed $cur_fields
	*/
	function get_field_id(&$cur_fields){
		$id_field = 'id';
		$num_field = $cur_fields->get_num_field($id_field);
		if($num_field < 0){
			$num_field = $cur_fields->get_num_field(array('db->auto_increment'=>true));
			if($num_field < 0){
				$num_field = 0;
			}
		}
		$id_field = $cur_fields->field[$num_field]->db->field;
		return $id_field;
	}
}

class ModulePaths{
	var $folder;
	var $setting;
	var $css;
	var $js;
	var $inc;
}
?>