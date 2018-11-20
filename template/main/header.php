<?php if(!defined('ACCESS_CODE') || intval(ACCESS_CODE+ACCESS_CODE*ACCESS_CODE) != 6) die('Access Error');?><!DOCTYPE html>
<html>
<head>
	<title>:: Добро пожаловать в систему управлением сайтом DASMANOV ::</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="Content-Language" content="ru" /><?
	if (!isset($addCSS)){
		$addCSS = array();
	}
	$addCSS[] = '/dasmanov/css/styles.css';
	$addCSS[] = '/dasmanov/css/jquery-ui-1.8.20.custom.css';
	if (isset($addCSS) && is_array($addCSS)){
		foreach ($addCSS as $line){
			?><link type="text/css" rel="stylesheet" href="<?php 
				echo $line; 
				if(preg_match('~^\/css\/(.*)$~isu',$line)){
					?>?<?=filemtime($_SERVER['DOCUMENT_ROOT'].$line);
				}
				?>" /><?php
		}
	}
	?>
</head>
<body><?if($USER->isAuthorized() and $USER->isAdmin()){?>
	<div id="body2" class="flex_container"><?$USER->FormLogout()?>
	<div id="top" class="flex_item"><div id="logo" class="pngfix"></div></div>
	<div id="breadcrumb" class="flex_item"><?$BREADCRUMB->Show()?></div>
	<div id="center" class="flex_item flex_container"><?include(ADMINISTRATOR_DOCROOT.'modules/left_menu/index.php');?>
	<div id="content" class="flex_item">
	<?}else{
	?><div id="body1">
	<div class="content1" style="padding-top: 46px;">
	<div class="preloader"></div><?
}?>