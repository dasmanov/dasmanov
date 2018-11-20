<?php if(!defined('ACCESS_CODE') || intval(ACCESS_CODE+ACCESS_CODE*ACCESS_CODE) != 6) die('Access Error');

class DB {
	var $host = "localhost";	//имя машины где находится базы
	var $name = "";				//имя базы данных
	var $user = "";				//имя пользователя базы данных
	var $pass = "";				//пароль для доступа
	var $enc = "UTF-8";			//кодировка сайта (utf8,cp1251)
	var $pref = "";				//префикс в базе данных
	var $spref = "";			//префикс используемый на сайте
	private $dataset = "";		//здесь будем хранить результат запроса
	private $record = "";		//а здесь будем хранить текущую запись
	private $link;				//и здесь будем хранить линк на подключение к базе данных.
	private $show_all_error = true;
	private $connected = false;

	private $query_string = "";

	function __construct($DB_CONFIG=''){
		if(empty($DB_CONFIG)){
			global $DB;
			if($DB->IsConnected()){
				$this->connected = true;
				$this->link = $DB->GetLink();
				$this->name = $DB->name;
			}
		}else{
			if(!empty($DB_CONFIG['name'])){
				$this->name = $DB_CONFIG['name'];
			}
			if(!empty($DB_CONFIG['host'])){
				$this->host = $DB_CONFIG['host'];
			}
			if(!empty($DB_CONFIG['user'])){
				$this->user = $DB_CONFIG['user'];
			}
			if(!empty($DB_CONFIG['pass'])){
				$this->pass = $DB_CONFIG['pass'];
			}
			if(!empty($DB_CONFIG['enc'])){
				$this->enc = $DB_CONFIG['enc'];
			}
			if(!empty($DB_CONFIG['pref'])){
				$this->pref = $DB_CONFIG['pref'];
			}
			if(!empty($DB_CONFIG['spref'])){
				$this->spref = $DB_CONFIG['spref'];
			}
			$this->enc = strtolower($this->enc);
			$this->enc = str_replace('-','',$this->enc);
			$this->Connect();
		}
	}

	/**
	* инициализируем подключение
	*
	*/
	function Connect(){
		//соединяемся с базой
		if($this->link = @mysqli_connect($this->host, $this->user, $this->pass)){
			//выбираем базу данных
			if(@mysqli_select_db($this->link,$this->name)){
				$this->connected = true;
				if(!empty($this->enc)){
					if ($version = mysqli_query($this->link,"SELECT VERSION()")) {
						$version = $version->fetch_assoc();
						$version = current($version);
						list($major, $minor) = explode(".", $version);
						$ver = $major.".".$minor;
						if((float)$ver >= 4.1) {
							if(!mysqli_query($this->link,"SET NAMES `".$this->enc."`;")){
								$this->show_db_error("Ошибка в установке кодировки сайта");
							}
						}
					} else {
						$this->show_db_error ("Невозможно определить версию сервера.");
					}
				}
			}else{
				$this->show_db_error("Ошибка в выборе базы данных MySQL");
			}
		}else{
			$this->show_db_error("Ошибка открытия соединение с сервером MySQL");
		}
	}

	/**
	* добавляем превикс к названию таблиы
	*
	* @param mixed $query
	*/
	private function addTabPrefix($query){
		return str_replace($this->pref,$this->spref,$query);	// просто возвращаем результат замены, большего нам и не надо.
	}

	/**
	* Обрабатывает строку запроса и оставляет только первый запрос
	*
	* @param mixed $query
	*/
	private function onlyOneQuery($query){
		$array = explode(";",$query);
		$size = sizeof($array);
		if($size > 1){
			$query = $array[0];
		}
		return $query;
	}

