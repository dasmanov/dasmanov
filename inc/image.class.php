<?php if(!defined('ACCESS_CODE') || intval(ACCESS_CODE+ACCESS_CODE*ACCESS_CODE) != 6) die('Access Error');

class IMAGE {

	### Версия GD ###
	//private $gdselected = 2;
	// GD Function List
	private $gd_function_suffix = array(
		'image/pjpeg' => 'JPEG',
		'image/jpeg'  => 'JPEG',
		'image/gif'   => 'GIF',
		'image/bmp'   => 'WBMP',
		'image/x-png' => 'PNG',
		'image/png'   => 'PNG'
	);
	private $width;
	private $height;
	private $exp;
	private $file_name;
	private $file_type;
	private $angle_rotation;
	private $source_file;

	private $dst_image;
	private $src_image;
	private $dst_x = 0;
	private $dst_y = 0;
	private $src_x = 0;
	private $src_y = 0;
	private $dst_w;
	private $dst_h;
	private $src_w;
	private $src_h;

	private $max_width = 3000;
	private $max_height = 3000;

	private $img_quality = 100;

	private $function_suffix;
	private $function_to_read;
	private $function_to_write;

	private $bg;

	private $watermark;
	//private $sizes = array();

	function __construct(){
		$this->width = $this->max_width;
		$this->height = $this->max_height;
		return false;
	}

	/**
	* добавляет новый размер для изменения размера
	*
	* @param mixed $width
	* @param mixed $height
	*/
	function new_size($width,$height){
		$this->width = $width;
		$this->height = $height;
		//$this->sizes[] = new ImgSize($width,$height);
	}

	/**
	* возвращает расширение файла
	*
	* @param mixed $img
	*/
	function get_exp($img){
		if (empty($img)) {
			return false;
		}else{
			$this->exp=".jpg";
			if (preg_match("/\.gif/i",$img)) {$this->exp=".gif";}
			if (preg_match("/\.png/i",$img)) {$this->exp=".png";}
			if (preg_match("/\.tiff/i",$img)) {$this->exp=".tiff";}
			if (preg_match("/\.jpg/i",$img)) {$this->exp=".jpg";}
			if (preg_match("/\.jpeg/i",$img)) {$this->exp=".jpg";}
			return $this->exp;
		}
	}

	/**
	* создает директоию если не создана
	*
	* @param mixed $uploaddir
	*/
	function upload_dir_isset($uploaddir,$max_level=1){
		$success = true;
		if(!is_dir($uploaddir)){
			$one_dir_up = $this->go_up_dir($uploaddir);
			if(!is_dir($one_dir_up) and $max_level < 3){	// защита, максимум для 3-х уровней вложения
				$this->upload_dir_isset($one_dir_up,++$max_level);
			}
			MODULES::Message("Папка '".$uploaddir."' не существует",'warning');
			if(mkdir($uploaddir, 0755)){
				MODULES::Message("Папка '".$uploaddir."' успешно создана",'success');
			}else{

				$success = false;
				MODULES::Message("Папка '".$uploaddir."' не создана",'error');
			}
		}
		return $success;
	}

	/**
	* возвращает путь директории выше на один уровень
	*
	* @param mixed $folder
	*/
	function go_up_dir($folder){
		$ar_folder = explode('/',$folder);
		$sizeof_ar_folder = sizeof($ar_folder);
		if($sizeof_ar_folder > 2){
			if(empty($ar_folder[$sizeof_ar_folder-1])){
				unset($ar_folder[$sizeof_ar_folder-1]);
				$ar_folder[$sizeof_ar_folder-2] = '';
			}else{
				unset($ar_folder[$sizeof_ar_folder-1]);
			}
			$folder = implode('/',$ar_folder);
			return $folder;
		}
		return false;
	}

	/**
	* Перемещает загруженный файл и выдает сообщение о результате
	*
	* @param mixed $file
	* @param mixed $dest_file_path
	*/
	function MoveUploadedFile($file,$dest_file_path){
		$success = true;
		if (move_uploaded_file($file['tmp_name'], $dest_file_path)) {
			MODULES::Message("Файл картинки загружен успешно",'success');
		}else{
			MODULES::Message("Ошибка загрузки файла картинки, код ошибки: ".$file['error'],'error');
			switch($file['error']){
				case '1':
					MODULES::Message("Размер принятого файла превысил максимально допустимый размер, который задан директивой upload_max_filesize конфигурационного файла php.ini.",'error');
					break;
				case '2':
					MODULES::Message("Размер загружаемого файла превысил значение MAX_FILE_SIZE, указанное в HTML-форме.",'error');
					break;
				case '3':
					MODULES::Message("Загружаемый файл был получен только частично.",'error');
					break;
				case '4':
					MODULES::Message("Файл не был загружен.",'error');
					break;
				case '6':
					MODULES::Message("Отсутствует временная папка.",'error');
					break;
				case '7':
					MODULES::Message("Не удалось записать файл на диск.",'error');
					break;
				case '8':
					MODULES::Message("PHP-расширение остановило загрузку файла. PHP не предоставляет способа определить какое расширение остановило загрузку файла; в этом может помочь просмотр списка загруженных расширений из phpinfo().",'error');
					break;
			}
			$success = false;
		}
		return $success;
	}

