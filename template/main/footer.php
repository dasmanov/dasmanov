<?php if(!defined('ACCESS_CODE') || intval(ACCESS_CODE+ACCESS_CODE*ACCESS_CODE) != 6) die('Access Error');?>
<?
if(defined('SHOW_ERRORS') and SHOW_ERRORS and !empty($_REQUEST['ERROR_MSG'])){
	echo $_REQUEST['ERROR_MSG'];
}
if(defined('SHOW_MESSAGES') and SHOW_ERRORS and !empty($_REQUEST['MESSAGES'])){
	echo $_REQUEST['MESSAGES'];
}
?></div></div>
<?if($USER->isAuthorized() and $USER->isAdmin()){?></div><?}
if (!isset($addJS)){
	$addJS = array();
}
$addJS[] = ADMINISTRATOR_PATH.'js/jquery.min.js';
$addJS[] = ADMINISTRATOR_PATH.'js/sonic.min.js';
$addJS[] = ADMINISTRATOR_PATH.'js/loader.min.js';
$addJS[] = ADMINISTRATOR_PATH.'js/jquery-ui-1.8.20.custom.min.js';
$addJS[] = ADMINISTRATOR_PATH.'js/jquery-ui-timepicker-addon.min.js';
$addJS[] = ADMINISTRATOR_PATH.'js/init.min.js';
//$addJS[] = ADMINISTRATOR_PATH.'js/w2.min.js';
$addJS[] = ADMINISTRATOR_PATH.'js/lighter.min.js';
$addJS[] = ADMINISTRATOR_PATH.'js/form.min.js';
$addJS[] = ADMINISTRATOR_PATH.'ckeditor/ckeditor.js';
$addJS[] = ADMINISTRATOR_PATH.'ckeditor/adapters/jquery.js';
$addJS[] = ADMINISTRATOR_PATH.'ckfinder/ckfinder.js';
$addJS[] = ADMINISTRATOR_PATH.'ckfinder/ckfinder_init.js';
$addJS[] = ADMINISTRATOR_PATH.'js/user_auth.min.js';
if (isset($addJS) && is_array($addJS)){
	foreach ($addJS as $line){
		?><script type="text/javascript" src="<?php 
			echo $line; 
			if(preg_match('~^\/(js|ckeditor|ckfinder)\/(.*)$~isu',$line)){
				?>?<?=filemtime($_SERVER['DOCUMENT_ROOT'].$line);
			}
			?>"></script><?php
	}
}
/*
?>
<script type="text/javascript" src="<?=ADMINISTRATOR_PATH;?>js/jquery.min.js"></script>
<script type="text/javascript" src="<?=ADMINISTRATOR_PATH;?>js/sonic.min.js"></script>
<script type="text/javascript" src="<?=ADMINISTRATOR_PATH;?>js/loader.min.js"></script>
<script type="text/javascript" src="<?=ADMINISTRATOR_PATH;?>js/jquery-ui-1.8.20.custom.min.js"></script>
<script type="text/javascript" src="<?=ADMINISTRATOR_PATH;?>js/jquery-ui-timepicker-addon.min.js"></script>
<!--[if lt IE 7]>
<script type="text/javascript" src="<?=ADMINISTRATOR_PATH;?>js/pngfix.min.js"></script>
<script type="text/javascript">PNG_fix.fix('.pngfix');</script>
<![endif]-->
<script type="text/javascript" src="<?=ADMINISTRATOR_PATH;?>js/init.min.js"></script>
<script type="text/javascript" src="<?=ADMINISTRATOR_PATH;?>js/w2.min.js"></script>
<script type="text/javascript" src="<?=ADMINISTRATOR_PATH;?>js/lighter.min.js"></script>
<script type="text/javascript" src="<?=ADMINISTRATOR_PATH;?>js/form.min.js"></script>
<script type="text/javascript" src="<?=ADMINISTRATOR_PATH;?>ckeditor/ckeditor.js"></script>
<script type="text/javascript" src="<?=ADMINISTRATOR_PATH;?>ckeditor/adapters/jquery.js"></script>
<script type="text/javascript" src="<?=ADMINISTRATOR_PATH;?>ckfinder/ckfinder.js"></script>
<script type="text/javascript" src="<?=ADMINISTRATOR_PATH;?>ckfinder/ckfinder_init.js"></script>
<?if(defined('SLOT_JS_1')){?><script type="text/javascript" src="<?=SLOT_JS_1;?>"></script><?}?>
<?if(defined('SLOT_JS_2')){?><script type="text/javascript" src="<?=SLOT_JS_2;?>"></script><?}?>
<?if(defined('SLOT_JS_3')){?><script type="text/javascript" src="<?=SLOT_JS_3;?>"></script><?}?>
<?if(defined('SLOT_JS_4')){?><script type="text/javascript" src="<?=SLOT_JS_4;?>"></script><?}?>
<script type="text/javascript" src="<?=ADMINISTRATOR_PATH;?>js/user_auth.min.js"></script><?
//*/
?><noscript><meta http-equiv="refresh" content="0; url=/nojavascript.html" /></noscript>
</body>
</html>