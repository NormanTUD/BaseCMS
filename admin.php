<?php
	$GLOBALS['php_start'] = microtime(true);
	if(file_exists('new_setup')) {
		include('setup.php');
		exit(0);
	}
	$page_title = "BaseCMS";
	include("header.php");

	$filename = $GLOBALS['adminpage'];


	if(!$GLOBALS['logged_in']) {
?>
		<div id="main">
			<a href="<?php print $filename; ?>" border="0"><img alt="Link zur Startseite" src="logo.png" /></a>
			<div id="wrapper" style="text-align: center">
			<div style="width: 400px; text-align: center; display: inline-block;">
<?php
				if($GLOBALS['logged_in_was_tried']) {
					if(get_post('username') || get_post('password')) {
						sleep(3);
?>
						<span style="color: red;">Benutzername oder Passwort falsch</span><br />
<?php
					} else {
?>
						<span style="color: red;">Benutzername und Passwort dürfen nicht leer sein.</span><br />
<?php
					}
				}
?>
				<form method="post">
					<i>Das Anmelden auf diesem Gerät meldet automatisch von allen anderen angemeldeten Geräten ab.</i>
					<input type="hidden" name="try_login" value="1" />
					<div style="height: 10px;"></div>
					<input type="text" name="username" placeholder="Benutzername" />
					<div style="height: 10px;"></div>
					<input type="password" name="password" placeholder="Passwort" />
					<div style="height: 20px;"></div>
					<input type="submit" value="Anmelden" />
				</form>
			</div>
			</div>
<?php
			$GLOBALS['end_html'] = 0;
?>
		</body>
	</html>
<?php
	} else {
		$dozent_name = htmlentities($GLOBALS['logged_in_data'][1]);
		if(!preg_match('/\w{2,}/', $dozent_name)) {
			$dozent_name = htmlentities($GLOBALS['logged_in_data'][1]);
		}
		if(!$GLOBALS['user_role_id'][0]) {
			$dozent_name = htmlentities($GLOBALS['logged_in_data'][1]).' <span class="class_red">!!! Ihr Account hat keine ihm zugeordnete Rolle! !!!</span>';
		}
?>
		<div id="main">
			<a href="<?php print $filename; ?>" border="0"><img alt="Link zur Startseite" src="logo.png" /></a>
			Willkommen, <?php print $dozent_name; ?>!
<?php
			if(get_post('password') == 'test' && get_post('try_login')) {
?>
				<script type="text/javascript">alert("Bitte ändern Sie Ihr Passwort! Dies können Sie unter dem Menüpunkt 'Eigene Daten ändern' machen. Diese Meldung wird bei jedem Anmelden kommen, solange Sie Ihr Passwort nicht geändert haben.");</script>
<?php
			}
?>
			<img src="empty.gif" width="200" height=1 /><div class="tooltip"><a style="color: red; font-size: 20" href="logout.php">Abmelden</a><span class="tooltiptext">Meldet alle angemeldeten Geräte ab</span></div>
<?php
			if($GLOBALS['user_role_id'] == 1) {
				$df = sprintf("%0.2f", disk_free_space($_SERVER['DOCUMENT_ROOT']) / 1024 / 1024 / 1024);
				if($df <= 1) {
					print("<br /><span class='class_red'>Warnung: nur noch $df GB freier Speicher auf der Festplatte!</span>");
				}
			}
?>
			<div style="height: 5px;"></div>
				<ul class="topnav">
					<li><a href="<?php print $filename; ?>" <?php print (get_get('page') || get_get('show_items')) ? '' : 'class="selected_tab"'; ?>><?php print (get_get('page') || get_get('show_items')) ? '' : '&rarr; '; ?>Willkommen!</a></li>
<?php
					if(count($GLOBALS['pages'])) {
						foreach ($GLOBALS['pages'] as $this_page) {
							# 0	   1	   2		3		    4
							#`name`, `file`, `page_id`, `show_in_navigation`, `parent`
							if($this_page[3]) {
								if($this_page[1]) { # Kein Dropdown
									if(!$this_page[4]) {
										if($this_page[2] == get_get('page') || $this_page[2] == get_get('show_items')) {
											print "<li class='selected_tab'><a href='$filename?page=".$this_page[2]."'>&rarr; $this_page[0]</a></li>\n";
										} else {
											print "<li><a href='$filename?page=".$this_page[2]."'>$this_page[0]</a></li>\n";
										}
									}
								} else { # Dropdown
									$subnav_data = print_subnavigation($this_page[2]);
									if($subnav_data[0]) {
?>
										<li class='selected_tab'><a href='<?php print $filename; ?>?show_items=<?php print $this_page[2];?>'>&rarr; <?php print $this_page[0]; ?> &darr;</a><?php print $subnav_data[1]; ?></li>
<?php
									} else {
										if($this_page[2] == get_get('page') || $this_page[2] == get_get('show_items')) {
?>
											<li class="dropdown selected_tab"><a href='<?php print $filename; ?>?show_items=<?php print $this_page[2];?>'>&rarr; <?php print $this_page[0]; ?> &darr;</a><?php print $subnav_data[1]; ?></li>
<?php
										} else {
?>
											<li class="dropdown"><a href='<?php print $filename; ?>?show_items=<?php print $this_page[2];?>'><?php print $this_page[0]; ?> &darr;</a><?php print $subnav_data[1]; ?></li>
<?php
										}
									}
								}
							}
						}
					} else {
						print "<h2 class='class_red'>Fehler beim Holen der Seiten!</h2>";
					}
?>
				</ul>
<?php
			foreach (array(
					array("hint", "blue"),
					array("error", "red"),
					array("right_issue", "red"),
					array("warning", "orange"),
					array("debug", "yellow"),
					array("message", "blue"),
					array("easter_egg", "hotpink"),
					array("success", "green")
				) as $msg) {
				show_output($msg[0], $msg[1]);
			}

			if($GLOBALS['accepted_public_data']) {
				$pagenr = get_get('page');
				if(!$pagenr) {
					$pagenr = get_post('page');
				}

				if(!preg_match('/^\d+$/', $pagenr)) {
					$pagenr = null;
				}

				if(get_get('show_items')) {
					$query = 'SELECT `id`, `name` FROM `page` WHERE `parent` = '.esc(get_get('show_items')).' AND `show_in_navigation` = "1" AND `id` IN (SELECT `page_id` FROM `role_to_page` WHERE `role_id` = '.esc($GLOBALS['user_role_id'][0]).') ORDER BY `name`';
					$result = rquery($query);

					if(mysqli_num_rows($result)) {
						$subpage_data = array();
						$subpage_ids = array();
						while ($row = mysqli_fetch_row($result)) {
							if($row[1]) {
								$subpage_data[] = array($row[0], $row[1]);
								$subpage_ids[] = $row[0];
							}
						}
						$subpage_texts = get_page_info_by_id($subpage_ids);
						print "<h2>Untermenüs von &raquo;".get_page_name_by_id(get_get('show_items'))."&laquo;</h2>\n";
						$GLOBALS['submenu_id'] = get_get('show_items');
						include('hinweise.php');
						print "<ul>\n";
						foreach ($subpage_data as $row) {
							if($row[1]) {
								print "<li style='margin: 5px 0;'><a href='$filename?page=$row[0]'>$row[1]</a> &mdash; ".htmlentities($subpage_texts[$row[0]])."</li>\n";
							}
						}
						print "</ul>\n";
					} else {
						print "<h2 class='class_red'>Der ausgewählte Menüpunkt ist leider nicht im System vorhanden oder Sie haben keine Rechte, auf ihn zuzugreifen.</h2>\n";
					}
				} else {
					if(!isset($pagenr)) {
						include(dirname(__FILE__).'/pages/welcome.php');
					} else {
						$page_file = '';
						if(array_key_exists($pagenr, $GLOBALS['pages'])) {
							$page_file = $GLOBALS['pages'][$pagenr][1];
						} else {
							$page_file = get_page_file_by_id($pagenr);
						}

						$page_file_basename = $page_file;

						$page_file = dirname(__FILE__).'/pages/'.$page_file;

						if(!file_exists($page_file)) {
							die("Die Datei `$page_file_basename` konnte nicht gefunden werden!");
						} else if (!$page_file_basename) {
							die("Die Unterseite konnte in der Datenbank nicht gefunden werden!");
						} else {
							if(check_page_rights($page_file_basename)) {
								if($GLOBALS['deletion_page']) {
									warning("<h2>Sicher, dass das alles gelöscht werden soll?</h2>");
									show_output("warning", "orange");
?>

									Um die <a href="https://de.wikipedia.org/wiki/Konsistenz_%28Datenspeicherung%29">Datenintegrität</a> zu gewährleisten, werden
									alle Datensätze, die von dem, der gelöscht werden soll, abhängig sind, auch gelöscht. Dies kann mitunter gewaltige
									Auswirkungen auf das gesamte System haben. Daher soll das Löschen extra bestätigt werden, bevor es ausgeführt wird.

									In den folgenden Tabellen sehen Sie alle Daten, die, mit diesem Datensatz zusammen, gelöscht werden. Am unteren Ende der
									Seite haben Sie die Möglichkeit, das Löschen tatsächlich auszuführen bzw. abzubrechen.
<?php
									if($GLOBALS['deletion_db'] && $GLOBALS['deletion_where']) {
										print get_foreign_key_deleted_data_html($GLOBALS['dbname'], $GLOBALS['deletion_db'], $GLOBALS['deletion_where']);
									}
?>
									<form method="post" enctype="multipart/form-data" action="<?php print $_SERVER['HTTP_REFERER']; ?>">
<?php
										foreach ($_POST as $this_post_name => $this_post_value) {
											if(!is_array($this_post_value)) {
?>
												<input type="hidden" name="<?php print htmlentities($this_post_name); ?>" value="<?php print htmlentities($this_post_value); ?>" />
<?php
											} else {
												foreach ($this_post_value as $array_this_post_name => $array_this_post_value) {
?>
													<input type="hidden" name="<?php print htmlentities($this_post_name); ?>[]" value="<?php print htmlentities($array_this_post_value); ?>" />
<?php
												}
											}
										}
?>
										<input type="hidden" name="delete_for_sure" value="1" />
										<input type="submit" value="Ja, ich bin mir sicher!" />
									</form>
									<form>
										<input type="button" value="Nein, lieber nicht." onClick="history.go(-1);return true;">
									</form>
<?php
								} else {
									$GLOBALS['this_page_number'] = $pagenr;
									$GLOBALS['this_page_file'] = $page_file;
									include('hinweise.php');
									include($page_file);
								}
							} else {
								print "<i class='class_red'>Sie haben kein Recht, auf diese Seite zuzugreifen.</i>";
							}
						}
					}
				}
			} else {
?>
				<h3>Datenschutz-/Einwilligungserklärung </h3>

				<p>Hiermit bestätige ich, dass ich das ganze rechtliche Blabla kenne, verstehe und damit einverstanden bin.</p>
<?php
				if(get_get('page') || get_get('show_items')) {
					$id = get_get('page');
					if(!$id) {
						$id = get_get('show_items');
					}
?>
					<p style="color: red;">Die Seite &raquo;<?php print get_page_name_by_id($id); ?>&laquo; konnte nicht aufgerufen werden. Bitte stimmen
					Sie zuerst den Datenschutzbedingungen zu.</p>
<?php
				}
?>
				
				<form>
					<input type="hidden" name="page" value="<?php print htmlentities(get_get('page')); ?>" />
					<input type="hidden" name="show_items" value="<?php print htmlentities(get_get('show_items')); ?>" />
					Ankreuzeln, wenn einverstanden, dann &raquo;Akzeptieren&laquo; drücken! &rarr; <input type="checkbox" name="sdsg_einverstanden" value="1" />
					<input type="submit" value="Akzeptieren" />
				</form>
<?php
			}
		}
		include("footer.php");

?>