	/**
	* устанавливает по MIME типу ($filetype) название функций для проведения дальнейших действий, а так-же качество изображения
	*
	*/
	private function set_function_suffix(){
		$this->function_suffix   = $this->gd_function_suffix[$this->file_type];
		$this->function_to_read  = "ImageCreateFrom".$this->function_suffix;
		$this->function_to_write = "Image".$this->function_suffix;
		$this->img_quality = 100;
		if($this->function_suffix == 'PNG'){
			$this->img_quality = 9;
		}
	}

	/**
	* определяет угол поворота изображения
	*
	*/
	private function set_angle_rotation(){
		$angle_rotation = 0;
		if($this->file_type == 'image/jpeg' or $this->file_type == 'image/tiff'){
			if(!empty($this->source_file)){
				if($exif = exif_read_data($this->source_file, 0, true)){
					if(!empty( $exif['IFD0']['Orientation'])){
						switch( $exif['IFD0']['Orientation'] ) {
							case 3:
								$angle_rotation = 180;
								break;
							case 6:
								$angle_rotation = 270;
								break;
							case 8:
								$angle_rotation = 90;
								break;
						}
					}
				}
			}
		}
		$this->angle_rotation = $angle_rotation;
		return true;
	}

	/**
	* получает параметры изображения и запоминает путь исходного файла
	*
	* @param mixed $sourcefile
	*/
	function get_image_params($sourcefile){
		$this->source_file = $sourcefile;
		if($size = GetImageSize($this->source_file)){
			$this->src_w = $this->width = $size[0];
			$this->src_h = $this->height = $size[1];

			$this->file_type = $size['mime'];
			$this->set_angle_rotation($this->source_file);
			$this->set_function_suffix();
			return true;
		}
		return false;
	}

