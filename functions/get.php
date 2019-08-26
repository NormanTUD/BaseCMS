<?php
	// Die get_-Funktionen sollen häßliche Konstrukte mit array_key_exists($bla, $_POST) vermeiden.
	function get_get ($name) {
		if(array_key_exists($name, $_GET)) {
			return $_GET[$name];
		} else {
			return NULL;
		}
	}

	function get_post ($name) {
		if(array_key_exists($name, $_POST)) {
			return $_POST[$name];
		} else {
			return NULL;
		}
	}

	function get_cookie ($name) {
		if(array_key_exists($name, $_COOKIE)) {
			return $_COOKIE[$name];
		} else {
			return NULL;
		}
	}

	function get_post_multiple_check ($names) {
		if(is_array($names)) {
			$return = 1;
			foreach ($names as $name) {
				if(!get_post($name)) {
					$return = 0;
					break;
				}
			}
			return $return;
		} else {
			return get_post($name);
		}
	}

	function get_get_int ($key) {
		$data = get_get($key);
		if(preg_match('/^\d+$/', $data)) {
			return $data;
		} else {
			return '';
		}
	}


?>
