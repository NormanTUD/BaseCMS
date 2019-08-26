<?php
	function print_subnavigation ($parent) {
		$query = 'SELECT `name`, `file`, `page_id`, `show_in_navigation`, `parent` FROM `view_account_to_role_pages` WHERE `user_id` = '.esc($GLOBALS['logged_in_user_id']).' AND `parent` = '.esc($parent).' AND `show_in_navigation` = "1" ORDER BY `name`';
		$result = rquery($query);

		$str = '';
		$subnav_selected = 0;

		if(mysqli_num_rows($result)) {
			$str .= "\t<ul>\n";
			while ($row = mysqli_fetch_row($result)) {
				if($row[2] == get_get('page')) {
					$str .= "\t\t<li style='font-weight: bold;'><a href='admin.php?page=".$row[2]."'>&rarr; $row[0]</a></li>\n";
					$subnav_selected = 1;
				} else {
					$str .= "\t\t<li><a href='admin.php?page=".$row[2]."'>$row[0]</a></li>\n";
				}
			}
			$str .= "\t</ul>\n";
		}

		return array($subnav_selected, $str);
	}

	function create_page_info_parent ($parent, $user_role_id_data = null) {
		$page_infos = array();
		$query = 'SELECT `p`.`id`, `p`.`name`, `p`.`file`, `pi`.`info`, `p`.`parent` FROM `page` `p` LEFT JOIN `page_info` `pi` ON `pi`.`page_id` = `p`.`id` WHERE `p`.`show_in_navigation` = "1" AND `parent` = '.esc($parent);
		if(isset($user_role_id_data)) {
			$query .= ' AND `p`.`id` IN (SELECT `page_id` FROM `role_to_page` WHERE `role_id` = '.esc($user_role_id_data).')';
		}
		$query .= ' ORDER BY p.name';
		$result = rquery($query);
		while ($row = mysqli_fetch_row($result)) {
			$page_infos[$row[0]] = array($row[0], $row[1], $row[2], $row[3], $row[4]);
		}
		return $page_infos;
	}

	function get_father_page ($id) {
		$query = 'SELECT `parent` FROM `page` WHERE `id` = '.esc($id);
		$result = rquery($query);

		if(mysqli_num_rows($result)) {
			$father = null;
			while ($row = mysqli_fetch_row($result)) {
				$father = $row[0];
			}
			return $father;
		} else {
			return null;
		}
	}

	function create_page_info () {
		$page_infos = array();
		$query = 'select p.id, p.name, p.file, pi.info, p.parent from page p left join page_info pi on pi.page_id = p.id where p.show_in_navigation = "1" ORDER BY p.name';
		$result = rquery($query);
		while ($row = mysqli_fetch_row($result)) {
			$page_infos[$row[0]] = array($row[0], $row[1], $row[2], $row[3], $row[4]);
		}
		return $page_infos;
	}


	function create_seiten_array () {
		$seiten = array();
		$query = 'SELECT `id`, `name`, `file` FROM `page`';
		$result = rquery($query);
		while ($row = mysqli_fetch_row($result)) {
			$seiten[$row[0]] = array($row[0], $row[1], $row[2]);
		}
		return $seiten;
	}

	function get_page_file_by_id ($id) {
		$key = "get_page_file_by_id($id)";
		if(array_key_exists($key, $GLOBALS['memoize'])) {
			return $GLOBALS['memoize'][$key];
		}

		$query = 'SELECT `file` FROM `page` WHERE `id` = '.esc($id);
		$result = rquery($query);

		$id = NULL;

		while ($row = mysqli_fetch_row($result)) {
			$id = $row[0];
		}

		$GLOBALS['memoize'][$key] = $id;

		return $id;
	}

	function get_page_info_by_id ($id) {
		$query = 'SELECT `page_id`, `info` FROM `page_info` WHERE `page_id` ';
		if(is_array($id)) {
			$query .= 'IN ('.join(', ', array_map('esc', $id)).')';
		} else {
			$query .= ' = '.esc($id);
		}
		$result = rquery($query);

		$data = array();

		while ($row = mysqli_fetch_row($result)) {
			if(is_array($id)) {
				$data[$row[0]] = $row[1];
			} else {
				$data = $row[1];
			}
		}

		return $data;
	}

	function get_page_name_by_id ($id) {
		$query = 'SELECT `name` FROM `page` WHERE `id` = '.esc($id);
		$result = rquery($query);

		$id = NULL;

		while ($row = mysqli_fetch_row($result)) {
			$id = $row[0];
		}

		return $id;
	}

	function create_page_id_by_name_array () {
		$query = 'SELECT `name`, `id` FROM `page`';
		$result = rquery($query);

		$id = array();

		while ($row = mysqli_fetch_row($result)) {
			$id[$row[1]] = $row[0];
		}

		return $id;
	}

	
	function get_page_id_by_filename ($file) {
		if(is_null($file) || !$file) {
			return null;
		}

		$key = "get_page_id_by_filename($file)";
		if(array_key_exists($key, $GLOBALS['memoize'])) {
			return $GLOBALS['memoize'][$key];
		}

		$return = null;

		// Falls $file = aktuelle Seite, dann einfach &page=... zurückgeben
		if(get_get('page') && get_page_file_by_id(get_get('page')) == $file) {
			$return = get_get('page');
		} else {
			$query = 'SELECT `id` FROM `page` WHERE `file` = '.esc($file);
			$result = rquery($query);

			$return = '';

			while ($row = mysqli_fetch_row($result)) {
				$return = $row[0];
			}
		}

		$GLOBALS['memoize'][$key] = $return;

		return $return;
	}

	function get_seitentext () {
		$tpnr = '';
		if(array_key_exists('this_page_number', $GLOBALS) && !is_null($GLOBALS['this_page_number'])) {
			$tpnr = $GLOBALS['this_page_number'];
		} else {
			$tpnr = get_page_id_by_filename('welcome.php');
		}

		$query = 'SELECT `text` FROM `seitentext` WHERE `page_id` = '.esc($tpnr);
		$result = rquery($query);

		$id = NULL;

		while ($row = mysqli_fetch_row($result)) {
			if($row[0]) {
				$id = $row[0];
			}
		}

		return $id;
	}

	function create_page_parent_array () {
		$rollen = array();
		$query = 'SELECT `id`, `name` FROM `page` WHERE `parent` IS NULL AND `file` IS NULL';
		$result = rquery($query);
		while ($row = mysqli_fetch_row($result)) {
			$rollen[$row[0]] = array($row[0], $row[1]);
		}
		return $rollen;
	}

?>