	/**
	* изменяет размер изображения и сохраняет его под $dest_file
	*
	* @param mixed $dest_file
	*/
	function resize($dest_file){
		if (empty($dest_file) || empty($this->source_file) || empty($this->function_suffix)) return false;
		$function_to_read = $this->function_to_read;
		$this->src_image = $function_to_read($this->source_file);
		// Build Thumbnail with GD2
		// create a blank image for the thumbnail
		$this->dst_image = ImageCreateTrueColor($this->width, $this->height );
		//imagesetinterpolation($dst_image, IMG_MITCHELL);
		$this->bg = imagecolorallocate ( $this->dst_image, 255, 255, 255 );
		imagecolortransparent($this->dst_image, $this->bg);
		imagefill ( $this->dst_image, 0, 0, $this->bg );
		imagealphablending($this->dst_image,true);
		imagesavealpha($this->dst_image, true);
		// resize it
		/*echo '$this->dst_x='.$this->dst_x.'<br>';
		echo '$this->dst_y='.$this->dst_y.'<br>';
		echo '$this->src_x='.$this->src_x.'<br>';
		echo '$this->src_y='.$this->src_y.'<br>';
		echo '$this->dst_w='.$this->dst_w.'<br>';
		echo '$this->dst_h='.$this->dst_h.'<br>';
		echo '$this->src_w='.$this->src_w.'<br>';
		echo '$this->src_h='.$this->src_h.'<br>';
		echo '$this->dst_image='.$this->dst_image.'<br>';
		echo '$this->src_image='.$this->src_image.'<br><br>';*/
		ImageCopyResampled($this->dst_image, $this->src_image, $this->dst_x, $this->dst_y, $this->src_x, $this->src_y, $this->dst_w, $this->dst_h, $this->src_w, $this->src_h);
		// rotate img to normal orientation
		if(!empty($this->angle_rotation)){
			$this->dst_image = imagerotate($this->dst_image,$this->angle_rotation,$this->bg);
		}

		if(!empty($this->watermark)){ // set watermark
			$function_suffix_watermark = $this->gd_function_suffix[$this->watermark['type']];
			$function_to_read_watermark = "ImageCreateFrom".$function_suffix_watermark;
			$watermark = $function_to_read_watermark($this->watermark['src']);
			$this->filter_opacity($watermark, ($this->watermark['opacity']*100));

			imagealphablending($watermark,true);
			imagesavealpha($watermark, true);

			$dest_x = 0;
			$dest_y = 0;

			if(!empty($this->watermark['right'])){
				$dest_x = $this->width - $this->watermark['new_width'] - $this->watermark['right'];
			}elseif(!empty($this->watermark['left'])){
				$dest_x = $this->watermark['left'];
			}

			if(!empty($this->watermark['bottom'])){
				$dest_y = $this->height - $this->watermark['new_height'] - $this->watermark['bottom'];
			}elseif(!empty($this->watermark['top'])){
				$dest_y = $this->watermark['top'];
			}

			if(!empty($this->watermark['position'])){
				$watermark_position = $this->watermark['position'];
				if(!empty($watermark_position['horizontal'])){
					$watermark_position_horizontal = $watermark_position['horizontal'];
					if(empty($watermark_position_horizontal['offset'])){
						$watermark_position_horizontal['offset'] = 0;
					}
					if(!empty($watermark_position_horizontal['align'])){
						switch($watermark_position_horizontal['align']){
							case 'left':
								$dest_x = $watermark_position_horizontal['offset'];
								break;
							case 'right':
								$dest_x = $this->width - $this->watermark['new_width'] - $watermark_position_horizontal['offset'];
								break;
							case 'center':
								$dest_x = round(($this->width - $this->watermark['new_width'])/2) + $watermark_position_horizontal['offset'];
								break;
						}
					}
				}
				if(!empty($watermark_position['vertical'])){
					$watermark_position_vertical = $watermark_position['vertical'];
					if(empty($watermark_position_vertical['offset'])){
						$watermark_position_vertical['offset'] = 0;
					}
					if(!empty($watermark_position_vertical['align'])){
						switch($watermark_position_vertical['align']){
							case 'top':
								$dest_y = $watermark_position_vertical['offset'];
								break;
							case 'bottom':
								$dest_y = $this->height - $this->watermark['new_height'] - $watermark_position_vertical['offset'];
								break;
							case 'center':
								$dest_y = round(($this->height - $this->watermark['new_height'])/2) + $watermark_position_vertical['offset'];
								break;
						}
					}
				}
			}
			ImageCopyResampled($this->dst_image, $watermark, $dest_x, $dest_y, 0, 0, $this->watermark['new_width'], $this->watermark['new_height'], $this->watermark['width'], $this->watermark['height']);
			$this->watermark = null;
		}

		$function_to_write = $this->function_to_write;
		$function_to_write($this->dst_image, $dest_file, $this->img_quality);
		ImageDestroy($this->dst_image);
		if(!empty($watermark)){
			ImageDestroy($watermark);
		}
	}

	/**
	* устанавливает прозрачность
	*
	* @param mixed $img
	* @param mixed $opacity
	*/
	function filter_opacity(&$img, $opacity){
		if (!isset($opacity)) {
			return false;
		}
		$opacity /= 100;

		//get image width and height
		$w = imagesx($img);
		$h = imagesy($img);

		//turn alpha blending off
		imagealphablending($img, false);

		//find the most opaque pixel in the image (the one with the smallest alpha value)
		$minalpha = 127;
		for ($x = 0; $x < $w; $x++) {
			for ($y = 0; $y < $h; $y++) {
				$alpha = (imagecolorat($img, $x, $y) >> 24) & 0xFF;
				if ($alpha < $minalpha) {
					$minalpha = $alpha;
				}
			}
		}

		//loop through image pixels and modify alpha for each
		for ($x = 0; $x < $w; $x++) {
			for ($y = 0; $y < $h; $y++) {
				//get current alpha value (represents the TANSPARENCY!)
				$colorxy = imagecolorat($img, $x, $y);
				$alpha = ($colorxy >> 24) & 0xFF;
				//calculate new alpha
				if ($minalpha !== 127) {
					$alpha = 127 + 127 * $opacity * ($alpha - 127) / (127 - $minalpha);
				} else {
					$alpha += 127 * $opacity;
				}
				//get the color index with new alpha
				$alphacolorxy = imagecolorallocatealpha($img, ($colorxy >> 16) & 0xFF, ($colorxy >> 8) & 0xFF, $colorxy & 0xFF, $alpha);
				//set pixel with the new color + opacity
				if (!imagesetpixel($img, $x, $y, $alphacolorxy)) {
					return false;
				}
			}
		}

		return true;
	}