	/**
	* Выполняет запрос SQL
	*
	* @param mixed $query
	* @param mixed $show_error
	*/
	function Query($query,$show_error=false,$change_dataset=true){
		$query = $this->addTabPrefix($query);
		/*if(defined('SQL_LOGS') and SQL_LOGS > 0){
		file_put_contents('sql.log', date('c').' '.$query."\n", FILE_APPEND | LOCK_EX);
		}*/
		//$query = $this->onlyOneQuery($query);
		$query_error = false;
		/*if(preg_match('~wpastr_results_horo~isu',$query)){
		file_put_contents($_SERVER['DOCUMENT_ROOT'].'/dasmanov/sql.log', date('c').' '.'Запросе: "'.$query.'" '.mysqli_error($this->link)."\n", FILE_APPEND | LOCK_EX);
		}*/
		if($change_dataset){
			if($this->dataset = mysqli_query($this->link,$query)){
				return $this->dataset;	// на всякий пожарный возвращаем результат вне класса, вдруг понадобится.
			}else{
				$query_error = true;
			}
		}else{
			$dataset = mysqli_query($this->link,$query);
			if($dataset){
				return $dataset;
			}else{
				$query_error = true;
			}
		}
		if($query_error){
			$this->log_error($query);
			if($show_error || $this->show_all_error){
				$this->show_db_error('Ошибка в запросе: "'.$query.'" '.mysqli_error($this->link));
			}
		}
		return false;
	}

	/**
	* Эта функция будет перемещать нас от строки до строки, если строки не существует она вернет FALSE
	*
	*/
	function Next(){
		if($this->record = mysqli_fetch_array($this->dataset,MYSQLI_ASSOC)){
			$record = &$this->record;
			return $record;
		}else{
			return false;
		}
	}

	/**
	* Эта функция возвратит массив значений
	*
	*/
	function GetRecord(){
		return $this->record;
	}

	/**
	* возвращает нам значение поля по его имени.
	*
	* @param mixed $value
	*/
	public function Value($value){
		return $this->record[$value];
	}

	/**
	* Получаем значение $vall из выборки $query
	*
	* @param mixed $query
	* @param mixed $vall
	*/
	public function getResult($query,$vall){
		$query = $this->addTabPrefix($query);
		//$query = $this->onlyOneQuery($query);
		if($this->dataset = mysqli_query($this->link,$query)){
			$this->Next();	// переходим на первую запись
			$ret = $this->Value($vall);	//забираем нужную нам величину
			$this->ClearDataSet();	//очищаем запрос
			return $ret;	//и возвращаем величину
		}else{
			$this->show_db_error();
			return FALSE;
		}
	}

	public function select($fields = "*") {
		if (!is_array($fields)) {
			$fields = explode(',',$fields);
		}
		foreach($fields as &$field_item){
			if($field_item != '*' and !preg_match('~`~isu',$field_item)){
				$field_item = '`'.$field_item.'`';
			}
		}
		$this->query_string = "SELECT ".implode(',',$fields);

		return $this;
	}

	public function from($table) {
		$ar_table = explode('.',$table);
		foreach($ar_table as &$table_item){
			$table_item = '`'.$table_item.'`';
		}
		$this->query_string.= " FROM ".implode(',',$ar_table);
		return $this;
	}

