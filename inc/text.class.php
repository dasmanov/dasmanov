<?php if(!defined('ACCESS_CODE') || intval(ACCESS_CODE+ACCESS_CODE*ACCESS_CODE) != 6) die('Access Error');

class TEXT {

	/**
	* переводит текстовую строку $s из utf8 кодировки в cp1251 и возвращает полученную строку
	*
	* @param mixed $s
	*/
	static function utf8_to_cp1251($s){
		if(is_array($s)){
			foreach($s as $key=>&$val){
				$val = TEXT::utf8_to_cp1251($val);
			}
			return $s;
		}else{
			/*if (!function_exists('mb_strlen')){
			function mb_strlen($str,$enc="cp1251"){
			//return strlen(iconv("UTF-8",$enc, $str));
			return strlen($str);
			}
			}*/
			$strlen=mb_strlen($s,"cp1251");
			for ($c=0;$c<$strlen;$c++){
				$i=ord($s[$c]);
				if ($i<=127) $out.=$s[$c];
				if ($byte2){
					$new_c2=($c1&3)*64+($i&63);
					$new_c1=($c1>>2)&5;
					$new_i=$new_c1*256+$new_c2;
					if ($new_i==1025){
						$out_i=168;
					} else {
						if ($new_i==1105){
							$out_i=184;
						} else {
							$out_i=$new_i-848;
						}
					}
					$out.=chr($out_i);
					$byte2=false;
				}
				if (($i>>5)==6) {
					$c1=$i;
					$byte2=true;
				}
			}
			return $out;
		}
	}

	/**
	* переводит текстовую строку $txt из cp1251 кодировки в utf8 и возвращает полученную строку
	*
	* @param mixed $txt
	*/
	static function cp1251_to_utf8 ($txt)  {
		$in_arr = array (
			chr(208), chr(192), chr(193), chr(194),
			chr(195), chr(196), chr(197), chr(168),
			chr(198), chr(199), chr(200), chr(201),
			chr(202), chr(203), chr(204), chr(205),
			chr(206), chr(207), chr(209), chr(210),
			chr(211), chr(212), chr(213), chr(214),
			chr(215), chr(216), chr(217), chr(218),
			chr(219), chr(220), chr(221), chr(222),
			chr(223), chr(224), chr(225), chr(226),
			chr(227), chr(228), chr(229), chr(184),
			chr(230), chr(231), chr(232), chr(233),
			chr(234), chr(235), chr(236), chr(237),
			chr(238), chr(239), chr(240), chr(241),
			chr(242), chr(243), chr(244), chr(245),
			chr(246), chr(247), chr(248), chr(249),
			chr(250), chr(251), chr(252), chr(253),
			chr(254), chr(255)
		);

		$out_arr = array (
			chr(208).chr(160), chr(208).chr(144), chr(208).chr(145),
			chr(208).chr(146), chr(208).chr(147), chr(208).chr(148),
			chr(208).chr(149), chr(208).chr(129), chr(208).chr(150),
			chr(208).chr(151), chr(208).chr(152), chr(208).chr(153),
			chr(208).chr(154), chr(208).chr(155), chr(208).chr(156),
			chr(208).chr(157), chr(208).chr(158), chr(208).chr(159),
			chr(208).chr(161), chr(208).chr(162), chr(208).chr(163),
			chr(208).chr(164), chr(208).chr(165), chr(208).chr(166),
			chr(208).chr(167), chr(208).chr(168), chr(208).chr(169),
			chr(208).chr(170), chr(208).chr(171), chr(208).chr(172),
			chr(208).chr(173), chr(208).chr(174), chr(208).chr(175),
			chr(208).chr(176), chr(208).chr(177), chr(208).chr(178),
			chr(208).chr(179), chr(208).chr(180), chr(208).chr(181),
			chr(209).chr(145), chr(208).chr(182), chr(208).chr(183),
			chr(208).chr(184), chr(208).chr(185), chr(208).chr(186),
			chr(208).chr(187), chr(208).chr(188), chr(208).chr(189),
			chr(208).chr(190), chr(208).chr(191), chr(209).chr(128),
			chr(209).chr(129), chr(209).chr(130), chr(209).chr(131),
			chr(209).chr(132), chr(209).chr(133), chr(209).chr(134),
			chr(209).chr(135), chr(209).chr(136), chr(209).chr(137),
			chr(209).chr(138), chr(209).chr(139), chr(209).chr(140),
			chr(209).chr(141), chr(209).chr(142), chr(209).chr(143)
		);

		$txt = str_replace($in_arr,$out_arr,$txt);
		return $txt;
	}

