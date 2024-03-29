<?php
/*
Diese Datei soll helfen, ein neues System aufzusetzen, in dem es die nötigen
Ordner, Datenbanken etc. erstellt und mit den ersten, einfachen Daten befüllt.
 */
	include_once("functions.php");
	$GLOBALS['setup_mode'] = 1;
	$php_start = microtime(true);
	if(file_exists('new_setup')) {
		$page_title = "BaseCMS Setup";
		$filename = 'admin.php';
?>
	<div id="mainindex">
		<h1>Setup</h1>

<?php
		$result = rquery('use '.$GLOBALS['dbname'], 0);
		if ($result === false) {
			print "<h2>Erstelle die Datenbank `".$GLOBALS['dbname']."`</h2>\n";
			rquery('CREATE DATABASE IF NOT EXISTS `'.$GLOBALS['dbname'].'`');
		}


		$admin_accounts = array();

		include("sql_datenbanken.php");

		rquery('SET @@global.innodb_large_prefix = 1');
		rquery('select @@FOREIGN_KEY_CHECKS');
		rquery('set FOREIGN_KEY_CHECKS=0');

		foreach ($GLOBALS['databases'] as $name => $create) {
			if(table_exists($GLOBALS['dbname'], $name)) {
				print "Die Tabelle `$name` existiert bereits. Daher erstelle ich sie nicht neu...<br>\n";
			} else {
				print "Die Tabelle `$name` existiert noch nicht. <i><b>Daher erstelle ich sie gerade neu...</b></i><br>\n";
				rquery($create);
			}
		}
		rquery('set FOREIGN_KEY_CHECKS=1');

		foreach ($GLOBALS['views'] as $name => $create) {
			if(table_exists($GLOBALS['dbname'], $name)) {
				print "Die View `$name` existiert bereits. Daher erstelle ich sie nicht neu...<br>\n";
			} else {
				print "Die View `$name` existiert noch nicht. <i><b>Daher erstelle ich sie gerade neu...</b></i><br>\n";
				rquery($create);
			}
		}

		if(array_key_exists('username', $_POST) && array_key_exists('password', $_POST)) {
			rquery('use `'.$GLOBALS['dbname'].'`');
			$query = 'insert into role (id, name) values (1, "Administrator")';
			rquery($query);
			$query = 'insert ignore into users (`username`, `password_sha256`) values ('.esc(get_post('username')).', '.esc(hash('sha256', get_post('password'))).')';
			rquery($query);
			$query = 'insert into role_to_user (role_id, user_id) values (1, 1)';
			rquery($query);
		}

		if(table_exists($GLOBALS['dbname'], 'users')) {
			$query = 'SELECT username FROM `'.$GLOBALS['dbname'].'`.`users` `u` JOIN `role_to_user` `ur` ON `ur`.`user_id` = `u`.`id` WHERE `role_id` = 1';
			$result = rquery($query);
			while ($row = mysqli_fetch_row($result)) {
				$admin_accounts[] = $row[0];
			}
		}

		$query = 'SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = "'.$GLOBALS['dbname'].'"';
		$result = rquery($query);


		if(count($admin_accounts)) {

			foreach ($GLOBALS['install_queries'] as $this_query) {
				print "Running $this_query<br />\n";
				rquery($this_query);
			}

			print "Folgende Administrator-Accounts existieren bereits: <br />\n";
			print "<ul>\n";
			foreach ($admin_accounts as $this_admin) {
				print "\t<li>$this_admin</li>\n";
			}
			print "</ul>\n";

			if($GLOBALS['slurped_sql_file'] == 0) {
				print "<h2>Initialdatensatzfüllung</h2>\n";
				include_once('fill_database.php');
			}

			print "<br /><span style='color: red; font-size: 25px;'>Bitte l&ouml;sche nun die `new_setup`-Datei!</span><br>";
		} else {
?>
			<form method="post" enctype="multipart/form-data">
				Die Datei muss im SQL-Format sein und die Initialdatensatzfüllungsbefehle enthalten.
				<input type="file" name="sql_file">
				<input type="submit" name="import_datenbank" value="Importieren" />
			</form>


			<h2>&mdash; oder &mdash;</h2>

			<form method="post">
				Benutzername: <input type="text" name="username" />
				Passwort: <input type="password" name="password" />
				<input type="submit" value="Adminkonto einrichten" />
			</form>
<?php
		}
	} else {
		exit(0);
	}

	include("footer.php");
?>