	/**
	* задает размеры нового изображения
	*
	* @param mixed $resize_mode
	* @param mixed $zoom
	*/
	function set_scale_size($resize_mode,$zoom='out'){
		if(!empty($resize_mode)){
			switch($resize_mode){
				case 'adapt':
					if($this->width == 0 or $this->height == 0){
						if($this->height == 0){
							$this->height = round($this->src_h*$this->width/$this->src_w);
						}else{
							$this->width = round($this->src_w*$this->height/$this->src_h);
						}
					}

					$this->dst_w = $this->width;
					$this->dst_h = round($this->dst_w * $this->src_h / $this->src_w);
					if($this->dst_h < $this->height){
						$this->dst_h = $this->height;
						$this->dst_w = round($this->dst_h * $this->src_w / $this->src_h);
					}
					$this->dst_x = round(($this->width - $this->dst_w) / 2);
					$this->dst_y = round(($this->height - $this->dst_h) / 2);

					$this->src_x = 0;
					$this->src_y = 0;
					break;
				case 'fit':
					if($this->dst_w < $this->width or $this->dst_h > $this->height){
						$this->dst_h = $this->height;
						$this->dst_w = (int)($this->dst_h * $this->src_w / $this->src_h);
					}
					if($this->dst_w > $this->width or $this->dst_h < $this->height){
						$this->dst_w = $this->width;
						$this->dst_h = (int)($this->dst_w * $this->src_h / $this->src_w);
					}
					/*if($zoom == 'out'){
					if($this->src_h < $this->dst_h or $this->src_w < $this->dst_w){
					$this->dst_w = $this->src_w;
					$this->dst_h = $this->src_h;
					}
					}*/
					//$this->width = $this->dst_w;
					//$this->height = $this->dst_h;
					$this->dst_x = round(($this->width-$this->dst_w)/2);
					$this->dst_y = round(($this->height-$this->dst_h)/2);
					$this->src_x = 0;
					$this->src_y = 0;
					break;
				case 'fit_width':
					if($this->src_w > $this->dst_w){
						$this->dst_w = $this->width;
						$this->dst_h = (int)($this->width * $this->src_h / $this->src_w);
					}else{
						$this->width = $this->dst_w = $this->src_w;
						$this->height = $this->dst_h = $this->src_h;
					}
					break;
				case 'fit_height':
					break;
				case 'repeat':
					break;
				case 'center':
					break;

				case 'proportional':
					if($this->dst_w < $this->width or $this->dst_h > $this->height){
						$this->dst_h = $this->height;
						$this->dst_w = (int)($this->dst_h * $this->src_w / $this->src_h);
					}
					if($this->dst_w > $this->width or $this->dst_h < $this->height){
						$this->dst_w = $this->width;
						$this->dst_h = (int)($this->dst_w * $this->src_h / $this->src_w);
					}
					if($this->src_h < $this->dst_h or $this->src_w < $this->dst_w){
						$this->dst_w = $this->src_w;
						$this->dst_h = $this->src_h;
					}
					$this->width = $this->dst_w;
					$this->height = $this->dst_h;
					$this->dst_x = 0;
					$this->dst_y = 0;
					$this->src_x = 0;
					$this->src_y = 0;
					break;
			}
			return true;
		}
		return false;
	}

	/**
	* проверяет файл на существование
	*
	* @param mixed $file_path
	*/
	static function exist($file_path){
		if(!empty($file_path)){
			if($file_path[0] == '/'){
				$file_path = DOCROOT.substr($file_path,1);
			}else{
				$file_path = DOCROOT.$file_path;
			}
			if(file_exists($file_path)){
				return true;
			}
		}
		return false;
	}

	/**
	* Проверяет существование файла по урл
	*
	* @param mixed $file_path
	*/
	static function exist_url($file_path){
		if(!empty($file_path)){
			$file_headers = @get_headers($file_path);
			if(preg_match('~200~isu',$file_headers[0])){
				return true;
			}
		}
		return false;
	}

	/**
	* добавляет водяной знак
	*
	* @param mixed $array
	*/
	function add_watermark($array){
		$this->watermark = $array;
	}

	/**
	* возвращает ширину изображения
	*
	*/
	function get_width(){
		return $this->width;
	}

	/**
	* возвращает высоту изображения
	*
	*/
	function get_height(){
		return $this->height;
	}

	/**
	* возвращает тип изображения
	*
	*/
	function get_file_type(){
		return $this->file_type;
	}
}

?>