	public function where($where) {
		if(is_array($where)){
			$BASE_LOGIC = 'AND';
			if(isset($where['LOGIC'])){
				$BASE_LOGIC = $where['LOGIC'];
			}
			$ar_where = array();
			foreach($where as $column=>$value){
				if(is_array($value)){
					if(isset($value['LOGIC'])){
						$LOGIC = '';
						$ar_sub_query_logic = array();
						foreach($value as $key=>$sub_value){
							if(empty($LOGIC) and $key == 'LOGIC'){
								$LOGIC = $sub_value;
								continue;
							}
							if(is_array($sub_value)){
								$ar_and_item = array();
								foreach($sub_value as $column=>$ssvalue){
									$allow_quote = true;
									$sign = '=';
									if(preg_match('~^(\!|\<\=|\<|\>\=|\>|\>\<)(.*)$~isu',$column,$matches)){
										$sign = $matches[1];
										if($sign == '!'){
											$sign.='=';
										}
										$column = $matches[2];
									}
									$sign_before_value = '';
									$sign_after_value = '';
									if(preg_match('~^(%|)(.*)(%|)$~isu',$ssvalue,$matches)){
										if(!empty($matches[1])){
											$sign_before_value = $matches[1];
										}
										if(!empty($matches[2])){
											$ssvalue = $matches[2];
										}
										if(!empty($matches[3])){
											$sign_after_value = $matches[3];
										}
										if(!empty($sign_before_value) or !empty($sign_after_value)){
											$sign = 'LIKE';
										}
									}
									if(preg_match('~^(not[\s]+|)null$~isu',$ssvalue,$matches)){
										$post_sign = '';
										if(($sign == '!' and empty($matches[1])) or ($sign != '!' and !empty($matches[1]))){
											$post_sign = ' NOT';
										}
										$sign ='IS'.$post_sign;
										$ssvalue = 'NULL';
										$allow_quote = false;
									}
									if($allow_quote){
										$sign_before_value = "'".$sign_before_value;
										$sign_after_value = "'".$sign_after_value;
									}
									$ar_and_item[] = "`".$column."` ".$sign." ".$sign_before_value.$this->real_escape_string($ssvalue).$sign_after_value;
								}
								$ar_sub_query_logic[] = '('.implode(' AND ',$ar_and_item).')';
							}
						}
						$ar_where[] = '('.implode(' '.$LOGIC.' ',$ar_sub_query_logic).')';
						//echo("<pre>");print_r($ar_where);echo("</pre>");
					}else{
						$sign_before_column = '`';
						$sign_after_column = '`';
						$sign_before_value = '';
						$sign_after_value = '';
						foreach($value as $sub_key=>&$value_item){
							if($sub_key != 'SUB_QUERY'){
								$sign_before_value = "'";
								$sign_after_value = "'";
								$value_item = $this->real_escape_string($value_item);
							}
						}
						$sign = '';
						if(preg_match('~^(\!)(.*)$~isu',$column,$matches)){
							$sign = $matches[1];
							if($sign == '!'){
								$sign =' NOT';
							}
							$column = $matches[2];
						}
						if(preg_match('~`~isu',$column,$matches)){
							$sign_before_column = '';
							$sign_after_column = '';
						}
						$ar_where[] = $sign_before_column.$column.$sign_after_column.$sign." IN (".$sign_before_value.implode($sign_after_value.",".$sign_before_value,$value).$sign_after_value.") ";
					}
				}else{
					if($column == 'LOGIC'){
						continue;
					}
					$allow_quote = true;
					$sign = '=';
					if(preg_match('~^(\!|\<\=|\<|\>\=|\>|\>\<)(.*)$~isu',$column,$matches)){
						$sign = $matches[1];
						if($sign == '!'){
							$sign.='=';
						}
						$column = $matches[2];
					}
					$sign_before_column = '`';
					$sign_after_column = '`';
					$sign_before_value = '';
					$sign_after_value = '';
					if(preg_match('~^(%|)(.*)(%|)$~isu',$value,$matches)){
						if(!empty($matches[1])){
							$sign_before_value = $matches[1];
						}
						if(!empty($matches[2])){
							$value = $matches[2];
						}
						if(!empty($matches[3])){
							$sign_after_value = $matches[3];
						}
						if(!empty($sign_before_value) or !empty($sign_after_value)){
							$sign = 'LIKE';
						}
					}
					if(preg_match('~^(not[\s]+|)null$~isu',$value,$matches)){
						$post_sign = '';
						if(($sign == '!' and empty($matches[1])) or ($sign != '!' and !empty($matches[1]))){
							$post_sign = ' NOT';
						}
						$sign ='IS'.$post_sign;
						$value = 'NULL';
						$allow_quote = false;
					}
					if($allow_quote){
						$sign_before_value = "'".$sign_before_value;
						$sign_after_value = "'".$sign_after_value;
					}
					if(preg_match('~`~isu',$column,$matches)){
						$sign_before_column = '';
						$sign_after_column = '';
					}
					$ar_where[] = $sign_before_column.$column.$sign_after_column." ".$sign." ".$sign_before_value.$this->real_escape_string($value).$sign_after_value;
				}
			}
			$this->query_string.= " WHERE ".implode(' '.$BASE_LOGIC.' ',$ar_where);
		}else{
			$this->query_string.= " WHERE ".$where;
		}
		return $this;
	}

	public function group($fields){
		if (!is_array($fields)) {
			$fields = explode(',',$fields);
		}
		foreach($fields as &$field_item){
			if(!preg_match('~`~isu',$field_item)){
				$field_item = '`'.$field_item.'`';
			}
		}
		$this->query_string.= " GROUP BY ".implode(',',$fields);

		return $this;
	}

