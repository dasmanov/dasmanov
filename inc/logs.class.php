<?php if(!defined('ACCESS_CODE') || intval(ACCESS_CODE+ACCESS_CODE*ACCESS_CODE) != 6) die('Access Error');

	class LOGS {
		/*var $table;
		var $current;
		var $count;
		var $max_level;
		var $field = array();*/
		var $new_item = array();

		function __construct(){
			/*$this->table = new Table();
			$this->titles = new TITLES();
			$this->CountFields();*/
		}

		function NewItem($key,$val){
			$this->new_item[$key] = $val;
		}

		function DeleteItem($key){
			unset($this->new_item[$key]);
		}

		function Add(){
			$fields = array();
			$values = array();
			foreach($this->new_item as $key=>$val){
				$fields[] = $key;
				$values[] = $val;
			}
			$sql = "INSERT INTO `logs` (`".implode('`,`',$fields)."`) VALUES ('".implode("','",$values)."')";
			$DB = new DB();
			if($DB->Query($sql,true)){
				$this->ClearItem;
				return true;
			}
			return false;
		}

		function ClearItem(){
			$this->new_item = array();
		}
	}
?>