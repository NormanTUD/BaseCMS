<?php
	header_remove("X-Powered-By"); // Serverinfos entfernen

	// Definition globaler Variablen
	include_once("functions/globals.php");

	include_once("mysql.php");
	include_once("functions/system.php");
	include_once("functions/db.php");
	include_once("functions/data.php");
	include_once("functions/get.php");
	include_once("functions/setup.php");
	if(!$GLOBALS['setup_mode']) {
		include_once("functions/session.php");
		include_once("functions/page.php");
		include_once("functions/date.php");
		include_once("functions/messages.php");
		include_once("functions/text.php");
		include_once("functions/rightmanagement.php");
		include_once("functions/useragent.php");
		include_once("functions/navigation.php");
		include_once("functions/callfunctions.php");
		update_user_agent_counter();
	}
?>