	public function having($fields){
		if (!is_array($fields)) {
			$fields = explode(',',$fields);
		}
		foreach($fields as &$field_item){
			if(!preg_match('~`~isu',$field_item)){
				$field_item = '`'.$field_item.'`';
			}
		}
		$this->query_string.= " HAVING ".implode(',',$fields);

		return $this;
	}

	public function order($by){
		if(is_array($by)){
			$ar_by = array();
			foreach($by as $column=>$sort_type){
				$ar_by[] = "`".$column."` ".$sort_type."";
			}
			$this->query_string.= " ORDER BY ".implode(', ',$ar_by);
		}else{
			$this->query_string.= " ORDER BY ".$by;
		}
		return $this;
	}

	/**
	* $offset - это номер строки в результирующей таблицы (от 0),
	* от которой необходимо отсчитывать записи
	* $count - это число, которое означает то,
	* сколько записей из результирующей таблицы необходимо отобрать, начиная от offset.
	*
	* @param mixed $count
	* @param mixed $offset
	* @return DB
	*/
	public function limit($count,$offset=null){
		$this->query_string.= " LIMIT ";
		if(!is_null($offset)){
			$this->query_string.= $offset.', ';
		}
		$this->query_string.= $count;
		return $this;
	}

	/**
	* Возвращает SQL запрос
	* 
	*/
	public function get_query_string(){
		return $this->query_string;
	}

	public function execute() {
		if (!empty($this->query_string)){
			$this->query_string.=';';
			/*if($_SERVER['REMOTE_ADDR'] == '83.149.45.100'){
			file_put_contents($_SERVER['DOCUMENT_ROOT'].'/dasmanov/sql.log',$this->query_string."\n",FILE_APPEND);
			}//*/
			//echo $this->query_string.'<br>';
			return $this->Query($this->query_string);
		}
		return false;
	}

	/**
	* Добавляет строку в таблицу
	*
	* @param string - Название таблицы
	* @param array - Массив названий столбцов(возможно со значениями)
	* @param array - Массив значений столбцов
	* @return bool
	*/
	function InsertRow($table,$vars,$values=array()){
		$n = sizeof($vars);
		if($n > 0){
			if(empty($values)){
				$array = $vars;
				$vars = array();
				foreach($array as $key => $val){
					$vars[] = $key;
					$values[] = $this->real_escape_string($val);
				}
			}else{
				foreach($values as $key => $val){
					$values[$key] = $this->real_escape_string($val);
				}
			}
			if($n == sizeof($values)){
				$sql = "INSERT INTO `".$table."` (`".implode("`,`",$vars)."`) VALUES ('".implode("','",$values)."');";
				if($this->Query($sql)){
					return mysqli_insert_id($this->link);
					//echo("<pre>");print_r($result);echo("</pre>");
					//return true;
				}
			}
		}
		return false;
	}

	/**
	* Обновляет строку в таблице
	*
	* @param string - Название таблицы
	* @param mixed - условие вида `id` = '5'
	* @param array - Массив названий столбцов(возможно со значениями)
	* @param array - Массив значений столбцов
	* @return bool
	*/
	function UpdateRow($table,$where,$vars,$values=array()){
		$n = sizeof($vars);
		if($n > 0){
			if($n == sizeof($values)){
				for($i=0;$i<$n;$i++){
					$vars[$vars[$i]] = $values[$i];
					unset($vars[$i]);
				}
				$values = array();
			}
			if(empty($values)){
				foreach($vars as $key => $val){
					$values[] = "`".$key."` = '".$this->real_escape_string($val)."'";
				}
				if (is_array($where)) {
					$ar_where = array();
					$LOGIC = 'AND';
					foreach($where as $key => $val){
						if($key == 'LOGIC'){
							$LOGIC = $val;
						}else{
							$ar_where[] = "`".$key."` = '".$this->real_escape_string($val)."'";
						}
					}
					$where = implode(' '.$LOGIC,$ar_where);
				}

				$sql = "UPDATE `".$table."` SET ".implode(', ',$values);
				if(!empty($where)){
					$sql.= " WHERE ".$where.';';
				}
				/*if(is_array($where)){
				$one = false;
				foreach($where as $key=>$val){
				if($one){
				$sql.= " AND";
				}else{
				$one = true;
				}
				$sql.= " `".$key."` LIKE '".$val."'";
				}
				}else{
				$sql.= ' '.$where;
				}//*/
				//$sql.= ";";
				if($this->Query($sql,false,false)){
					return true;
				}
			}
		}
		return false;
	}

