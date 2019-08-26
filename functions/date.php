<?php
	function us_date_to_european_date ($date) {
		if(preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $date, $founds)) {
			return $founds[3].'.'.$founds[2].'.'.$founds[1];
		} else {
			return $date;
		}
	}

	function european_data_to_us_date ($date) {
		if(preg_match('/^\d{4}\.\d{2}\.\d{2}$/', $date)) {
			return $date;
		} else {
			if(preg_match('/^(\d{2})\.(\d{2})\.(\d{4})$/', $date, $founds)) {
				$str = $founds[3].'-'.$founds[2].'-'.$founds[1];
				return $str;
			}
		}
	}
?>
