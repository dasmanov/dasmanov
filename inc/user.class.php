<?php if(!defined('ACCESS_CODE') || intval(ACCESS_CODE+ACCESS_CODE*ACCESS_CODE) != 6) die('Access Error');

class USER {
	private $id;

	var $failed = 5;
	var $attempts;
	private $authorized = false;
	private $is_admin = false;
	private $blocked = false;
	private $table_name = 'sys_users';
	private $category_table_name = 'categories_sys_users';

	function __construct(){
		$this->count_attempts();
		if(!empty($_SESSION['blocked'])){
			$this->blocked = true;
		}
		if(!empty($_SESSION['user_id'])){
			$this->id = $_SESSION['user_id'];
		}
	}

	/**
	* возвращает ID пользователя
	*
	*/
	function getID(){
		return $this->id;
	}

	/**
	* возвращает количество возможных попыток авторизации
	*
	*/
	function count_attempts(){
		if(!empty($_SESSION['failed'])){
			$this->attempts = $this->failed - $_SESSION['failed'];
		}else{
			$this->attempts = $this->failed;
		}
		return $this->attempts;
	}

	/**
	* Проверят авторизованность пользователя, возвращает true если авторизован и false в остальных случаях
	*
	*/
	function isAuthorized(){
		if($this->authorized){
			return true;
			//}elseif(isset($_SESSION['user_id']) AND $_SESSION['ip'] == $_SERVER['REMOTE_ADDR'] and !empty($_SESSION['logged'])){
		}elseif(isset($_SESSION['user_id']) and !empty($_SESSION['logged'])){
			$this->authorized = true;
			return true;
		}elseif($this->is_login_by_cookie()){
			return true;
		}
		return false;
	}

	/**
	* Возвращает true если пользователь админ, false в противном случае
	*
	*/
	function isAdmin(){
		if($this->is_admin){
			return true;
		}elseif(!empty($_SESSION['is_admin'])){
			$this->is_admin = true;
			return true;
		}
		return false;
	}

	/**
	* Если пользователь исчерпал возможные попытки для авторизации, то возвращается true, или false в противном случае
	*
	*/
	function checkFailed(){
		if(!empty($_SESSION['failed']) and $_SESSION['failed'] >= $this->failed){
			echo 'Ваши попытки исчерпаны, попробуйте повторить позже или обратитесь к администратору для восстановления учетной записи';
			return true;
		}
		return false;
	}

	/**
	* Пытается авторизоваться если пользователь ввел все данные верно
	*
	*/
	function LogIn(){
		global $POST;
		if(!$this->blocked){
			if($POST->noempty('check_log_in')){
				if($this->checkFailed()){
					return false;
				}
				$md5_email = md5($POST->GetValue('email'));
				$md5_password = md5($POST->GetValue('password'));
				global $DB;
				$DB->select();
				$DB->from($this->table_name);
				$DB->where(array('md5_email'=>$md5_email,'md5_password'=>$md5_password,'active'=>1));
				/*$sql = "SELECT * ";
				$sql.= "FROM `".$this->table_name."` ";
				$sql.= "WHERE `md5_email`='".$md5_email."' ";
				$sql.= "AND `md5_password`='".$md5_password."' ";
				$sql.= "AND `active` > 0";//*/
				if($DB->execute()){
					if($DB->NumRows() > 0){
						if($ar_fields = $DB->Next()){
							$this->data_login_process($ar_fields);
							return true;
						}
					}
					$DB->ClearDataSet();
				}
				if(empty($_SESSION['user_id'])){
					if(empty($_SESSION['failed'])){
						$_SESSION['failed'] = 0;
					}
					$_SESSION['failed']++;
					if($this->count_attempts() > 0){
						echo('Неудачная попытка входа, осталось попыток: '.$this->count_attempts().'');
					}else{
						if($this->checkFailed()){
							return false;
						}
					}
				}
			}else{
				$this->blocked = true;
				$_SESSION['blocked'] = true;
			}
		}
		return false;
	}

	function data_login_process($ar_fields){
		global $DB,$LANGUAGE;
		$_SESSION['user_id'] = $this->id = $ar_fields['id'];
		if(!empty($ar_fields['company_id'])){
			$_SESSION['company_id'] = $ar_fields['company_id'];
		}
		if(!empty($ar_fields['groups_id'])){
			$_SESSION['groups_id'] = json_decode($ar_fields['groups_id'],true);
			if(is_array($_SESSION['groups_id'])){
				$sql2 = "SELECT * ";
				$sql2.= "FROM `".$this->category_table_name."` ";
				$sql2.= "WHERE `ID` IN ('".implode("','",$_SESSION['groups_id'])."') ";
				$sql2.= "AND `active` > 0";
				$DB2 = new DB();

				if($DB2->Query($sql2)){
					if($DB2->NumRows() > 0){
						while($DB2->Next()){
							$is_admin = $DB2->Value('is_admin');
							if(!empty($is_admin)){
								$_SESSION['is_admin'] = true;
								break;
							}
						}
					}
					$DB2->ClearDataSet();
				}
			}
		}

		//$_SESSION['group_id'] = $DB->Value('pid');

		$_SESSION['ip'] = $_SERVER['REMOTE_ADDR'];
		$_SESSION['logged'] = true;
		unset($_SESSION['failed']);

		$last_login = date("Y-m-d H:i:s");
		$auth_code = md5($last_login.$this->id);
		Cookie::set('auth_code',$auth_code);
		$DB->UpdateRow($this->table_name,array('id'=>$this->id),array('last_login'=>$last_login,'auth_code'=>$auth_code));

		//echo("<pre>");print_r($_SESSION);echo("</pre>");
		//die();
	}