	/**
	* Удаляет строки из таблиы $table
	*
	* @param mixed $table
	* @param mixed $where
	*/
	function DeleteRows($table, $where){
		if(is_array($where)){
			$sql = "DELETE FROM `".$table."` WHERE ";
			foreach($where as $key=>$val){
				$sql.= " `".$key."` LIKE '".$val."'";
			}
			$sql.= ";";
			if($this->Query($sql,false,false)){
				return true;
			}
		}
		return false;
	}

	/**
	* возвращаем количество строк
	*
	* @return int
	*/
	function NumRows(){
		return mysqli_num_rows($this->dataset);
	}

	/**
	* убиваем наш запрос
	*/
	function ClearDataSet(){
		$this->dataset = "";
	}

	function GetLink(){
		if($this->connected){
			return $this->link;
		}
		return false;
	}

	function IsConnected(){
		return $this->connected;
	}

	/**
	* Выводит ошибку MySQL
	*/
	private function show_db_error($error=''){
		if(!isset($_REQUEST['ERROR_MSG'])){
			$_REQUEST['ERROR_MSG'] = '';
		}
		ob_start();
		//$bt = debug_backtrace();
		//$caller = array_shift($bt);
		//echo("<pre>");print_r($caller);echo("</pre>\n");
		$sqlerr = mysqli_error($this->link);
		if(empty($error)){
			echo 'Ошибка в запросе: "'.$sql.'" '.$sqlerr;
		}else{
			echo $error." (".$sqlerr.")";
		}
		?><br /><?
		$_REQUEST['ERROR_MSG'].= ob_get_contents();
		ob_get_clean();
	}

	/**
	* Логирование ошибок
	* 
	* @param mixed $query
	*/
	private function log_error($query){
		if(defined('SQL_LOGS') and SQL_LOGS > 0){
			file_put_contents($_SERVER['DOCUMENT_ROOT'].'/dasmanov/logs/sql.log', date('c').' '.'Ошибка в запросе: "'.$query.'" '.mysqli_error($this->link)."\n", FILE_APPEND | LOCK_EX);
		}
	}

	/**
	* Возвращает значение автоинкрементного поля
	* @param string - Название таблицы
	* @return int - значение автоинкремента
	*/
	function get_table_auto_increment($table){
		$sql = "SHOW TABLE STATUS FROM `".$this->name."` LIKE '".$table."'";
		if($this->Query($sql)){
			if($this->Next()){
				$val = $this->Value('Auto_increment');
				$this->ClearDataSet();
				return $val;
			}
		}
		return false;
	}

	/**
	* обезопасить текст для использования в БД
	*
	* @param mixed $text
	*/
	function real_escape_string($text){
		return mysqli_real_escape_string($this->link,$text);
	}

	/**
	* Возвращает массив полей таблицы $name_table
	*
	* @param mixed $name_table
	*/
	function list_fields($name_table){
		$query = "SELECT * FROM ".$name_table." LIMIT 1";
		$ar_fields = array();
		if ($this->Query($query)) {
			$finfo = $this->dataset->fetch_fields();
			foreach ($finfo as $val) {
				$ar_fields[] = strtolower($val->name);
				/*printf("Name:      %s\n",   $val->name);
				printf("Table:     %s\n",   $val->table);
				printf("Max. Len:  %d\n",   $val->max_length);
				printf("Length:    %d\n",   $val->length);
				printf("charsetnr: %d\n",   $val->charsetnr);
				printf("Flags:     %d\n",   $val->flags);
				printf("Type:      %d\n\n", $val->type);*/
			}
			$this->ClearDataSet();
		}
		return $ar_fields;
	}
}
?>
