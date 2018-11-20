<?
define('ACCESS_CODE', 2);
define('DOCROOT', $_SERVER['DOCUMENT_ROOT'].'/');
$root_path = DOCROOT.'dasmanov';
require($root_path.'/config.php');
require($root_path.'/session_config.php');
require($root_path.'/inc/post.class.php');
require($root_path.'/inc/user.class.php');
require($root_path.'/inc/cookie.class.php');
require($root_path.'/inc/cache.class.php');
require($root_path.'/inc/DB/mysqli.class.php');
$DB = new DB($DB_CONFIG);
unset($DB_CONFIG);

$POST = new POST();
$CACHE = new Cache();
$LANGUAGE = new Language();
$er404 = true;
$module = $POST->GetValue('module');
$action = $POST->GetValue('action');
$json = $POST->GetValue('json');
if(!empty($json)){
	$ar_json = array();
	ob_start();
}
////////////////////////////////////////////////////////
if($module == 'sys_users'){
	if(class_exists('USER')){
		$USER = new USER();
		if($action == 'load_form_login'){
			if($USER->getAuthForm()){
				echo('{SUCCESS}');
			}
			$er404 = false;
		}elseif($action == 'login'){
			if($USER->isAuthorized()){
				//if($USER->isAdmin()){
				echo('{REFRESH}');
				//}
			}elseif($USER->LogIn()){
				if($USER->isAdmin()){
					echo('{SUCCESS}');
				}else{
					echo('{REFRESH}');
				}
			}
			$er404 = false;
		}elseif($action == 'logout'){
			if($USER->LogOut()){
				echo('{SUCCESS}');
			}else{
				echo('Неудачная попытка выхода');
			}
			$er404 = false;
		}
	}else{
		echo('{FATAL}');
		echo('Не подключен класс проверки автоизации');
		$er404 = false;
	}
}
////////////////////////////////////////////////////////
/*if(!empty($_GET['test']) && $_GET['test']=='dasmanov'){
session_destroy();
}*/
////////////////////////////////////////////////////////
/*if(!empty($_GET['test']) && $_GET['test']=='dasmanov'){
if(empty($_SESSION['user_id'])){
$email = trim($POST->GetValue('email'));
$password = trim($POST->GetValue('password'));
if(!empty($email) and !empty($password)){
$md5_email = md5($email);
$md5_password = md5($password);
$sql = "SELECT * ";
$sql.= "FROM `sys_users` ";
$sql.= "WHERE `md5_email`='".$md5_email."' ";
$sql.= "AND `md5_password`='".$md5_password."' ";
$sql.= "AND `active` > 0";
if($DB->Query($sql)){
if($DB->Next()){
@session_start();
$_SESSION['user_id'] = $DB->Value('id');
$_SESSION['group_id'] = $DB->Value('pid');
$_SESSION['ip'] = $_SERVER['REMOTE_ADDR'];
}
}
}
$POST->ClearFromSession();
}
if(isset($_SESSION['user_id']) AND $_SESSION['ip'] == $_SERVER['REMOTE_ADDR']){
//
echo json_encode($_SESSION);
}
$er404 = false;
}*/
if($er404){
	header("HTTP/1.0 404 Not Found");
}
if(!empty($json)){
	$ar_json['html'] = ob_get_contents();ob_get_clean();
	$json_string = json_encode($ar_json,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
	echo $json_string;
}
?>