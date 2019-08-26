<?php
	if(1 || (check_page_rights(get_page_id_by_filename(basename(__FILE__))) && file_exists('/etc/vvz_debug_query')) || file_exists('/etc/vvz_debug_query_all')) { // Wichtig, damit Niemand ohne Anmeldung etwas ändern kann
		include_once('scripts/SqlFormatter.php');
		print "<div style='clear: both;' />\n";
		print "<div class='autocenter_large'>\n";
		print "<a onclick='$(\"#query_analyzer\").toggle()' class='outline_text'>Query-Debugger anzeigen?</a>\n";
		print "<div style='clear: both; display: none;' id='query_analyzer' />\n";
		print "\t<table style='max-width: 1350px; background-color: #707070'><tr><th>Query</th><th>Duration</th><th>Numrows</th><th>Query doppelt?</th></tr>\n";
		$i = 0;
		$j = 0;
		$sum = 0;
		$rows = 0;
		$done_queries = array();
		$irgendeine_query_doppelt = 'Nein';
		foreach ($GLOBALS['queries'] as $item) {
			if(!preg_match('/;$/', $item['query'])) {
				$item['query'] .= ';';
			}
			$item['query'] = preg_replace('/`session_id` = "[^"]+"/', '`session_id` = "" /* !!!ausgeblendet!!! */', $item['query']);
			$item['query'] = preg_replace('/INSERT IGNORE INTO `session_ids` \(`session_id`, `user_id`\) VALUES \("[^"]+"/', 'INSERT IGNORE INTO `session_ids` (`session_id`, `user_id`) VALUES ("/* !!!ausgeblendet!!! */"', $item['query']);


			$item['query'] = preg_replace('/`password_sha256` = "[^"]+"/', '`password_sha256` = "" /* !!!ausgeblendet!!! */', $item['query']);

			if(array_key_exists('numrows', $item) && is_int($item["numrows"])) {
				$rows += $item['numrows'];
			} else {
				$item['numrows'] = '&mdash;';
			}
			$query_doppelt = array_key_exists($item['query'], $done_queries) ? 'Ja' : 'Nein';
			if($query_doppelt == 'Ja') {
				$irgendeine_query_doppelt = 'Ja';
			}
			print "\t\t<tr><td>".SqlFormatter::highlight($item['query'])."</td><td>".number_format($item['time'], 6)."</td><td>".$item['numrows']."</td><td>".$query_doppelt."</td>\n";
			if(preg_match('/^\s*\/\*.*\*\/\s*(UPDATE|SELECT|DELETE|INSERT)\s(?!@@)/i', $item['query'])) {
				$i++;
			} else {
				$j++;
			}
			$sum += $item['time'];
			$done_queries[$item['query']] = 1;

		}

		if($irgendeine_query_doppelt == 'Ja') {
			$irgendeine_query_doppelt = '<span class="class_red">Ja</span>';
		} else {
			$irgendeine_query_doppelt = '<span class="class_green">Nein</span>';
		}

		print "\t\t<tr><td>&mdash;</td><td>&sum;Zeit&darr;</td><td>&sum;NR&darr;</td><td>Queries Doppelt? $irgendeine_query_doppelt</td></tr>\n";
		print "\t\t<tr><td>All ".($j + $i)." Queries ($j preparational, $i functional)</td><td>".number_format($sum, 8)."</td><td>$rows</td><td></td></tr>\n";
		$php_time = microtime(true) - $GLOBALS['php_start'];
		print "\t\t<tr><td>PHP without Queries</td><td>".number_format($php_time - $sum, 8)."</td><td></td><td></td></tr>\n";
		print "\t\t<tr><td>All</td><td>".number_format($php_time, 6)."</td><td></td><td></td></tr>\n";
		print "\t</table>\n";
		if(count($GLOBALS['function_usage'])) {
			print "<br /><br />\t<table style='max-width: 1350px; background-color: #707070'><tr><th>Funktionsname</th><th>Anzahl Aufrufe</th><th>Zeit in Queries</th></tr>\n";
			foreach ($GLOBALS['function_usage'] as $name) {
				print "\t\t<tr><td>".$name['name']."</td><td>".$name['count']."</td><td>".number_format($name['time'], 6)."</td></tr>\n";
			}
			print "\t</table>\n";
		}

		$included_files = get_included_files();

		print "\t<br /><table>\n";
		$i = 0;
		foreach ($included_files as $id => $name) {
			if($i == 0) {
				print "\t\t<tr><td style='background-color: rgb(0, 48, 94); color: white'>Benutzte Dateien:</td></tr>\n";
			}
			if(!file_exists($name)) {
				$testname = "./pages/$name";
				$testname2 = "./scripts/$name";
				if(file_exists($testname)) {
					$name = $testname;
				}else if (file_exists($testname2)) {
					$name = $testname2;
				} else {
					$name = "<span style='color: red'>$name</span>";
				}
			}
			print "\t\t<tr><td>$name</td></tr>\n";
			$i++;
		}
		print "\t</table>\n";

		print "</div>\n";
		print "</div>\n";
	}
?>
