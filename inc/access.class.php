<?php if(!defined('ACCESS_CODE') || intval(ACCESS_CODE+ACCESS_CODE*ACCESS_CODE) != 6) die('Access Error');

class ACCESS {
	var $groups_id;
	var $ar_access = array(
		'name' => array('view','add','edit','delete'),
		'title' => array('Просматривать','Добавлять','Редактировать','Удалять'));
	var $count;

	private $sym_div = array(';','::');

	var $ar_value;

	private $group_name = 'access';
	private $count_name;

	private $count_modules=0;

	private $module;
	private $action;

	function __construct(){
		global $URL;
		$this->module = $URL->GetValue('module');
		$this->action = $URL->GetValue('action');
		if(!empty($_SESSION['groups_id'])){
			$this->groups_id = $_SESSION['groups_id'];
		}
		$this->count = sizeof($this->ar_access['name']);

		$this->count_name = $this->group_name.'_count';
		if(!empty($this->groups_id)){
			$this->GetAccess();
		}
	}

	/**
	* проверяет массив разрешений идобавляет разрешения в общий список разреений
	*
	* @param mixed $value
	*/
	function GetValueForEdit($value){
		if(!empty($value)){
			if($test_ar = json_decode($value,true)){
				foreach($test_ar as $module=>$ar_item){
					foreach($ar_item as $method=>$val){
						if(empty($val)){
							$this->ar_value[$module][$method] = null;
						}else{
							$this->ar_value[$module][$method] = $val;
						}
					}
				}
				return true;
			}
		}
		return false;
	}

	/**
	* вывод html формы для каждого модуля по 4 чекбокса с правами на создание, редактирование, просмотр и удаление.
	*
	* @param mixed $form
	*/
	function html_form(&$form){
		global $Modules;
		$FORM = new FORM();
		$FORM->tabindex = $form->tabindex;

		$this->count_modules = $Modules->count;

		$form->name = $this->count_name;
		$form->value = $this->count_modules;
		$form->type = 'hidden';
		$FORM->html_form_element($form);

		$form->id = '';

		for($i=0;$i<$this->count_modules;$i++){
			$form1 = new FormField();
			$form2 = new FormField();

			$m_name = $Modules->module[$i]->name;

			$form1->name = $this->group_name.'_'.$i;
			$form1->value = $m_name;
			$form1->type = 'hidden';
			$FORM->html_form_element($form1);

			$form2->value = $Modules->module[$i]->title;
			$form2->type = 'label';
			$FORM->html_form_element($form2);

			for($j=0;$j<$this->count;$j++){
				$form3 = new FormField();
				$form3->name = $this->group_name.'_'.$i.'_'.$this->ar_access['name'][$j];
				$form3->checked = null;
				$form3->value = 1;
				if(!empty($this->ar_value[$m_name][$this->ar_access['name'][$j]])){
					$form3->checked = "checked";
				}
				$form3->title = $this->ar_access['title'][$j];
				$form3->type = 'checkbox';
				$form3->id = $form3->name;
				$FORM->html_form_element($form3);
				unset($form3);
			}
			unset($form1,$form2);
		}
		$form->tabindex = $FORM->tabindex;
	}

	function GetValueFromForm(){
		global $POST;

		$ar_value = array();
		for($j=0,$n=$POST->GetValue($this->count_name);$j<$n;$j++){
			$name_field = $this->group_name.'_'.$j;
			$module = $POST->GetValue($name_field);
			foreach($this->ar_access['name'] as $val){
				$m_access = intval($POST->GetValue($name_field.'_'.$val));
				$ar_value[$module][$val] = $m_access;
			}
		}
		return json_encode($ar_value);
	}

	/**
	* получение информации о доступах по принадлежащим пользователю категориям
	*
	*/
	private function GetAccess(){
		$DB = new DB();
		$sql = "SELECT * ";
		$sql.= "FROM `categories_sys_users` ";
		$sql.= "WHERE `id` IN ('".implode("','",$this->groups_id)."') ";
		$sql.= "AND `active` > 0";
		$success = false;
		if($DB->Query($sql)){
			if($DB->NumRows() > 0){
				while($DB->Next()){
					$this->GetValueForEdit($DB->Value('access'));
				}
				$success = true;
			}
			$DB->ClearDataSet();
		}
		return $success;
	}

	public function IsAccess($module='',$action = ''){
		if(empty($module)){
			$module = $this->module;
		}

		if(empty($action)){
			$action = $this->action;
		}

		switch($action){
			case 'check_add':
				$action = 'add';
				break;

			case 'check_edit':
				$action = 'edit';
				break;

			case 'check_delete':
				$action = 'delete';
				break;

			default:
				$action = 'view';
				break;
		}
		if(isset($this->ar_value[$module][$action]) && $this->ar_value[$module][$action]){
			return true;
		}
		return false;
	}

	/**
	* проверяет поле на разрешенный доступ
	*
	* @param mixed $access
	*/
	public function FieldIsAccess($access){
		$URL = new URL();
		$action = $URL->GetValue('action');
		if(!empty($access->full)){
			$cur_access = &$access->full;
		}elseif($action == 'view'){
			$cur_access = &$access->view;
		}elseif($action == 'add' || $action == 'check_add'){
			$cur_access = &$access->add;
		}elseif($action == 'edit' || $action == 'check_edit'){
			$cur_access = &$access->edit;
		}
		if(!empty($cur_access)){
			$ar_access = explode('::',$cur_access);
			//echo("<pre>");print_r($ar_access);echo("</pre>");
			$sizeof_ar_access = sizeof($ar_access);
			if($sizeof_ar_access > 2){
				if($ar_access[0] == 'categories_sys_users'){
					eval('$ar_access = '.$ar_access[2].';');
					if(!in_array($this->groups_id,$ar_access)){
						return false;
					}
				}
			}
		}
		return true;
	}
}
?>