<?php
	// http://stackoverflow.com/questions/13646690/how-to-get-real-ip-from-visitor

	rquery("CREATE DATABASE IF NOT EXISTS `".$GLOBALS['dbname']."`");
	rquery('USE `'.$GLOBALS['dbname'].'`');
	rquery("SET NAMES utf8");

	function compare_db ($file, $session_ids = 0) {
		if(!check_function_rights(__FUNCTION__)) { return; }
		if(file_exists($file)) {
			$skip = array();
			if(!$session_ids) {
				$skip = array('session_ids');
			}
			$now = backup_tables('*', $skip);
			$then = file_get_contents($file);

			if(strlen($then)) {
				require_once dirname(__FILE__).'/Classes/Diff.php';

				$a = explode("\n", $then);
				$b = explode("\n", $now);

				$options = array();

				$diff = new Diff($a, $b, $options);
				require_once dirname(__FILE__).'/Classes/Diff/Renderer/Html/SideBySide.php';
				$renderer = new Diff_Renderer_Html_SideBySide;
				$tdiff = $diff->Render($renderer);
				if($tdiff) {
					return $tdiff;
				} else {
					error('Das Diff konnte nicht erzeugt werden oder war leer. ');
				}
			} else if (!$now) {
				error('Das Image der aktuellen Datenbank konnte nicht erstellt werden. ');
			} else {
				error('Die Vergleichsdatei darf nicht leer sein. ');
			}
		} else {
			error('Die Datei konnte nach dem Hochladen nicht gefunden werden. Bitte die Apache-Konfiguration überprüfen! ');
		}
	}

	// https://stackoverflow.com/questions/1883079/best-practice-import-mysql-file-in-php-split-queries
	function SplitSQL($file, $delimiter = ';') {
		if(!$GLOBALS['setup_mode']) {
			if(!check_function_rights(__FUNCTION__)) { return; }
		}

		$GLOBALS['slurped_sql_file'] = 1;
		set_time_limit(0);

		if (is_file($file) === true) {
			$file = fopen($file, 'r');
			$GLOBALS['install_counter'] = 1;

			if (is_resource($file) === true) {
				$query = array();

				while (feof($file) === false) {
					$query[] = fgets($file);

					if(preg_match('~' . preg_quote($delimiter, '~') . '\s*$~iS', end($query)) === 1) {
						$query = trim(implode('', $query));

						stderrw(">>> ".($GLOBALS['install_counter']++).": $query\n");

						if (rquery($query) === false) {
							print '<h3>ERROR: '.htmlentities($query).'</h3>'."\n";
						}

						while (ob_get_level() > 0) {
							ob_end_flush();
						}

						flush();
					}

					if (is_string($query) === true) {
						$query = array();
					}
				}

				return fclose($file);
			}
		}

		return false;
	}

	function table_exists ($db, $table) {
		$query = "SELECT table_name FROM information_schema.tables WHERE table_schema = ".esc($db)." AND table_name = ".esc($table);
		$result = mysqli_query($GLOBALS['dbh'], $query);
		$table_exists = 0;
		while ($row = mysqli_fetch_row($result)) {
			$table_exists = 1;
		}
		return $table_exists;
	}

	/* https://davidwalsh.name/backup-mysql-database-php */
	function backup_tables ($tables = '*', $skip = null) {
		if(!$GLOBALS['setup_mode']) {
			if(!check_function_rights(__FUNCTION__)) { return; }
		}

		rquery('USE `'.$GLOBALS['dbname'].'`');
		//get all of the tables
		if($tables == '*') {
			$tables = array();
			$result = rquery('SHOW TABLES');
			while($row = mysqli_fetch_row($result)) {
				if(!((is_array($skip) && array_search($row[0], $skip)) || (!is_array($skip) && $row[0] == $skip))) {
					$tables[] = $row[0];
				}
			}
		} else {
			$tables = is_array($tables) ? $tables : explode(',', $tables);
		}
		
		$return = "SET FOREIGN_KEY_CHECKS=0;\n";
		$return .= "DROP DATABASE `".$GLOBALS['dbname']."`;\n";
		$return .= "CREATE DATABASE IF NOT EXISTS `".$GLOBALS['dbname']."`;\n";
		$return .= "USE `".$GLOBALS['dbname']."`;\n";

		foreach(sort_tables($tables) as $table) {
			$result = rquery('SELECT * FROM '.$table);
			$num_fields = mysqli_field_count($GLOBALS['dbh']);

			$this_return = '';
			
			$row2 = mysqli_fetch_row(rquery('SHOW CREATE TABLE '.$table));
			$row2 = preg_replace('/CHARSET=latin1/', 'CHARSET=utf8', $row2);
			if(preg_match('/^CREATE TABLE/i', $row2[1])) {
				$this_return .= 'DROP TABLE IF EXISTS '.$table.';';
			} else {
				$this_return .= 'DROP VIEW IF EXISTS '.$table.';';
			}

			$this_return.= "\n\n".$row2[1].";\n\n";

			if(preg_match('/^CREATE TABLE/i', $row2[1])) {
				for ($i = 0; $i < $num_fields; $i++) {
					while($row = mysqli_fetch_row($result)) {
						$this_return.= 'INSERT INTO `'.$table.'` VALUES(';
						for($j=0; $j < $num_fields; $j++) {
							$row[$j] = esc($row[$j]);
							if (isset($row[$j])) {
								$this_return .= $row[$j];
							} else {
								$this_return .= 'NULL';
							}
							if ($j < ($num_fields - 1)) {
								$this_return .= ', ';
							}
						}
						$this_return .= ");\n";
					}
				}
			}

			$return .= "$this_return\n";
		}
		
		$return .= "\n\n\nSET FOREIGN_KEY_CHECKS=1;\n";
		return $return;
	}

	function sort_tables ($tables) {
		$create_views = array();
		$create_tables = array();

		foreach ($tables as $table) {
			if(preg_match('/^view_|^ua_overview$/', $table)) {
				$create_views[] = $table;
			} else {
				$create_tables[] = $table;
			}
		}

		$tables_sorted_tmp = array();

		foreach ($create_tables as $table) {
			$foreign_keys = get_foreign_key_tables($GLOBALS['dbname'], $table);
			$foreign_keys_counter = 0;
			if(array_key_exists(0, $foreign_keys)) {
				$foreign_keys_counter = count($foreign_keys[0]);
			}
			$tables_sorted_tmp[] = array('name' => $table, 'foreign_keys_counter' => $foreign_keys_counter);
		}

		usort($tables_sorted_tmp, 'foreignKeyAscSort');

		foreach ($tables_sorted_tmp as $table) {
			$tables_sorted[] = $table['name'];
		}

		foreach ($create_views as $view) {
			$tables_sorted[] = $view;
		}

		return $tables_sorted;
	}

	function foreignKeyAscSort($item1, $item2) {
		if ($item1['foreign_keys_counter'] == $item2['foreign_keys_counter']) {
			return 0;
		} else {
		        return ($item1['foreign_keys_counter'] < $item2['foreign_keys_counter']) ? -1 : 1;
		}
	}

	function get_referencing_foreign_keys ($database, $table) {
		$query = 'SELECT TABLE_SCHEMA, TABLE_NAME, COLUMN_NAME, REFERENCED_COLUMN_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE REFERENCED_TABLE_SCHEMA = "'.$database.'" AND REFERENCED_TABLE_NAME = '.esc($table);
		$result = rquery($query);
		$foreign_keys = array();
		while ($row = mysqli_fetch_row($result)) {
			$foreign_keys[] = array('database' => $row[0], 'table' => $row[1], 'column' => $row[2], 'reference_column' => $row[3]);
		}

		return $foreign_keys;
	}

	function get_foreign_key_deleted_data_html ($database, $table, $where) {
		$data = get_foreign_key_deleted_data ($database, $table, $where);

		$html = '';
		$j = 0;
		foreach ($data as $key => $this_data) {
			$html .= "<h2>$key</h2>\n";

			$html .= "<table>\n";
			$i = 0;
			foreach ($this_data as $value) {
				if($i == 0) {
					$html .= "\t<tr>\n";
					foreach ($value as $column => $column_value) {
						$html .= "\t\t<th>".htmlentities($column)."</th>\n";
					}
					$html .= "\t</tr>\n";
				}
				$html .= "\t<tr>\n";
				foreach ($value as $column => $column_value) {
					if(preg_match('/password|session_id|salt/', $column)) {
						$html .= "\t\t<td><i>Aus Sicherheitsgründen wird diese Spalte nicht angezeigt.</i></td>\n";
					} else {
						if($column_value) {
							$html .= "\t\t<td>".htmlentities($column_value)."</td>\n";
						} else {
							$html .= "\t\t<td><i style='color: orange;'>NULL</i></td>\n";
						}
					}
				}
				$html .= "\t</tr>\n";
				$i++;
			}
			$html .= "</table>\n";

			if($i == 1) {
				$html .= "<h3>$i Zeile</h3><br />\n";
			} else {
				$html .= "<h3>$i Zeilen</h3><br />\n";
			}
			$j += $i;
		}

		$html .= "<h4>Insgesamt $j Datensätze</h4>\n";

		return $html;
	}

	function get_primary_keys ($database, $table) {
		$query = "SELECT k.column_name FROM information_schema.table_constraints t JOIN information_schema.key_column_usage k USING(constraint_name,table_schema,table_name) WHERE t.constraint_type='PRIMARY KEY' AND t.table_schema = ".esc($GLOBALS['dbname'])."   AND t.table_name = ".esc($table);
		$result = rquery($query);

		$data = array();

		while ($row = mysqli_fetch_row($result)) {
			$data[] = $row;
		}

		return $data;
	}

	function get_foreign_key_tables ($database, $table) {
		$query = "SELECT TABLE_NAME, COLUMN_NAME, ' -> ', REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE REFERENCED_COLUMN_NAME IS NOT NULL AND CONSTRAINT_SCHEMA = ".esc($database)." AND TABLE_NAME = ".esc($table);
		$result = rquery($query);

		$data = array();

		while ($row = mysqli_fetch_row($result)) {
			$data[] = $row;
		}

		return $data;
	}

	function get_foreign_key_deleted_data ($database, $table, $where) {
		$GLOBALS['get_data_that_would_be_deleted'] = array();
		$data = get_data_that_would_be_deleted($database, $table, $where);
		$GLOBALS['get_data_that_would_be_deleted'] = array();
		return $data;
	}

	function get_data_that_would_be_deleted ($database, $table, $where, $recursion = 100) {
		if($recursion <= 0) {
			error("get_data_that_would_be_deleted: Tiefenrekursionsfehler. ");
			return;
		}

		if($recursion == 100) {
			$GLOBALS['get_data_that_would_be_deleted'] = array();
		}

		if($table) {
			if(preg_match('/^[a-z0-9A-Z_]+$/', $table)) {
				if(is_array($where)) {
					$foreign_keys = get_referencing_foreign_keys($database, $table);
					$data = array();

					$query = 'SELECT * FROM `'.$table.'`';
					if(count($where)) {
						$query .= ' WHERE 1';
						foreach ($where as $name => $value) {
							$query .= " AND `$name` IN (".esc($value).')';
						}
					}
					$result = rquery($query);

					$to_check = array();

					while ($row = mysqli_fetch_row($result)) {
						$new_row = array();
						$i = 0;
						foreach ($row as $this_row) {
							$field_info = mysqli_fetch_field_direct($result, $i);
							$new_row[$field_info->name] = $this_row;
							foreach ($foreign_keys as $this_foreign_key) {
								if($this_foreign_key['reference_column'] == $field_info->name) {
									$to_check[] = array('value' => $this_row, 'foreign_key' => array('table' => $this_foreign_key['table'], 'column' => $this_foreign_key['column'], 'database' => $this_foreign_key['database']));
								}
							}
							$i++;
						}
						$GLOBALS['get_data_that_would_be_deleted'][$table][] = $new_row;
					}
					foreach ($to_check as $this_to_check) {
						if(isset($this_to_check['value']) && !is_null($this_to_check['value'])) {
							get_data_that_would_be_deleted($database, $this_to_check['foreign_key']['table'], array($this_to_check['foreign_key']['column'] => $this_to_check['value']), $recursion - 1);
						}
					}

					$data = $GLOBALS['get_data_that_would_be_deleted'];

					return $data;
				} else {
					die("\$where needs to be an array with column_name => value pairs");
				}
			} else {
				die('`'.htmlentities($table).'` is not a valid table name');
			}
		} else {
			die("\$table was not defined!");
		}
	}

	function run_install_query ($database, $queries_data, $print = 0) {
		if($database) {
			if(is_array($queries_data) && count($queries_data)) {
				stderrw("Befülle `$database`");
				$query = 'DELETE FROM `'.$database.'`'."\n";
				if($print) {
					print green_text($query);
				}
				rquery($query);
				foreach ($queries_data as $query) {
					if($print) {
						print green_text($query)."\n";
					}
					$result = rquery($query);
					if($print) {
						if($result) {
							print green_text("Ok\n");
						} else {
							print red_text("Warning\n");
						}
					}
				}
			} else {
				die("\$queries_data muss ein Array sein");
			}
		} else {
			die("\$database muss definiert sein");
		}
	}

	function table_has_mergeable_structure ($table) {
		if(preg_match('/^view_/', $table)) {
			return 0;
		}
		$query1 = 'SHOW COLUMNS FROM '.$table;
		$result1 = rquery($query1);

		$has_mergeable_structure = 1;
		while ($row1 = mysqli_fetch_row($result1)) {
			if($row1[0] == 'id' || $row1[0] == 'name' || $row1[0] == 'abkuerzung' || $row1[0] == 'studiengang_id') {
				# OK
			} else {
				$has_mergeable_structure = 0;
			}
		}

		return $has_mergeable_structure;
	}

	function start_transaction () {
		rquery('SET autocommit = 0');
		rquery('START TRANSACTION');
	}

	function commit () {
		rquery('COMMIT');
		rquery('SET autocommit = 1');
	}

	function rollback () {
		rquery('ROLLBACK');
		rquery('SET autocommit = 1');
	}

	function multiple_esc_join ($data) {
		if(is_array($data)) {
			$data = array_map('esc', $data);
			$string = join(", ", $data);
			return $string;
		} else {
			return esc($data);
		}
	}

	function esc ($parameter) { // escape
		if(!is_array($parameter)) { // Kein array
			if(isset($parameter) && strlen($parameter)) {
				return '"'.mysqli_real_escape_string($GLOBALS['dbh'], $parameter).'"';
			} else {
				return 'NULL';
			}
		} else { // Array
			$str = join(', ', array_map('esc', array_map('my_mysqli_real_escape_string', $parameter)));
			return $str;
		}
	}

	function my_mysqli_real_escape_string ($arg) {
		return mysqli_real_escape_string($GLOBALS['dbh'], $arg);
	}

	// Idee: über diese Wrapperfunktion kann man einfach Queries mitloggen etc., falls notwendig.
	function rquery ($internalquery, $die = 1) {
		$caller_file = debug_backtrace()[0]['file'];
		$caller_line = debug_backtrace()[0]['line'];
		$caller_function = '';
		if(array_key_exists(1, debug_backtrace()) && array_key_exists('function', debug_backtrace()[1])) {
			$caller_function = debug_backtrace()[1]['function'];
		}
		$start = microtime(true);
		$result = mysqli_query($GLOBALS['dbh'], $internalquery);
		$end = microtime(true);
		$used_time = $end - $start;
		$numrows = "&mdash;";
		if(!is_bool($result)) {
			$numrows = mysqli_num_rows($result);
		}
		$GLOBALS['queries'][] = array('query' => "/* $caller_file, $caller_line".($caller_function ? " ($caller_function)" : '').": */\n$internalquery", 'time' => $used_time, 'numrows' => $numrows);

		if($caller_function) {
			if(array_key_exists($caller_function, $GLOBALS['function_usage'])) {
				$GLOBALS['function_usage'][$caller_function]['count']++;
				$GLOBALS['function_usage'][$caller_function]['time'] += $used_time;
			} else {
				$GLOBALS['function_usage'][$caller_function]['count'] = 1;
				$GLOBALS['function_usage'][$caller_function]['time'] = $used_time;
				$GLOBALS['function_usage'][$caller_function]['name'] = $caller_function;
			}
		}

		if(!$result) {
			if($die) {
				dier("Ung&uuml;ltige Anfrage: <p><pre>".$internalquery."</pre></p>".htmlentities(mysqli_error($GLOBALS['dbh'])), 1);
			}
		}

		if($GLOBALS['rquery_print']) {
			print "<p>".htmlentities($internalquery)."</p>\n";
		}

		return $result;
	}

	function merge_data ($table, $from, $to) {
		if(!check_function_rights(__FUNCTION__)) { return; }
		if(preg_match('/^[a-z0-9A-Z_]+$/', $table)) {
			foreach ($from as $this_from) {
				$where = array('id' => $from);
				$data = get_foreign_key_deleted_data($GLOBALS['dbname'], $table, $where);

				foreach ($data as $this_table => $this_table_val) {
					if($this_table != $table) {
						$where = '';
						$refkey = '';

						$this_where = array();

						$foreign_keys = get_foreign_key_tables($GLOBALS['dbname'], $this_table);
						foreach ($foreign_keys as $this_foreign_key) {
							if($this_foreign_key[3] == $table) {
								$refkey = $this_foreign_key[1];
							}
						}

						if($refkey) {
							$primary_keys = get_primary_keys($GLOBALS['dbname'], $this_table);
							$i = 0;
							foreach ($this_table_val as $this_table_val_2) {
								$this_where_str = '';
								foreach ($primary_keys as $this_primary_key) {
									$this_where_str .= ' (';
									$this_where_str .= "`$this_primary_key[0]` = ".esc($this_table_val_2[$this_primary_key[0]]);
									$this_where_str .= ') OR ';

									$i++;
								}
								$this_where[] = $this_where_str;
							}
							$where = join(' ', $this_where);
							$where = preg_replace('/\s+OR\s*$/', '', $where);

							if($where) {
								if(preg_match('/=/', $where)) {
									$query = "UPDATE `$this_table` SET `$refkey` = ".esc($to)." WHERE $where";
									stderrw($query);
									$result = rquery($query);
								} else {
									die("Es konnte kein valides `$where entwickelt werden`: $where.");
								}
							} else {
								die("Es konnte kein `$where entwickelt werden`.");
							}
						}
					}
				}
			}

			$wherea = array();
			foreach ($from as $this_from) {
				if($this_from != $to) {
					$wherea[] = $this_from;
				}
			}
			$where = '`id` IN ('.join(', ', array_map('esc', $wherea)).')';
			$query = "DELETE FROM `$table` WHERE $where";
			$result = rquery($query);

			if($result) {
				success('Die Keys wurden erfolgreich gelöscht. ');
			} else {
				error('Die Daten wurden nicht erfolgreich gemergt. ');
			}
		} else {
			error('Die Tabelle `'.htmlentities($table).'` konnte ist nicht valide.');
		}
	}

?>
