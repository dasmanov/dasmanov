<?php if(!defined('ACCESS_CODE') || intval(ACCESS_CODE+ACCESS_CODE*ACCESS_CODE) != 6) die('Access Error');

	class Date_Time {
		var $date = '';
		var $ar_date = '';
		var $mk_time = '';

		function __construct($date = ''){
			if(!empty($date)){
				$this->date = $date;
				$this->ar_date = $this->ArrayFromDateTime($this->date);
				$this->mk_time = $this->mk_time($this->ar_date);
			}
		}
		/**
		* возвращает количество дней, исключая указанные в массиве дни начиная (0 - вс, 1 - пн, 2 -вт, 3 - ср, 4 - чт, 5 - пт, 6 - сб)
		*
		*/
		function count_days($date_begin,$date_end,$except){
			$date_begin = Date_Time::ArrayFromDateTime($date_begin);
			$date_end = Date_Time::ArrayFromDateTime($date_end);


			$mk_time_cur = mktime($cur_date_time[3], $cur_date_time[4], $cur_date_time[5], $cur_date_time[1], $cur_date_time[2], $cur_date_time[0]);
			$mk_time = mktime($last_date_time[3], $last_date_time[4], $last_date_time[5], $last_date_time[1], $last_date_time[2], $last_date_time[0]);
			///////////////////////////////////////
			// исключает выходные дни воскресенье и субботу
			$arr = getdate($mk_time);
			$last_date_time[0] = $arr['year'];
			$last_date_time[1] = $arr['mon'];
			$last_date_time[2] = $arr['mday'];
			$last_date_time[3] = $arr['hours'];
			$last_date_time[4] = $arr['minutes'];
			$last_date_time[5] = $arr['seconds'];

			$count_days = ($mk_time-$mk_time_cur)/(3600*24);
			$weekends = 0;
			for($i=0;$i<$count_days;$i++){
				$cur_arr = getdate(mktime($cur_date_time[3], $cur_date_time[4], $cur_date_time[5], $cur_date_time[1], ++$cur_date_time[2], $cur_date_time[0]));
				$w_day = $cur_arr['wday'];
				if(in_array($w_day,$except)){
					$i--;
					$weekends++;
				}
			}
			$mk_time = mktime($last_date_time[3], $last_date_time[4], $last_date_time[5], $last_date_time[1], $last_date_time[2]+$weekends, $last_date_time[0]);
			///////////////////////////////////////
			$date = date("Y-m-d H:i:s", $mk_time);

			$form2 = new FormField();
			$form2->id = $form->id;
			$form2->value = $date;
			echo $this->GetElement('label',$form2);

			$form->value = $date;
			$form->type = 'hidden';
			$form->id = '';
			echo $this->GetElement('input',$form);
		}

		/**
		* возвращает дату и время в массиве из стандартной даты
		*
		* @param mixed $date_time
		*/
		function ArrayFromDateTime($date_time){
			$ar_date = explode(' ',$date_time);
			$n_date = explode('-',$ar_date[0]);
			$n_time = explode(':',$ar_date[1]);
			$array = array(
			0 => $n_date[0],
			1 => $n_date[1],
			2 => $n_date[2],
			3 => $n_time[0],
			4 => $n_time[1],
			5 => $n_time[2]
			);
			return $array;
		}

		/**
		* Возвращает метку времени для заданной даты(массив полученный из функции ArrayFromDateTime)
		*
		* @param mixed $ar_date_time
		* @return int
		*/
		function mk_time($ar_date_time){
			return mktime($ar_date_time[3], $ar_date_time[4], $ar_date_time[5], $ar_date_time[1], $ar_date_time[2], $ar_date_time[0]);
		}

		/**
		* Возвращает дату в заданном формате
		*
		* @param mixed $format
		* @param mixed $mk_time
		* @return string
		*/
		function date_format($format,$mk_time=''){
			if(empty($mk_time)){
				$mk_time = $this->mk_time;
			}
			return date($format, $mk_time);
		}

	}

?>
