<?php
	if(!isset($GLOBALS['setup_mode'])) {
		$GLOBALS['setup_mode'] = 0;
	}

	if(file_exists('new_setup')) {
		$GLOBALS['setup_mode'] = 1;
	}

	if(!$GLOBALS['setup_mode']) {
		rquery('SELECT @@FOREIGN_KEY_CHECKS');
		rquery('SET FOREIGN_KEY_CHECKS=1');
	} else {
		rquery('CREATE DATABASE IF NOT EXISTS `'.$GLOBALS['dbname'].'`');
	}

	function insert_values ($database, $columns, $data, $print = 0) {
		if($database) {
			if(is_array($columns)) {
				if(is_array($data)) {
					stderrw("Befülle `$database`\n");
					$query = 'DELETE FROM `'.$database.'`'."\n";
					if($print) {
						print green_text($query);
					}
					rquery($query);
					$base_query = 'INSERT IGNORE INTO `'.$database.'` ('.join(', ', $columns).') VALUES (';
					foreach ($data as $this_data) {
						$query = $base_query;

						if(is_array($this_data)) {
							foreach ($this_data as $this_data_key => $this_data_data) {
								$query .= esc($this_data_key).', '.esc($this_data_data);
							}

							$query = preg_replace('/,\s*$/', '', $query);
						} else {
							$query .= esc($this_data);
						}
						$query .= ')';
						$result = rquery($query);
						if($print) {
							print green_text($query)."\n";
							if($result) {
								print green_text("Ok\n");
							} else {
								print red_text("Warning\n");
							}
						}
					}
				} else {
					die("\$data muss ein Array sein! ($database)");
				}
			} else {
				die("\$columns muss ein Array sein! ($database)");
			}
		} else {
			die("Datenbank muss definiert werden!");
		}
	}

	if($GLOBALS['setup_mode']) {
		if(get_post('import_datenbank')) {
			rquery('USE `'.$GLOBALS['dbname'].'`');
			if(array_key_exists('sql_file', $_FILES) && array_key_exists('tmp_name', $_FILES['sql_file'])) {
				SplitSQL($_FILES['sql_file']['tmp_name']);
			}
		}
	}
?>
