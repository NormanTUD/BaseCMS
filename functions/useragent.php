<?php
	function update_user_agent_counter () {
		if(isset($GLOBALS['logged_in_user_id'])) {
			return;
		}
		include_once('ua/PHP-UA-parser.php');
		$browser_name = $GLOBALS['parsed_UA']['browser_name'];
		$browser_vers = $GLOBALS['parsed_UA']['browser_vers'];
		$platfrm_name = $GLOBALS['parsed_UA']['platfrm_name'];
		$platfrm_vers = $GLOBALS['parsed_UA']['platfrm_vers'];

		$browser_id = get_and_create_ua_browser($browser_name, $browser_vers);
		$os_id = get_and_create_ua_os($platfrm_name, $platfrm_vers);

		update_ua_call($os_id, $browser_id);
	}

	function update_ua_call($os_id, $browser_id) {
		$year = date("Y");
		$month = date("m");
		$day = date("d");

		$query = 'INSERT INTO `ua_call` (`specific_os_id`, `specific_browser_id`, `c`, `month`, `year`, `day`) VALUES ('.esc($os_id).', '.esc($browser_id).', 1, '.esc($month).', '.esc($year).', '.esc($day).') ON DUPLICATE KEY UPDATE `c` = `c` + 1';
		rquery($query);
	}

	function get_and_create_ua_os ($os_name, $os_vers) {
		$os_id = null;
		$spec_os_id = null;

		if(!$os_vers || $os_vers == "Unknown") {
			$os_vers = "n/a";
		}

		if(!$os_name || $os_name == "Unknown") {
			$os_name = "n/a";
		}

		if(strlen($os_vers) > 100) {
			$os_vers = substr($os_vers, 0, 100);
		}

		if(strlen($os_name) > 100) {
			$os_name = substr($os_name, 0, 100);
		}

		$query = 'SELECT `id` FROM `ua_os` WHERE `name` = '.esc($os_name);
		$result = rquery($query);
		if(mysqli_num_rows($result)) {
			while ($row = mysqli_fetch_row($result)) {
				$os_id = $row[0];
			}

			$query = 'SELECT `id` FROM `ua_specific_os` WHERE `name_id` = '.esc($os_id).' AND `version` = '.esc($os_vers);
			$result = rquery($query);

			if(mysqli_num_rows($result)) {
				while ($row = mysqli_fetch_row($result)) {
					$spec_os_id = $row[0];
				}
			} else {
				$query = 'INSERT INTO `ua_specific_os` (`name_id`, `version`) VALUES ('.esc($os_id).', '.esc($os_vers).')';
				rquery($query);
				return get_and_create_ua_os($os_name, $os_vers);			
			}
		} else {
			$query = 'INSERT INTO `ua_os` (`name`) VALUES ('.esc($os_name).')';
			rquery($query);
			return get_and_create_ua_os($os_name, $os_vers);
		}

		return $spec_os_id;
	}

	function get_and_create_ua_browser ($browser_name, $browser_vers) {
		$browser_id = null;
		$spec_browser_id = null;

		if(!$browser_vers || $browser_vers == "Unknown") {
			$browser_vers = "n/a";
		}

		if(!$browser_name || $browser_name == "Unknown") {
			$browser_name = "n/a";
		}

		if(strlen($browser_name) > 100) {
			$browser_name = substr($browser_name, 0, 100);
		}

		if(strlen($browser_vers) > 100) {
			$browser_vers = substr($browser_vers, 0, 100);
		}

		$query = 'SELECT `id` FROM `ua_browser` WHERE `name` = '.esc($browser_name);
		$result = rquery($query);
		if(mysqli_num_rows($result)) {
			while ($row = mysqli_fetch_row($result)) {
				$browser_id = $row[0];
			}

			$query = 'SELECT `id` FROM `ua_specific_browser` WHERE `name_id` = '.esc($browser_id).' AND `version` = '.esc($browser_vers);
			$result = rquery($query);

			if(mysqli_num_rows($result)) {
				while ($row = mysqli_fetch_row($result)) {
					$spec_browser_id = $row[0];
				}
			} else {
				$query = 'INSERT INTO `ua_specific_browser` (`name_id`, `version`) VALUES ('.esc($browser_id).', '.esc($browser_vers).')';
				rquery($query);
				return get_and_create_ua_browser($browser_name, $browser_vers);			
			}
		} else {
			$query = 'INSERT INTO `ua_browser` (`name`) VALUES ('.esc($browser_name).')';
			rquery($query);
			return get_and_create_ua_browser($browser_name, $browser_vers);
		}

		return $spec_browser_id;
	}

	if(!$GLOBALS['setup_mode']) {
		if(get_post('try_login')) {
			$GLOBALS['logged_in_was_tried'] = 1;
		}

		if(get_cookie('session_id')) {
			delete_old_session_ids();
			$query = 'SELECT `user_id`, `username`, `accepted_public_data` FROM `view_user_session_id` WHERE `session_id` = '.esc($_COOKIE['session_id']).' AND `enabled` = "1"';
			$result = rquery($query);
			while ($row = mysqli_fetch_row($result)) {
				$GLOBALS['logged_in'] = 1;
				$GLOBALS['logged_in_data'] = $row;
				$GLOBALS['logged_in_user_id'] = $row[0];
				$GLOBALS['user_role_id'] = get_role_id_by_user($row[0]);
				$GLOBALS['accepted_public_data'] = $row[2];
			}
		}

		if (!$GLOBALS['logged_in'] && get_post('username') && get_post('password')) {
			delete_old_session_ids();
			$GLOBALS['logged_in_was_tried'] = 1;
			$user = $_POST['username'];
			$possible_user_id = get_user_id($user);
			$salt = get_salt($possible_user_id);
			$pass = hash('sha256', $_POST['password'].$salt);

			$query = 'SELECT `id`, `username`, `accepted_public_data` FROM `users` WHERE `username` = '.esc($user).' AND `password_sha256` = '.esc($pass).' AND `enabled` = "1"';
			$result = rquery($query);
			while ($row = mysqli_fetch_row($result)) {
				delete_old_session_ids($GLOBALS['logged_in_user_id']);
				$GLOBALS['logged_in'] = 1;
				$GLOBALS['logged_in_data'] = $row;
				$GLOBALS['logged_in_user_id'] = $row[0];
				$GLOBALS['user_role_id'] = get_role_id_by_user($row[0]);
				$GLOBALS['accepted_public_data'] = $row[2];

				$session_id = generate_random_string(1024);
				$query = 'INSERT IGNORE INTO `session_ids` (`session_id`, `user_id`) VALUES ('.esc($session_id).', '.esc($row[0]).')';
				rquery($query);

				setcookie('session_id', $session_id, time() + 86400, "/");
			}
		}

		if($GLOBALS['logged_in_user_id'] && basename($_SERVER['SCRIPT_NAME']) == 'admin.php') {
			$query = 'SELECT `name`, `file`, `page_id`, `show_in_navigation`, `parent` FROM `view_account_to_role_pages` WHERE `user_id` = '.esc($GLOBALS['logged_in_user_id']).' ORDER BY `parent`, `name`';
			$result = rquery($query);

			while ($row = mysqli_fetch_row($result)) {
				$GLOBALS['pages'][$row[2]] = $row;
			}

			if(get_get('sdsg_einverstanden')) {
				$query = 'UPDATE `users` SET `accepted_public_data` = "1" WHERE `id` = '.esc($GLOBALS['logged_in_user_id']);
				rquery($query);

				$GLOBALS['accepted_public_data'] = 1;
			}
		}

		if(array_key_exists('REQUEST_URI', $_SERVER) && preg_match('/\/pages\//', $_SERVER['REQUEST_URI'])) {
			$script_name = basename($_SERVER['REQUEST_URI']);
			$page_id = get_page_id_by_filename($script_name);
			if($page_id) {
				$header = 'Location: ../'.$GLOBALS['adminpage'].'?page='.$page_id;
				header($header);
			} else {
				die("Die internen Seiten dürfen nicht direkt aufgerufen werden. Die gesuchte Seite konnte im Index nicht gefunden werden. Nehmen Sie &mdash; statt der direkten URL &mdash; den Weg über das Administrationsmenü.");
			}
		}
	}
?>