	/**
	* удаляет из строки $text html теги и возвращает полученную строку
	*
	* @param mixed $text
	*/
	static function html_del_tags($text){
		$text_s_html = $text;
		$text_s_html = str_replace('&ldquo;', '&quot;', $text_s_html);
		$text_s_html = str_replace('&rdquo;', '&quot;', $text_s_html);
		$text_s_html = str_replace('&laquo;', '&quot;', $text_s_html);
		$text_s_html = str_replace('&raquo;', '&quot;', $text_s_html);
		$text_s_html = str_replace('&quot;', '"', $text_s_html);
		$text_s_html = str_replace('&nbsp;', ' ', $text_s_html);
		$text_s_html = str_replace('&ndash;', '-', $text_s_html);
		$text_s_html=str_replace(array("\r\n", "\r", "\n")," ",$text_s_html);
		$text_s_html=html_entity_decode($text_s_html);
		$text_s_html=strip_tags($text_s_html);
		$text_s_html=preg_replace(" (\{.*?\}“”)", "", $text_s_html);
		$text_s_html = str_replace('  ', ' ', $text_s_html);
		return $text_s_html;
	}

	/**
	* возвращает безопасную строку
	*
	* @param mixed $text
	*/
	static function safe_text($text){
		$text = TEXT::html_del_tags($text);
		$text = htmlspecialchars_decode($text);
		$text = trim($text);
		//$text = htmlspecialchars($text);
		$text = nl2br($text);
		//$text = mysql_real_escape_string($text);
		return $text;
	}

	/**
	* возвращает html код с ссылкой для отправки письма на почтовый ящик $email
	*
	* @param mixed $email
	*/
	static function email_html($email){
		$email = str_replace(array("\n","\r","\t"),'',$email);
		$email = trim($email);
		$ar_email = explode('@',$email);
		if(sizeof($ar_email) > 1){
			ob_start();
			?><a class="email"><?=$ar_email[0];?> <?=$ar_email[1];?></a><?
			$email = ob_get_contents();ob_get_clean();
		}
		return $email;
	}

	/**
	* возвращает html код с ссылкой для вызова абонента по номеру телефона $phone
	* в формате типа +{код страны} ({код оператора}) номер телефона
	*
	* @param mixed $phone
	*/
	static function phone_html($phone){
		$phone = str_replace(array("\n","\r","\t"),'',$phone);
		$phone = trim($phone);
		$ar_phone = explode(')',$phone);
		if(preg_match('~(\+7|8)*[\s]*\(([0-9]{3,5})\)[\s]*(.*)~i',$phone,$full_phone)){
			//echo("<pre>");print_r($full_phone);echo("</pre>");
			$tel = str_replace(array(' ','-','(',')'),'',$phone);
			ob_start();
			?><a href="tel:<?=$tel;?>"><?=$phone;?></a><?
			$phone = ob_get_contents();ob_get_clean();
		}
		return $phone;
	}

	/**
	* преобразует строку $text в html сущности и возвращает результат
	*
	* @param mixed $text
	*/
	static function to_html($text){
		return htmlspecialchars(htmlspecialchars_decode($text));
	}

	/**
	* функция превода текста с кириллицы в траскрипт
	*
	* @param mixed $st
	*/
	static function translit($st){
		// Сначала заменяем "односимвольные" фонемы.
		$st=strtr($st,"абвгдеёзийклмнопрстуфхъыэ_",
			"abvgdeeziyklmnoprstufh'iei");
		$st=strtr($st,"АБВГДЕЁЗИЙКЛМНОПРСТУФХЪЫЭ_",
			"ABVGDEEZIYKLMNOPRSTUFH'IEI");
		// Затем - "многосимвольные".
		$st=strtr($st,
			array(
				"ж"=>"zh", "ц"=>"ts", "ч"=>"ch", "ш"=>"sh",
				"щ"=>"shch","ь"=>"", "ю"=>"yu", "я"=>"ya",
				"Ж"=>"ZH", "Ц"=>"TS", "Ч"=>"CH", "Ш"=>"SH",
				"Щ"=>"SHCH","Ь"=>"", "Ю"=>"YU", "Я"=>"YA",
				"ї"=>"i", "Ї"=>"Yi", "є"=>"ie", "Є"=>"Ye"
			)
		);
		// Возвращаем результат.
		return $st;
	}
}
?>