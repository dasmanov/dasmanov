<? if(!defined('ACCESS_CODE') || intval(ACCESS_CODE+ACCESS_CODE*ACCESS_CODE) != 6) die('Access Error');
	define('BASEPATH',true);
	$DB_CONFIG['name']	= "astroinfinity_ru";
	$DB_CONFIG['host']	= "localhost";
	$DB_CONFIG['user']	= "root";
	$DB_CONFIG['pass']	= "";

	define('UPDATE_CONFIGURATION', 1);

	define('SHOW_ERRORS', 1);
	define('SHOW_MESSAGES', 1);
	define('SQL_LOGS', 1);

	define('TIME_ZONE', 'Europe/Moscow');
?>