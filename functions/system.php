<?php
	function stderrw ($str) {
		trigger_error($str, E_USER_WARNING);
	}

	function green_text ($str) {
	    return "\033[32m ".$str."\033[0m ";
	}

	function red_text ($str) {
	    return "\033[31m ".$str."\033[0m ";
	}

	function print_debug ($str) {
		print green_text($str);
	}

	// http://php.net/manual/de/function.is-writable.php#118667
	function is_writable_r($dir) {
		if (is_dir($dir)) {
			if(is_writable($dir)){
				$objects = scandir($dir);
				foreach ($objects as $object) {
					if ($object != "." && $object != "..") {
						if (!is_writable_r($dir."/".$object)) return false;
						else continue;
					}
				}   
				return true;   
			} else {
				return false;
			}

		} else if (file_exists($dir)){
			return (is_writable($dir));

		}
	}

	function dier ($data, $enable_html = 0) {
		$source_data = debug_backtrace()[0];
		@$source = 'Aufgerufen von <b>'.debug_backtrace()[1]['file'].'</b>::<i>'.debug_backtrace()[1]['function'].'</i>, line '.htmlentities($source_data['line'])."<br />\n";
		if($GLOBALS['logged_in_user_id']) {
			print $source;
		}
		print "<pre>\n";
		ob_start();
		print_r($data);
		$buffer = ob_get_clean();
		if($enable_html) {
			print $buffer;
		} else {
			print htmlentities($buffer);
		}
		print "</pre>\n";
		if($GLOBALS['logged_in_user_id']) {
			print "Backtrace:\n";
			print "<pre>\n";
			foreach (debug_backtrace() as $trace) {
					print htmlentities(sprintf("\n%s:%s %s", $trace['file'], $trace['line'], $trace['function']));
			}
			print "</pre>\n";
		}
		include("footer.php");
		exit();
	}


?>