	/**
	* Завершает сеанс
	*
	*/
	function LogOut(){
		global $POST;
		if($POST->noempty('check_log_out')){
			Cookie::delete('auth_code');
			return session_destroy();
		}
		return false;
	}

	/**
	* Вывод формы для авторизации
	*
	*/
	function getAuthForm(){
		if($this->blocked){
			echo('Ваш пользователь заблокирован');
			return false;
		}elseif($this->checkFailed()){
			return false;
		}elseif($this->isAuthorized() and !$this->isAdmin()){
			echo('У вас нет доступа к административной части');
		}else{
			?><form id="f_auth" method="post" action="" class="pngfix">
				<fieldset>
					<table id="t_auth" cellpadding="0" cellspacing="0">
						<thead>
							<tr><td align="right"><div class="pngfix"></div></td><th><label>Авторизация</label></th><td></td></tr>
							<tr><th colspan="3"><div class="sr"></div></th></tr>
						</thead>
						<tbody>
							<tr><td align="right"><label for="email">Логин</label></td><td align="left"><div class="in_text"><div class="in"><input id="email" type="text" name="email" maxlength="25" tabindex="1" /></div></div></td><td></td></tr>
							<tr><td align="right"><label for="password">Пароль</label></td><td align="left"><div class="in_text"><div class="in"><input id="password" type="password" name="password" maxlength="25" tabindex="2" /></div></div></td><th align="left"><input class="pngfix" type="submit" value="" title="Войти" tabindex="3" /></th></tr>
						</tbody>
					</table>
				</fieldset>
			</form><?
			return true;
		}
	}

	/*private function LoadPageAuth(){
	global $URL;
	ob_start();
	?><!DOCTYPE html>
	<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
	<title>:: Авторизация ::</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<meta http-equiv="Content-Language" content="ru" />
	<link href="<?=ADMINISTRATOR_PATH;?>css/styles.css" type="text/css" rel="stylesheet" />
	</head>
	<body>
	<div id="body1">
	<div class="content1" style="padding-top: 46px;">

	<?
	if(defined('SHOW_ERRORS') and SHOW_ERRORS and !empty($_REQUEST['ERROR_MSG'])){
	echo $_REQUEST['ERROR_MSG'];
	}
	if(defined('SHOW_MESSAGES') and SHOW_ERRORS and !empty($_REQUEST['MESSAGES'])){
	echo $_REQUEST['MESSAGES'];
	}
	?>
	</div>
	</div>
	<script type="text/javascript" src="<?=ADMINISTRATOR_PATH;?>js/jquery.js"></script>
	<script type="text/javascript" src="<?=ADMINISTRATOR_PATH;?>js/main_auth.js"></script>
	<!--[if lt IE 7]>
	<script type="text/javascript" src="<?=ADMINISTRATOR_PATH;?>js/pngfix.js"></script>
	<script type="text/javascript">PNG_fix.fix('.pngfix');</script>
	<![endif]-->
	<noscript><meta http-equiv="refresh" content="0; url=/nojavascript.html" /></noscript>
	</body>
	</html><?
	$form_auth = ob_get_contents();
	ob_end_clean();
	echo $form_auth;
	}*/

	/**
	* Вывод формы для выхода
	*
	*/
	function FormLogout(){
		global $DB;
		$DB->select();
		$DB->from($this->table_name);
		$DB->where(array('id'=>$this->id));
		if($DB->execute()){
			if($DB->Next()){
				?><form id="f_logout" method="post" action="">
					<fieldset>
						<label><?=$DB->Value('name');?> [<?=$DB->Value('email');?>]</label>
						<input type="submit" value="Выйти" tabindex="100" />
					</fieldset>
				</form><?
			}
			$DB->ClearDataSet();
		}
	}

	/**
	* Залогинен ли через COOKIE
	*
	*/
	function is_login_by_cookie(){
		global $DB;
		$auth_code = Cookie::get('auth_code');
		if(!empty($auth_code) and empty($_SESSION['user_id'])){
			$DB->select();
			$DB->from($this->table_name);
			$DB->where(array('auth_code'=>$auth_code));
			if($DB->execute()){
				if($DB->NumRows() > 0){
					if($ar_fields = $DB->Next()){
						$this->data_login_process($ar_fields);
						return true;
					}
				}else{
					Cookie::delete('auth_code');
					ob_clean();
					@header("location:/dasmanov/");
					echo '<script>window.location="/dasmanov/";</script>';//*/
					exit();
				}
				$DB->ClearDataSet();
			}
		}//*/
		return false;
	}
}

?>