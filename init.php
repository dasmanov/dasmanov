<?php if(!defined('ACCESS_CODE') || intval(ACCESS_CODE+ACCESS_CODE*ACCESS_CODE) != 6) die('Access Error');
$_REQUEST['MESSAGES'] = '';
$_REQUEST['ERROR_MSG'] = '';

require_once("config.php");
if(defined('TIME_ZONE')){
	$tz = TIME_ZONE;
	if(!empty($tz)){
		if(!date_default_timezone_set($tz)){
			$_REQUEST['ERROR_MSG'].= 'Ошибка указания часового пояса';
		}
	}
}
include_once('session_config.php');

header("HTTP/1.0 200 Ok");
header("Expires: Thu, 19 Feb 1998 13:24:18 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0,pre-check=0");
header("Cache-Control: max-age=0");
header("Pragma: no-cache");

if(defined('SHOW_ERRORS') && SHOW_ERRORS){
	error_reporting(E_ALL);
	ini_set('display_errors','On');
}else{
	error_reporting(0);
}


define('RELATIVE','/dasmanov/');

define('DOCROOT', $_SERVER['DOCUMENT_ROOT'].'/');
define('DOCROOT_IMAGES', DOCROOT.'img/');
define('DOCROOT_CSS', DOCROOT.'css/');
define('DOCROOT_JS', DOCROOT.'js/');

