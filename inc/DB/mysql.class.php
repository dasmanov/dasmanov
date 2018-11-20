<?php if(!defined('ACCESS_CODE') || intval(ACCESS_CODE+ACCESS_CODE*ACCESS_CODE) != 6) die('Access Error');

	class DB_ {
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

		function DB($DB_CONFIG=''){
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

		function Connect(){	//инициализируем класс
			//соединяемся с базой
			if($this->link = @mysql_connect($this->host, $this->user, $this->pass)){
				//выбираем базу данных
				if(@mysql_select_db($this->name,$this->link)){
					$this->connected = true;
					if(!empty($this->enc)){
						if ($version = mysql_query("SELECT VERSION()",$this->link)) {
							$version = mysql_result($version, 0);
							list($major, $minor) = explode(".", $version);
							$ver = $major.".".$minor;
							if((float)$ver >= 4.1) {
								if(!mysql_query("SET NAMES `".$this->enc."`;",$this->link)){
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

		private function addTabPrefix($query){
			return str_replace($this->pref,$this->spref,$query);	// просто возвращаем результат замены, большего нам и не надо.
		}

		private function onlyOneQuery($query){
			// Обрабатывает строку запроса и оставляет только первый запрос
			$array = explode(";",$query);
			$size = sizeof($array);
			if($size > 1){
				$query = $array[0];
			}
			return $query;
		}

		function Query($query,$show_error=false){
			// Выполняет запрос SQL
			$query = $this->addTabPrefix($query);
			if(defined('SQL_LOGS') and SQL_LOGS > 0){
				file_put_contents('sql.log', date('c').' '.$query."\n", FILE_APPEND | LOCK_EX);
			}
			//$query = $this->onlyOneQuery($query);
			if($this->dataset = mysql_query($query,$this->link)){
				return $this->dataset;	// на всякий пожарный возвращаем результат вне класса, вдруг понадобится.
			}else{
				if($show_error || $this->show_all_error){
					$this->show_db_error('Ошибка в запросе: "'.$query.'" '.$show_error);
				}
				return FALSE;
			}
		}

		function Next(){
			//Эта функция будет перемещать нас от строки до строки, если строки не существует она вернет FALSE
			if($this->record = mysql_fetch_array($this->dataset,MYSQL_ASSOC)){
				return TRUE;
			}else{
				return FALSE;
			}
		}

		function GetRecord(){
			//Эта функция возвратит массив значений
			return $this->record;
		}

		function Value($value){
			// а эта будет возвращать нам значение поля по его имени.
			return $this->record[$value];
		}

		function getResult($query,$vall){
			$query = $this->addTabPrefix($query);
			//$query = $this->onlyOneQuery($query);
			if($this->dataset = mysql_query($query,$this->link)){
				$this->Next();	// переходим на первую запись
				$ret = $this->Value($vall);	//забираем нужную нам величину
				$this->ClearDataSet();	//очищаем запрос
				return $ret;	//и возвращаем величину
			}else{
				$this->show_db_error();
				return FALSE;
			}
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
			if($n > 1){
				if(empty($values)){
					$array = $vars;
					$vars = array();
					foreach($array as $key => $val){
						$vars[] = $key;
						$values[] = $val;
					}
				}
				if($n == sizeof($values)){
					$sql = "INSERT INTO `".$table."` (`".implode("`,`",$vars)."`) VALUES ('".implode("','",$values)."');";
					if($this->Query($sql)){
						return true;
					}
				}
			}
			return false;
		}

		/**
		* Обновляет строку в таблице
		*
		* @param string - Название таблицы
		* @param int - условие вида `id` = '5'
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
						$values[] = " `".$key."` = '".$val."'";
					}
					$sql = "UPDATE `".$table."` SET".implode(',',$values)." WHERE ".$where.";";
					if($this->Query($sql)){
						return true;
					}
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
			return mysql_num_rows($this->dataset);
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
			$sqlerr = mysql_error();
			if(empty($error)){
				echo $sqlerr;
			}else{
				echo $error." (".$sqlerr.")";
			}
		?><br /><?
			$_REQUEST['ERROR_MSG'].= ob_get_contents();
			ob_get_clean();
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
	}
?>
