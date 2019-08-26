<?php
	function error ($message) {
		add_to_output("error", $message);
	}

	function hint ($message) {
		add_to_output("hint", $message);
		show_output("hint", $message);
	}

	function success ($message) {
		add_to_output("success", $message);
	}

	function warning ($message) {
		add_to_output("warning", $message);
	}

	function debug ($message) {
		add_to_output("debug", $message);
	}

	function right_issue ($message) {
		add_to_output("right_issue", $message);
	}

	function message ($message) {
		add_to_output("message", $message);
	}

	function add_to_output ($name, $msg) {
		if($name) {
			if($msg) {
				$GLOBALS[$name][] = $msg;
			}
		} else {
			die(htmlentities($name)." existiert nicht!");
		}
	}

	function print_hinweis_for_page ($chosen_page){
		$hinweis = get_hinweis_for_page($chosen_page);
		if($hinweis) {
			hint("Hinweis: <span class='blue_text'>".htmlentities($hinweis)."</span>");
			#print "<span style='color: blue;'><i>Hinweis: </i> ".htmlentities($hinweis)."<br /><br /></span>";
		}
	}

	function get_hinweis_for_page ($chosen_page) {
		$query = 'SELECT `hinweis` FROM `hinweise` WHERE `page_id` = '.esc($chosen_page);
		$result = rquery($query);
		$hinweis = '';
		while ($row = mysqli_fetch_row($result)) {
			if(strlen($row[0]) && !preg_match('/^\s*$/', $row[0])) {
				$hinweis = $row[0];
			}
		}
		return $hinweis;
	}

	function show_output ($name, $color) {
		if(global_exists($name)) {
			print "<div class='square'>\n";
			print "<div class='one'>\n";
			if(file_exists("./i/$name.svg")) {
				print "<img height='60' src='./i/$name.svg' />\n";
			}
			print "</div>\n";
			print "<div class='two'>\n";
			$this_output = $GLOBALS[$name];
			if(is_array($this_output)) {
				$this_output = array_unique($this_output);
			} else {
				$this_output = array($this_output);
			}
			if($color) {
				if(count($this_output) > 1) {
					print "<ul>\n";
				}
				foreach ($this_output as $this_output_item) {
					#print "<span class='class_$color'><h2>$name: ".$this_output_item."</h2></span>\n";
					if(count($this_output) > 1) {
						print "<li>\n";
					}
					print "<span class='message_text'>".$this_output_item."</span>\n";
					if(count($this_output) > 1) {
						print "</li>\n";
					}
				}
				if(count($this_output) > 1) {
					print "</ul>\n";
				}
			}
			print "</div>\n";
			print "</div>\n";
			print "<div class='clear_both' /><br />\n";
		}
	}

?>
