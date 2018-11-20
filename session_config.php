<?php if(!defined('ACCESS_CODE') || intval(ACCESS_CODE+ACCESS_CODE*ACCESS_CODE) != 6) die('Access Error');
	ini_set('session.save_path', $_SERVER['DOCUMENT_ROOT'].'/dasmanov/cache/sessions');
	ini_set('session.cookie_lifetime', 120960);
	ini_set('session.gc_maxlifetime', 120960);
	ini_set('session.gc_probability', 100);
	session_start();
?>