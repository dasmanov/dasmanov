<?php if(!defined('ACCESS_CODE') || intval(ACCESS_CODE+ACCESS_CODE*ACCESS_CODE) != 6) die('Access Error');

class FORM {
	private $form = array();
	private $cur = 0;
	var $tabindex = 0;

	function __construct(){

	}

	/**
	* создается новое поле формы
	*
	* @param mixed $form
	*/
	function NewItem($form){
		switch($form->type){
			case 'text':
			default:
				$this->form[$this-cur] = new FormField();
				break;
		}
	}

	/**
	* выводит html код элемнта $form согласно его типу
	*
	* @param mixed $form
	*/
	public function html_form_element($form){
		global $Modules, $DB, $URL, $ACCESS, $POST;

		$action = $URL->GetValue('action');
		$pid = intval($URL->GetValue('pid'));
		$cat = intval($URL->GetValue('cat'));

		$type = $form->type;
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
				//echo("<pre>");print_r($json_type);echo("</pre>");
			}
		}
		if(!$json){
			$type = $ar_type[0];
		}
		$name = $form->name;
		/*if($name == 'status'){
		echo("<pre>");print_r($form);echo("</pre>");
		}*/
		if(empty($_SESSION['post'][$name])){
			$value = $form->value;
		}else{
			$value = $_SESSION['post'][$name];
			$form->value = $_SESSION['post'][$name];
		}

		$_id = '';
		if($form->id){
			$el_id = $form->id;
			$_id = ' id="'.$el_id.'"';
		}

		$cur_module = $Modules->module[$Modules->GetCurrnet()];
		if(empty($cat)){
			$cur_fields = $cur_module->fields;
		}else{
			$cur_fields = $cur_module->categories;
		}
		if($type != 'auto_increment'){
			$cur_num_field = $cur_fields->get_num_field($name);
			if($cur_num_field >= 0){
				//if($cur_fields->field[$cur_num_field]->form->name == $name){
				if($action == 'add'){
					if(empty($cur_fields->field[$cur_num_field]->edit->add)){
						$form->disabled = 'disabled';
					}
				}elseif($action == 'edit'){
					if(empty($cur_fields->field[$cur_num_field]->edit->edit)){
						$form->disabled = 'disabled';
					}
				}
				//}
			}
		}

		switch($type){

			case 're':
				$num = $Modules->module[$Modules->GetCurrnet()]->fields->get_num_type($ar_type[1]);
				$form2 = $Modules->module[$Modules->GetCurrnet()]->fields->field[$num]->form;
				$form2->name = $form->name;
				$this->html_form_element($form2,$el_id);
				break;

			case 'auto_increment':
				$form2 = new FormField();
				$form2->id = $form->id;
				if($value == ''){
					$form2->value = 'новый';
				}else{
					$form2->value = $form->value;
					$form->id = '';
					$form->type = 'hidden';
					echo $this->GetElement('input',$form);
				}
				echo $this->GetElement('label',$form2);
				break;

			case 'md5':
				break;

			case 'price':
				if(empty($form->value)){
					$form->value = 0;
				}
				$form->value = str_replace(' ','',$form->value);;
				$form->value = number_format($form->value,0,'.',' ');
				$form->tabindex = $this->GetTabindex();
				$form->type = 'text';
				echo $this->GetElement('input',$form);
				break;

			case 'text':
				$form->tabindex = $this->GetTabindex();
				$form->type = 'text';
				echo $this->GetElement('input',$form);
				break;

			case 'number':
				if($json){
					$decimals = 0;
					if(!empty($json_type['decimals'])){
						$decimals = intval($json_type['decimals']);
					}
					$cur_num_field = $cur_fields->get_num_field($name);

					if($cur_num_field >= 0){
						$cur_db_field = $cur_fields->field[$cur_num_field]->db;
						if(empty($decimals) and !empty($cur_db_field->decimals)) {
							$decimals = intval($cur_db_field->decimals);
						}
						if(empty($form->min) and !empty($cur_db_field->attributes) and preg_match('~UNSIGNED~iu',$cur_db_field->attributes)){
							$form->min = 0;
						}
						if(empty($form->size) and !empty($cur_db_field->length)){
							$form->size = $cur_db_field->length;
							if(!empty($decimals)){
								$form->size++;
							}
						}
					}
					if(empty($decimals)){
						$form->step = 1;
					}else{
						$form->step = '0.';
						for($i=0,$n=$decimals-1;$i<$n;$i++){
							$form->step.= '0';
						}
						$form->step.= '1';
					}
				}
				$form->tabindex = $this->GetTabindex();
				$form->type = $type;
				echo $this->GetElement('input',$form);
				break;

			case 'hidden':
				if(!empty($ar_type[1])){
					switch($ar_type[1]){
						case 'view':
							$form2 = new FormField();
							//$form2->id = $form->id;
							$form2->value = $form->value;
							//$form->id = '';
							echo $this->GetElement('label',$form2);
							break;
					}
				}
				$form->type = 'hidden';
				echo $this->GetElement('input',$form);
				break;

			case 'password':
				if($action == 'edit'){
					$value = '';
					$form->value = '';
				}
				$form->type = 'password';
				$form->tabindex = $this->GetTabindex();
				echo $this->GetElement('input',$form);
				break;

			case 'textarea':
				$form->type = '';
				$form->tabindex = $this->GetTabindex();
				echo $this->GetElement('textarea',$form);
				break;

			case 'checkbox':
				$form->type = 'checkbox';
				/*if(empty($form->value)){
				$form->value = 1;
				}*/
				//$form->checked='';
				/*if(!empty($form->value)) {
				$form->checked = "checked";
				}*/
				if(empty($form->value)){
					$form->checked = null;
				}
				$form->tabindex = $this->GetTabindex();
				echo $this->GetElement('input',$form);
				break;

			case 'file':
				$file_type = '';
				if($json){
					if(!empty($json_type['file_type'])){
						$file_type = $json_type['file_type'];
					}
				}elseif(!empty($ar_type[1])){
					$file_type = $ar_type[1];
				}

				$form2 = new FormField();
				$form2->type = 'hidden';
				$form2->name = 'MAX_FILE_SIZE';
				$form2->value = '30000000';
				echo $this->GetElement('input',$form2);

				//echo $file_type."<br>";

				if(!empty($file_type)){
					switch($file_type){
						case 'image':
							if(!empty($form->value) && $form->value != 'null'){
								$md5_value = md5($form->value);
								$_SESSION['temp'][$md5_value] = $form->value;

								$img = $form->value;
								$url_img = '';
								if($json_img = json_decode($img,true)){
									if(empty($json_img['thumb']['src'])){
										if(empty($json_img['normal']['src'])){
											if(!empty($json_img['original']['src'])){
												$url_img = $json_img['original']['src'];
											}
										}else{
											$url_img = $json_img['normal']['src'];
										}
									}else{
										$url_img = $json_img['thumb']['src'];
									}
								}else{
									if (isset($_SERVER['HTTPS']) &&
									($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 1) ||
									isset($_SERVER['HTTP_X_FORWARDED_PROTO']) &&
									$_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') {
										$protocol = 'https://';
									}
									else {
										$protocol = 'http://';
									}
									$url_img = $protocol.$_SERVER['HTTP_HOST'].$img;
								}
								if(IMAGE::exist_url($url_img)){

									$form2 = new FormField();
									$form2->src = $url_img;
									$form2->title = $url_img;
									echo $this->GetElement('img',$form2);


									$form2 = new FormField();
									$form2->type = 'hidden';
									$form2->name = 'hidden_'.$form->name;
									$form2->value = $md5_value;
									echo $this->GetElement('input',$form2);

									$id_f_d = $form->id.'_del';

									$form3 = new FormField();
									$form3->type = 'checkbox';
									$form3->value = 1;
									//$form3->checked = '';
									$form3->name = 'delete_'.$form->name;
									$form3->id = $id_f_d;

									$form2 = new FormField();
									$form2->value = 'Удалить: '.$this->GetElement('input',$form3);
									$form2->for = $id_f_d;
									$form2->class = 'checkbox_del';
									echo $this->GetElement('label',$form2);
								}
							}
							break;
						default:
							if(!empty($form->value) && $form->value != 'null'){
								$form2 = new FormField();
								$form2->type = 'hidden';
								$form2->name = 'hidden_'.$form->name;
								$form2->value = $form->value;
								echo $this->GetElement('input',$form2);

								$id_f_d = $form->id.'_del';

								$form3 = new FormField();
								$form3->type = 'checkbox';
								$form3->value = 0;
								$form3->checked = '';
								$form3->name = 'delete_'.$form->name;
								$form3->id = $id_f_d;

								$form2 = new FormField();
								$form2->value = 'Удалить: '.$this->GetElement('input',$form3);
								$form2->for = $id_f_d;
								$form2->class = 'checkbox_del';
								echo $this->GetElement('label',$form2);
							}
							break;
					}
				}

				$form->type = 'file';
				$form->value = '';
				$form->tabindex = $this->GetTabindex();
				echo $this->GetElement('input',$form);
				break;

			case 'select':
				if(!empty($ar_type[1])){

					switch($ar_type[1]){

						case 'array':
							eval('$array = array'.$ar_type[2].';');
							if(!empty($array)) {
								$form->type = null;
								$form->value = null;
								$form->tabindex = $this->GetTabindex();
								echo $this->GetElement('select',$form);
								foreach($array as $key=>$val){
									$form2 = new FormField();
									$form2->value = $key;
									if($value == $form2->value){
										$form2->selected = 'selected';
									}
									echo $this->GetElement('option',$form2);
									echo $val;
									echo $this->GetElement('option');
								}
								echo $this->GetElement('select');
							}

							break;

						case 'parent':
							if(!empty($ar_type[2])){
								$_ar = explode('->',$ar_type[2]);
								$f_key = $_ar[0];
								$f_value = $_ar[1];
								if($URL->GetValue('cat') > 0){
									$_fields = $Modules->module[$Modules->GetCurrnet()]->categories;
								}else{
									$_fields = $Modules->module[$Modules->GetCurrnet()]->fields;
								}
								$max_level = $_fields->max_level;

								$array = array();

								$this->GetTree1($_fields->table->name,0,$_fields->field[$_fields->get_num_field($f_key)]->form->value,$f_key,$f_value,0,$array);

								if(empty($value) && $action == 'add'){
									$value = $pid;
								}

								$form->type = null;
								$form->value = null;
								$form->tabindex = $this->GetTabindex();
								echo $this->GetElement('select',$form);
								$form2 = new FormField();
								$form2->value = 0;
								if($value == $form2->value){
									$form2->selected = 'selected';
								}
								echo $this->GetElement('option',$form2);
								echo '-------';
								echo $this->GetElement('option');
								for($i=0,$n=sizeof($array);$i<$n;$i++){
									$show = true;
									if($max_level > 0){
										if($array[$i]['level'] >= $max_level){
											$show = false;
										}
									}
									$key = $array[$i][$f_key];
									$val = $array[$i][$f_value];
									$str = '';
									for($j=1;$j<$array[$i]['level'];$j++){
										$str.='&nbsp;-&nbsp;';
									}
									if($show){
										$form2 = new FormField();
										$form2->value = $key;
										if($value == $form2->value){
											$form2->selected = 'selected';
										}
										echo $this->GetElement('option',$form2);
										echo $str.$val;
										echo $this->GetElement('option');
									}
								}
								echo $this->GetElement('select');
							}
							break;

						case 'sys_users':
							if($sizeof_ar_type > 1){
								if(!empty($ar_type[2])){
									$where = '';
									if($sizeof_ar_type > 3){
										if($ar_type[3] == 'if'){
											if($URL->GetValue('cat') > 0){
												$_fields = $Modules->module[$Modules->GetCurrnet()]->categories;
											}else{
												$_fields = $Modules->module[$Modules->GetCurrnet()]->fields;
											}
											$f_2 = $_fields->get_num_field($ar_type[4]);
											if($_fields->field[$f_2]->form->value == $ar_type[5]){
												if($ar_type[6] == 'session'){
													$value = $_SESSION[$ar_type[7]];
													//$where = "WHERE `id` = '".$value."'";
												}
											}
										}else{
											if($sizeof_ar_type > 4){
												if($ar_type[3] == 'array'){
													eval('$array = array'.$ar_type[4].';');
													$i=0;
													foreach($array as $key=>$val){
														if($i>0){
															$pref_where = 'OR';
														}else{
															$pref_where = 'WHERE';
														}
														if(is_array($val)){
															foreach($val as $val2){
																if($i>0){
																	$pref_where = 'OR';
																}
																$where.= $pref_where." `".$key."` = '".$val2."' ";
																$i++;
															}
														}else{
															$where.= $pref_where." `".$key."` = '".$val."' ";
														}
														$i++;
													}
												}
											}else{
												$ar_type[3] = explode('=',$ar_type[3]);
												if(sizeof($ar_type[3])>1){
													$where = "WHERE `".$ar_type[3][0]."` = '".$ar_type[3][1]."'";
												}
											}
										}
									}
									$_ar = explode('->',$ar_type[2]);
									$f_key = $_ar[0];
									$f_value = $_ar[1];
									$sql = "SELECT `".$f_key."`, `".$f_value."`, `email` FROM `".$ar_type[1]."` ".$where;
									if($DB->Query($sql)){
										if(empty($value) && $action == 'add' && $name == 'pid'){
											$value = $pid;
										}

										$form->type = null;
										$form->value = null;
										$form->tabindex = $this->GetTabindex();
										echo $this->GetElement('select',$form);
										$form2 = new FormField();
										$form2->value = 0;
										if($value == $form2->value){
											$form2->selected = 'selected';
										}
										echo $this->GetElement('option',$form2);
										echo '-------';
										echo $this->GetElement('option');
										while($DB->Next()){
											$row = $DB->GetRecord();
											$key = $row[$f_key];
											$val = $row[$f_value];

											$form2 = new FormField();
											$form2->value = $key;
											if($value == $form2->value){
												$form2->selected = 'selected';
											}
											echo $this->GetElement('option',$form2);
											echo $val.' ('.$row['email'].')';
											echo $this->GetElement('option');
										}
										echo $this->GetElement('select');
									}
								}
							}
							break;

						case $Modules->module[$Modules->GetCurrnet()]->categories->table->name:
							if(!empty($ar_type[2])){
								$cur_table = $ar_type[1];
								$_ar = explode('->',$ar_type[2]);
								$f_key = $_ar[0];
								$f_value = $_ar[1];

								$array = array();

								//$_pid = null;
								$_pid = 0;
								/*if($Modules->module[$Modules->GetCurrnet()]->categories->get_num_field('pid') >= 0 and $URL->GetValue('cat') > 0){
								$_pid = $pid;
								}*/
								$id = $POST->GetValue('id');
								if($URL->GetValue('cat') > 0 and !empty($id)){
									$this->GetTree1($cur_table,$_pid,$id,$f_key,$f_value,0,$array);
								}else{
									$this->GetTree2($cur_table,$_pid,$f_key,$f_value,0,$array);
								}

								if(empty($value) && $action == 'add'){
									$value = $URL->GetValue($name);
								}
								//$this->GetTree1($cur_table,$_pid,$value,$f_key,$f_value,0,$array);
								$form->type = null;
								$form->value = null;
								$form->tabindex = $this->GetTabindex();
								echo $this->GetElement('select',$form);
								$form2 = new FormField();
								$form2->value = 0;
								if($value == $form2->value){
									$form2->selected = 'selected';
								}
								echo $this->GetElement('option',$form2);
								echo '-------';
								echo $this->GetElement('option');
								for($i=0,$n=sizeof($array);$i<$n;$i++){
									$key = $array[$i][$f_key];
									$val = $array[$i][$f_value];
									$str = '';
									for($j=1;$j<$array[$i]['level'];$j++){
										$str.='&nbsp;-&nbsp;';
									}

									$form2 = new FormField();
									$form2->value = $key;
									if($value == $form2->value){
										$form2->selected = 'selected';
									}
									echo $this->GetElement('option',$form2);
									echo $str.$val;
									echo $this->GetElement('option');
								}
								echo $this->GetElement('select');
							}
							break;

						default:
							$table_name = $ar_type[1];
							if(!empty($ar_type[2])){
								$_ar = explode('->',$ar_type[2]);
								$f_key = $_ar[0];
								$f_value = $_ar[1];
								$cat_table = false;
								$table_not_cats = $table_name;
								if(preg_match('~^categories_(.*)$~iu',$table_name,$match)){
									$table_not_cats = $match[1];
									$cat_table = true;
								}
								if(!preg_match('~'.$Modules->GetCurrnet().'~iu',$table_not_cats) and $cat_table and $Modules->module[$table_not_cats]->categories->get_num_field('pid') >= 0){
									$array = array();

									$this->GetTree2($table_name,0,$f_key,$f_value,0,$array);

									if(empty($value) && $action == 'add' && $name == 'pid'){
										$value = $pid;
									}
									//$this->GetTree1($cur_table,$_pid,$value,$f_key,$f_value,0,$array);
									$form->type = null;
									$form->value = null;
									$form->tabindex = $this->GetTabindex();
									echo $this->GetElement('select',$form);
									$form2 = new FormField();
									$form2->value = 0;
									if($value == $form2->value){
										$form2->selected = 'selected';
									}
									echo $this->GetElement('option',$form2);
									echo '-------';
									echo $this->GetElement('option');
									for($i=0,$n=sizeof($array);$i<$n;$i++){
										$key = $array[$i][$f_key];
										$val = $array[$i][$f_value];
										$str = '';
										for($j=1;$j<$array[$i]['level'];$j++){
											$str.='&nbsp;-&nbsp;';
										}

										$form2 = new FormField();
										$form2->value = $key;
										if($value == $form2->value){
											$form2->selected = 'selected';
										}
										echo $this->GetElement('option',$form2);
										echo $str.$val;
										echo $this->GetElement('option');
									}
									echo $this->GetElement('select');
								}else {
									$select_fields = array();
									$select_fields[$f_key] = $f_key;
									$select_fields[$f_value] = $f_value;
									$sql = "SELECT `".implode("`, `",$select_fields)."` FROM `".$table_name."` ";
									if($DB->Query($sql)){
										$num_rows = $DB->NumRows();
										if(empty($value) && $action == 'add' && $name == 'pid'){
											$value = $pid;
										}
										if(empty($value) and !empty($_SESSION[$Modules->GetCurrnet()][$pid][$form->name])){
											$value = $_SESSION[$Modules->GetCurrnet()][$pid][$form->name];
										}
										$form->type = null;
										$form->value = null;
										$form->tabindex = $this->GetTabindex();
										if($num_rows > 10000){
											// исключение когда слишком много элементов, чтобы не зависло
											$form->type = 'input';
											$form->value = $value;
											/*if($action !== 'add'){
											$form->disabled = true;
											}*/
											echo $this->GetElement('input',$form);
										}else{
											$value = mb_strtolower($value);
											echo $this->GetElement('select',$form);
											$form2 = new FormField();
											$form2->value = 0;
											if($value == $form2->value){
												$form2->selected = 'selected';
											}
											echo $this->GetElement('option',$form2);
											echo '-------';
											echo $this->GetElement('option');
											while($row = $DB->Next()){
												$key = $row[$f_key];
												$val = $row[$f_value];

												$form2 = new FormField();
												$form2->value = $key;
												if($value == mb_strtolower($form2->value)){
													$form2->selected = 'selected';
												}
												echo $this->GetElement('option',$form2);
												echo $val;
												echo $this->GetElement('option');
											}
											echo $this->GetElement('select');
										}
									}
								}
							}
							break;
					}
				}
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

							$f_type = '';
							if(!empty($json_type['tags']['type'])){
								$f_type = $json_type['tags']['type'];
							}

							$multiple = '';
							if(isset($form->multiple)){
								$multiple = $form->multiple;
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

								if($DB->Query($sql)){
									$ar_value = json_decode($value,true);
									if(empty($ar_value)){
										$ar_value = array();
									}
									$current_categories_fields = &$Modules->module[$Modules->GetCurrnet()]->categories;
									if(empty($value) && $action == 'add' and $table == $current_categories_fields->table->name){
										$num_section_id = $current_categories_fields->get_num_field(array('form->type'=>'select::'.$current_categories_fields->table->name.'::'),true);
										if($num_section_id < 0){
											$num_section_id = $current_categories_fields->get_num_field(array('form->type'=>'select::parent::'),true);
										}
										if($num_section_id < 0){
											$field_cat_pid = 'pid';
										}else{
											$field_cat_pid = $current_categories_fields->field[$num_section_id]->db->field;
										}
										$ar_value = array($URL->GetValue($field_cat_pid));
									}
									if($f_type == 'checkbox'){
										$i = 0;
										while($DB->Next()){
											$row = $DB->GetRecord();
											$value = $row[$f_value];
											$label = $row[$f_label];

											$id_f_d = $form->id.'_'.$value;

											$form2 = new FormField();
											$form2->id = $id_f_d;
											$form2->value = $value;
											$form2->type = 'checkbox';
											$form2->name = $form->name;
											if(!empty($multiple)){
												$form2->name.= '['.$i.']';
											}
											if(in_array($value,$ar_value)){
												$form2->checked = 'checked';
											}
											$form2->tabindex = $this->GetTabindex();

											$form3 = new FormField();
											$form3->type = 'hidden';
											$form3->name = 'hidden_'.$form2->name;
											$form3->value = $form2->value;

											$form4 = new FormField();
											$form4->for = $id_f_d;
											$form4->value = $this->GetElement('input',$form2).$this->GetElement('input',$form3).$label;
											echo $this->GetElement('label',$form4);
											$i++;
										}
									}
									$DB->ClearDataSet();
								}
							}
						}
					}
				}
				break;

			case 'calculate':
				if(empty($json)) {
					if (!empty($ar_type[1])) {
						$form2 = new FormField();
						//$form2->id = $form->id;
						$form2->value = $form->value;
						//$form->id = '';
						echo $this->GetElement('label', $form2);

						$form->type = 'hidden';
						echo $this->GetElement('input', $form);
					}
				}else{
					$form2 = new FormField();
					//$form2->id = $form->id;
					$form2->value = $form->value;
					//$form->id = '';
					echo $this->GetElement('label', $form2);

					$form->type = 'hidden';
					echo $this->GetElement('input', $form);
				}
				break;

			case 'datetime':
				if($sizeof_ar_type > 1){
					switch($ar_type[1]){

						case 'current':
							$date = date("Y-m-d H:i:s");
							$form->value = $date;
							$form->type = 'hidden';
							$this->html_form_element($form);

							$form->value = $date;
							$form->type = 'label';
							$this->html_form_element($form,$el_id);
							break;

						case 'default':
							if($sizeof_ar_type > 2){
								switch($ar_type[2]){
									case 'current':
										if(empty($form->value) or $form->value == '0000-00-00 00:00:00'){
											$form->value = date("Y-m-d H:i:s");
										}
										echo $this->GetElement('input',$form);
										break;
								}
							}
							break;

						case 'add':
							if($sizeof_ar_type > 1){
								$add_date = $ar_type[2];
								$ar_date = explode(' ',$add_date);
								$n_date = explode('-',$ar_date[0]);
								$n_time = explode(':',$ar_date[1]);

								$cur_date_time = array();
								$cur_date_time[0] = date("Y");
								$cur_date_time[1] = date("m");
								$cur_date_time[2] = date("d");
								$cur_date_time[3] = date("H");
								$cur_date_time[4] = date("i");
								$cur_date_time[5] = date("s");

								$last_date_time = array();
								$last_date_time[0] = $cur_date_time[0] + $n_date[0];
								$last_date_time[1] = $cur_date_time[1] + $n_date[1];
								$last_date_time[2] = $cur_date_time[2] + $n_date[2];
								$last_date_time[3] = $cur_date_time[3] + $n_time[0];
								$last_date_time[4] = $cur_date_time[4] + $n_time[1];
								$last_date_time[5] = $cur_date_time[5] + $n_time[2];

								$mk_time_cur = mktime($cur_date_time[3], $cur_date_time[4], $cur_date_time[5], $cur_date_time[1], $cur_date_time[2], $cur_date_time[0]);
								$mk_time = mktime($last_date_time[3], $last_date_time[4], $last_date_time[5], $last_date_time[1], $last_date_time[2], $last_date_time[0]);
								///////////////////////////////////////
								// исключает выходные дни воскресенье и субботу
								$arr = getdate($mk_time);
								$last_date_time[0] = $arr['year'];
								$last_date_time[1] = $arr['mon'];
								$last_date_time[2] = $arr['mday'];
								$last_date_time[3] = $arr['hours'];
								$last_date_time[4] = $arr['minutes'];
								$last_date_time[5] = $arr['seconds'];

								$count_days = ($mk_time-$mk_time_cur)/(3600*24);
								$weekends = 0;
								for($i=0;$i<$count_days;$i++){
									$cur_arr = getdate(mktime($cur_date_time[3], $cur_date_time[4], $cur_date_time[5], $cur_date_time[1], ++$cur_date_time[2], $cur_date_time[0]));
									$w_day = $cur_arr['wday'];
									if($w_day < 1 || $w_day > 5){
										$i--;
										$weekends++;
									}
								}
								$mk_time = mktime($last_date_time[3], $last_date_time[4], $last_date_time[5], $last_date_time[1], $last_date_time[2]+$weekends, $last_date_time[0]);
								///////////////////////////////////////
								$date = date("Y-m-d H:i:s", $mk_time);

								$form2 = new FormField();
								$form2->id = $form->id;
								$form2->value = $date;
								echo $this->GetElement('label',$form2);

								$form->value = $date;
								$form->type = 'hidden';
								$form->id = '';
								echo $this->GetElement('input',$form);

								/*$add_date = $ar_type[2];
								$ar_date = explode(' ',$add_date);
								$n_date = explode('-',$ar_date[0]);
								$n_time = explode(':',$ar_date[1]);
								$mk_time = mktime(date("H")+$n_time[0], date("i")+$n_time[1], date("s")+$n_time[2], date("m")+$n_date[1], date("d")+$n_date[2], date("Y")+$n_date[0]);
								///////////////////////////////////////
								// исключает выходные дни воскресенье и субботу
								$arr = getdate($mk_time);
								$w_day = $arr['wday'];
								if($w_day < 1 || $w_day > 5){
								$mk_time = mktime(date("H")+$n_time[0], date("i")+$n_time[1], date("s")+$n_time[2], date("m")+$n_date[1], date("d")+$n_date[2]+2, date("Y")+$n_date[0]);
								}
								///////////////////////////////////////
								$date = date("Y-m-d H:i:s", $mk_time);

								$form2 = new FormField();
								$form2->id = $form->id;
								$form2->value = $date;
								echo $this->GetElement('label',$form2);

								$form->value = $date;
								$form->type = 'hidden';
								$form->id = '';
								echo $this->GetElement('input',$form);*/
							}
							break;
					}
				}else{
					$form->type="text";
					if(empty($form->value) || $action == 'add'){
						$form->value = date("Y-m-d H:i:s");
					}
					$form->tabindex = $this->GetTabindex();
					echo $this->GetElement('input',$form);
				}
				break;

			case 'date':
				if($sizeof_ar_type > 1){
					switch($ar_type[1]){
						case 'current':
							$date = date("Y-m-d");
							$form->value = $date;
							$form->type = 'hidden';
							$this->html_form_element($form);

							$form->value = $date;
							$form->type = 'label';
							$this->html_form_element($form,$el_id);
							break;
					}
				}else{
					$form->type="text";
					if(empty($form->value) || $action == 'add'){
						$form->value = date("Y-m-d");
					}
					$form->tabindex = $this->GetTabindex();
					echo $this->GetElement('input',$form);
				}
				break;

			case 'time':
				$format = 'HH:MM:SS';
				if($sizeof_ar_type > 0){
					$format = strtoupper($ar_type[1]);
				}
				$ar_format = explode(':',$format);
				$sizeof_ar_format = sizeof($ar_format);

				if(empty($value)){
					$value = '00:00:00';
				}
				$ar_value = explode(':',$value);
				$ar_value['HH'] = &$ar_value[0];
				$ar_value['MM'] = &$ar_value[1];
				$ar_value['SS'] = &$ar_value[2];

				$form2 = new FormField();
				for($i=0;$i<$sizeof_ar_format;$i++){
					if($i > 0){
						echo ':';
					}
					$form2->name = $ar_format[$i];
					$form2->value = $ar_value[$ar_format[$i]];
					$form2->type = 'text';
					$form2->size = 2;
					$form2->maxlength = 2;
					$form2->tabindex = $this->GetTabindex();
					echo $this->GetElement('input',$form2);
				}
				$form->value = $value;
				$form->type = 'hidden';
				echo $this->GetElement('input',$form);
				break;

			case 'label':
				$form->type = '';
				echo $this->GetElement('label',$form);
				break;

			case 'access':
				if($action == 'edit'){
					$ACCESS->GetValueForEdit($value);
				}
				$form->tabindex = $this->GetTabindex();
				$ACCESS->html_form($form);
				$this->tabindex = $form->tabindex;
				break;
		}
	}

	/**
	* Возвращает дерево элементов выстроенных по иерархии исключая значение $cur_val из поля $f_key
	*
	* @param mixed $table
	* @param mixed $pid
	* @param mixed $cur_val
	* @param mixed $f_key
	* @param mixed $f_value
	* @param mixed $level
	* @param mixed $array
	* @param mixed $i
	*/
	function GetTree1($table,$pid=0,$cur_val,$f_key,$f_value,$level=0,&$array,&$i=0){
		global $_fields;
		$DB = new DB();
		$sql = "SELECT `".$f_key."`, `".$f_value."` ";
		$sql.= "FROM `".$table."` ";
		$sql.= "WHERE `pid` = '".$pid."' ";
		$sql.= "AND `".$f_key."` <> '".$cur_val."'";
		if($DB->Query($sql)){
			if($DB->NumRows() > 0){
				$level++;
				while($DB->Next()){
					$id = $DB->Value($f_key);
					$array[$i][$f_key] = $DB->Value($f_key);
					$array[$i][$f_value] = $DB->Value($f_value);
					$array[$i]['level'] = $level;
					$i++;
					if($id != $pid){
						$this->GetTree1($table,$id,$cur_val,$f_key,$f_value,$level,$array,$i);
					}
				}
			}
		}
	}

	/**
	* Возвращает дерево элементов выстроенных по иерархии
	*
	* @param mixed $table
	* @param mixed $pid
	* @param mixed $f_key
	* @param mixed $f_value
	* @param mixed $level
	* @param mixed $array
	* @param mixed $i
	*/
	function GetTree2($table,$pid=null,$f_key,$f_value,$level=0,&$array,&$i=0){
		$DB = new DB();
		$sql = "SELECT `".$f_key."`, `".$f_value."` ";
		$sql.= "FROM `".$table."` ";
		if(!is_null($pid)){
			$sql.= "WHERE `pid` = '".$pid."' ";
		}
		$sql.= "ORDER BY `".$f_value."` ASC";
		if($DB->Query($sql)){
			if($DB->NumRows() > 0){
				$level++;
				while($DB->Next()){
					$id = $DB->Value($f_key);
					$array[$i][$f_key] = $DB->Value($f_key);
					$array[$i][$f_value] = $DB->Value($f_value);
					$array[$i]['level'] = $level;
					$i++;
					if(!is_null($pid) and $id != $pid){
						$this->GetTree2($table,$id,$f_key,$f_value,$level,$array,$i);
					}
				}
			}
		}
	}

	/**
	* Возвращает текущее значение tabindex и увеливает значение на единицу если $ai = true
	*
	* @param mixed $ai
	*/
	function GetTabindex($ai=true){
		if($ai){
			$this->tabindex++;
		}
		return $this->tabindex;
	}

	/**
	* устанавливает текущее значение tabindex
	*
	* @param mixed $tabindex
	*/
	function SetTabindex($tabindex){
		$this->tabindex = $tabindex;
		return $this->tabindex;
	}

	/**
	* Возвращает html код заданного элемента по типу $type и с параметрами $form
	*
	* @param mixed $type
	* @param mixed $form
	*/
	static function GetElement($type,$form=false){
		if(!empty($form)){
			$params = get_class_vars(get_class($form));
		}
		$element = '';
		switch($type){

			case 'input':
			case 'img':
				$element = '<'.$type;
				foreach ($params as $name=>$value) {
					if(isset($form->$name)){
						$value = $form->$name;
						if(!is_null($value)){
							$element.= ' '.$name.'="'.$value.'" ';
						}
					}
				}
				$element.= '/>';
				break;

			case 'label':
			case 'textarea':
				$element = '<'.$type;
				$text = '';
				foreach ($params as $name=>$value) {
					$value = $form->$name;
					if($name == 'value'){
						$text = $value;
					}else{
						if(!empty($value)){
							$element.= ' '.$name.'="'.$value.'"';
						}
					}
				}
				$element.= '>'.$text.'</'.$type.'>';
				break;

			case 'select':
			case 'option':
				if(empty($form)){
					$element = '</'.$type.'>';
				}else{
					$element = '<'.$type;
					foreach ($params as $name=>$value){
						if(isset($form->$name)) {
							$value = $form->$name;
							$element .= ' ' . $name . '="' . $value . '"';
						}
					}
					$element.= '>';
				}
				break;
		}
		return $element;
	}

}

?>
