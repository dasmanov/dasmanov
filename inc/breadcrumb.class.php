<?php if(!defined('ACCESS_CODE') || intval(ACCESS_CODE+ACCESS_CODE*ACCESS_CODE) != 6) die('Access Error');

class BREADCRUMB {
	private $breadcrumb = array();
	private $current = 0;
	private $count = 0;

	/**
	* Инициализация хлебных крошек
	*
	*/
	function __construct(){
		$this->NewItem(RELATIVE,'Главная');
		$_url = new URL(true);

		/*global $URL, $Modules;
		$module = $URL->GetValue('module');
		$action = $URL->GetValue('action');
		$id = $URL->GetValue('id');
		$pid = $URL->GetValue('pid');*/
		/*if(empty($module)){

		}else{
		$_url->SetValue('module',$module);
		$this->NewItem($_url->GetURL(),$Modules->module[$module]->title);
		}*/
	}

	/**
	* добавляет ноый элемент в хлебные крошки
	*
	* @param mixed $url
	* @param mixed $title
	*/
	private function NewItem($url,$title){
		$ar = &$this->breadcrumb[$this->current];
		$ar['url'] = $url;
		$ar['title'] = $title;
		$this->current++;
		$this->count++;
	}

	/**
	* выводит html код хлебных крошек
	*
	*/
	function Show(){
		$n=&$this->count;
		if($n > 1){
			?><ul id="bc"><?
				for($i=0;$i<$n;$i++){
					$class = '';
					if($i<1){
						$class = ' class="home"';
					}
					$ar = &$this->breadcrumb[$i];
					$href = $ar['url'];
					$title = $ar['title'];
					?><li<?=$class;?>><a href="<?=$href;?>"><?=$title;?></a></li><?
				}
			?></ul><?
		}
	}

	/**
	* автоматически строит хлебные крошки
	*
	*/
	function BuildAuto(){
		global $URL, $Modules;
		$module = $URL->GetValue('module');
		if(!empty($module)){
			$N_URL = new URL(true);
			$N_URL->SetValue('module',$module);
			$cur_module = &$Modules->module[$module];
			$this->NewItem($N_URL->GetURL(),$cur_module->title);

			$cur_fields = &$cur_module->categories;

			$num = $cur_fields->get_num_field(array('form->type'=>'select::'.$cur_module->categories->table->name.'::'),true);
			if($num < 0){
				$cur_fields = &$cur_module->fields;
				$num = $cur_fields->get_num_field(array('form->type'=>'select::'.$cur_module->categories->table->name.'::'),true);
			}
			$num_name_pid = $cur_module->categories->get_num_field(array('form->type'=>'select::parent::'),true);
			if($num_name_pid < 0){
				$num_name_pid = $cur_module->categories->get_num_field(array('form->type'=>'select::'.$cur_module->categories->table->name.'::'),true);
			}
			$field_pid = null;
			if($num_name_pid > 0){
				$field_pid = $cur_fields->field[$num_name_pid]->form->name;
			}

			if($num >= 0){
				$name_pid_value = $cur_fields->field[$num]->form->name;
				$pid = $URL->GetValue($name_pid_value);
				if(!empty($pid)){
					$N_URL->SetValue($name_pid_value,$pid);
					$type = $cur_fields->field[$num]->form->type;
					$ar_type = explode('::',$type);
					$sizeof_ar_type = sizeof($ar_type);
					if($ar_type[0] == 'select'){
						if(!empty($ar_type[1])){
							$table = $ar_type[1];
							if($table == 'parent'){
								$table = $cur_fields->table->name;
							}
							$_ar = explode('->',$ar_type[2]);
							$f_key = $_ar[0];
							$f_value = $_ar[1];
							$ar_cat = array();
							$this->parent_catalog_scan($table,$f_key,$f_value,$field_pid,$pid,$ar_cat);
							for($n=0,$i=sizeof($ar_cat)-1;$i>=$n;$i--){
								$N_URL->SetValue($name_pid_value,$ar_cat[$i][$f_key]);
								$this->NewItem($N_URL->GetURL(),$ar_cat[$i][$f_value]);
							}
						}
					}
				}
			}
		}
	}

	/**
	* сканирует путь по родителям
	*
	* @param mixed $table
	* @param mixed $key
	* @param mixed $value
	* @param mixed $pid
	* @param mixed $array
	* @param mixed $i_array
	*/
	private function parent_catalog_scan($table,$key,$value,$name_pid_value,$pid,&$array,$i_array=0){
		global $DB;
		$DB->select(array($key,$name_pid_value,$value));
		$DB->from($table);
		$DB->where(array($key=>$pid));
		if($DB->execute()){
			//echo 'execute';
			if($DB->NumRows() > 0){
				if($row = $DB->Next()){
					if($row[$name_pid_value] != $pid){
						$array[$i_array][$key] = $row[$key];
						$array[$i_array][$value] = $row[$value];
						$i_array++;
						if($i_array > 100){
							echo('Слишком большой уровень вложенности');
							exit;
						}
						$this->parent_catalog_scan($table,$key,$value,$name_pid_value,$row[$name_pid_value],$array,$i_array);
					}
				}
			}
			$DB->ClearDataSet();
		}
	}
}
?>