define('HTTPROOT', 'http://'.$_SERVER['HTTP_HOST'].'/');
define('HTTP_REDIRECT', 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
define('HTTPROOT_IMAGES', HTTPROOT.'images/');
define('HTTPROOT_CSS', HTTPROOT.'css/');
define('HTTPROOT_JS', HTTPROOT.'js/');

define('ADMINISTRATOR_DOCROOT', DOCROOT.'dasmanov/');
define('ADMINISTRATOR_DOCROOT_TABLES', ADMINISTRATOR_DOCROOT.'tables/');
define('ADMINISTRATOR_DOCROOT_IMAGES', ADMINISTRATOR_DOCROOT.'img/');
define('ADMINISTRATOR_DOCROOT_CSS', ADMINISTRATOR_DOCROOT.'css/');
define('ADMINISTRATOR_DOCROOT_JS', ADMINISTRATOR_DOCROOT.'js/');


define('ADMINISTRATOR_HTTPROOT', HTTPROOT);
define('ADMINISTRATOR_HTTPROOT_IMAGES', ADMINISTRATOR_HTTPROOT.'img/');
define('ADMINISTRATOR_HTTPROOT_CSS', ADMINISTRATOR_HTTPROOT.'css/');
define('ADMINISTRATOR_HTTPROOT_JS', ADMINISTRATOR_HTTPROOT.'js/');

define('ADMINISTRATOR_PATH', '/dasmanov/');
define('ADMINISTRATOR_PATHROOT', ADMINISTRATOR_PATH);
define('ADMINISTRATOR_PATHROOT_IMAGES', ADMINISTRATOR_PATH.'img/');
define('ADMINISTRATOR_PATHROOT_CSS', ADMINISTRATOR_PATH.'css/');
define('ADMINISTRATOR_PATHROOT_JS', ADMINISTRATOR_PATH.'js/');

require("inc/user.class.php");
require("inc/text.class.php");
require("inc/logs.class.php");
require("inc/datetime.class.php");
require("inc/url.class.php");
require("inc/DB/mysqli.class.php");
require("inc/fields.class.php");
require("inc/modules.class.php");
require("inc/breadcrumb.class.php");
require("inc/form.class.php");
require("inc/post.class.php");
require("inc/access.class.php");
require("inc/image.class.php");
require("inc/cache.class.php");
require("inc/cookie.class.php");
require_once $_SERVER['DOCUMENT_ROOT'].'/inc/functions.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/inc/language.class.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/inc/application.class.php';


$DB = new DB($DB_CONFIG);
unset($DB_CONFIG);
//$Modules = new MODULES(ADMINISTRATOR_PATH);
$Modules = new MODULES(ADMINISTRATOR_DOCROOT_TABLES);
$Modules->DB = &$DB;
$Modules->Init();

//echo("<pre>");print_r($Modules);echo("</pre>");
$HTML = '';
$URL = new URL();
$POST = new POST();
$CACHE = new Cache();
//$ASTRO = new ASTRO();

if(class_exists('USER')){
	$USER = new USER;
	//is_login_by_cookie();
	$ACCESS = new ACCESS();
	$BREADCRUMB = new BREADCRUMB();

	$module = $URL->GetValue('module');
	$action = $URL->GetValue('action');
	$id = $URL->GetValue('id');
	$pid = $URL->GetValue('pid');
	if(empty($action)){
		$action = 'view';
	}

	$file = $URL->GetValue('file');
	if(!empty($file)){
		$cur_fields = $Modules->module[$module]->fields;
		$sql = "SELECT * ";
		$sql.= "FROM `".$cur_fields->table->name."` ";

		$ar_files = $cur_fields->GetValuesByValue('form::type','file');
		$name_file = '';
		if(!empty($ar_files[0])){
			$cur_field = $cur_fields->field[$ar_files[0]];
			$type = $cur_field->form->type;
			$ar_type = explode('::',$type);
			if($ar_type[1] == 'name_to'){
				$name_file = $ar_type[2];
			}
			$sql.= "WHERE `".$cur_field->db->field."` = '".$file."' ";
		}
		if($DB->Query($sql)){
			if($DB->Next()){
				$row = $DB->GetRecord();
				$name_file = $row[$name_file];
			}
		}
		if(empty($name_file)){
			$name_file = $file;
		}
		$full_name_patch = DOCROOT.'upload/'.$file;
		if(file_exists($full_name_patch)){
			// сбрасываем буфер вывода PHP, чтобы избежать переполнения памяти выделенной под скрипт
			// если этого не сделать файл будет читаться в память полностью!
			if (ob_get_level()) {
				ob_end_clean();
			}
			if(preg_match('~windows~isu',$_SERVER['HTTP_USER_AGENT'])){
				$name_file = iconv('UTF-8', 'WINDOWS-1251', $name_file);
			}
			// заставляем браузер показать окно сохранения файла
			header('Content-Description: File Transfer');
			header('Content-Type: application/octet-stream');
			header('Content-Disposition: attachment; filename=' . $name_file);
			header('Content-Transfer-Encoding: binary');
			header('Expires: 0');
			header('Cache-Control: must-revalidate');
			header('Pragma: public');
			header('Content-Length: ' . filesize($full_name_patch));
			// читаем файл и отправляем его пользователю
			readfile($full_name_patch);
		}else{
			echo 'Запрошенный Файл не найден';
		}
		exit();
	}

	ob_start();
	$BREADCRUMB->BuildAuto();
	require('template/astroinfinity/header.php');
	if($USER->isAuthorized() and $USER->isAdmin()){
		if(empty($module)){
			include(ADMINISTRATOR_DOCROOT.'modules/center/index.php');
		}else{
			if($ACCESS->IsAccess()){
				switch($action){
					case 'add':
						$POST->CopyFromSession();
						$Modules->module[$module]->FormSave();
						break;
					case 'check_add':
						$Modules->module[$module]->Save();
						break;
					case 'edit':
						$Modules->module[$module]->FormSave();
						break;
					case 'check_edit':
						$Modules->module[$module]->Save();
						break;
					case 'delete':
						$Modules->module[$module]->Delete();
						break;
					case 'check_delete':
						$Modules->module[$module]->CheckDelete();
						break;
					case 'view':
					default:
						$POST->ClearFromSession();
						$Modules->module[$module]->View();
						break;
				}
			}
		}
		/*if(defined('SHOW_ERRORS') and SHOW_ERRORS and !empty($_REQUEST['ERROR_MSG'])){
			echo $_REQUEST['ERROR_MSG'];
		}
		if(defined('SHOW_MESSAGES') and SHOW_ERRORS and !empty($_REQUEST['MESSAGES'])){
			echo $_REQUEST['MESSAGES'];
		}*/
	}
	require('template/astroinfinity/footer.php');
	$HTML = ob_get_contents();
	ob_clean();
}
$HTML = str_replace("\t",'',$HTML);
echo $HTML;
unset($DB);
exit();
?>
