<?php if(!defined('ACCESS_CODE') || intval(ACCESS_CODE+ACCESS_CODE*ACCESS_CODE) != 6) die('Access Error');

	$n = &$Modules->count;
	if($n > 0){
	?><div id="lmenu" class="flex_item">
		<ul id="left_menu" class="flex_container">
			<?for($i=0;$i<$n;$i++){
					$href = new URL(true);
					$cur_module = &$Modules->module[$i];
					$href->SetValue('module',$cur_module->name);
					$href = $href->GetURL();
					$title = $cur_module->title;
					if($ACCESS->IsAccess($cur_module->name,'view')){
					?><li class="flex_item"><a href="<?=$href;?>"><?=$title;?></a></li><?
					}
				}
			?>
		</ul>
	</div>
	<?
	}
	unset($i,$n,$href,$title);
?>
