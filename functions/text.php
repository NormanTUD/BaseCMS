<?php
	function htmle ($str, $shy = 0) {
		if($shy) {
			if($str) {
				$str = htmlentities($str);
				return $str;
			} else {
				return '&mdash;';
			}
		} else {
			if($str) {
				return htmlentities($str);
			} else {
				return '&mdash;';
			}
		}
	}

	function fill_front_zeroes ($str, $len, $pre = '0') {
		while (strlen($str) < $len) {
			$str = "$pre$str";
		}
		return $str;
	}

	function replace_newlines_htmlentities ($str) {
		$str = preg_replace("/[\n\r]/", "<br>", $str);

		return htmlentities($str);
	}

	function generate_random_string ($length = 50) {
		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$charactersLength = strlen($characters);
		$randomString = '';
		for ($i = 0; $i < $length; $i++) {
			$randomString .= $characters[mt_rand(0, $charactersLength - 1)];
		}
		return $randomString;
	}

	function sanitize_data ($data, $recursion = 0) {
		if($recursion == 300) {
			die("ERROR: Deep-Recursion! Bitte melden Sie dies dem Administrator.");
		}

		if(is_array($data)) {
			foreach ($data as $te => $val) {
				$data[$te] = sanitize_data($val, $recursion + 1);
			}

			return $data;
		} else {
			return htmlentities($data);
		}
	}

?>
