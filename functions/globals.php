<?php
	$GLOBALS['error'] = array();
	$GLOBALS['hint'] = array();
	$GLOBALS['message'] = array();
	$GLOBALS['warning'] = array();
	$GLOBALS['success'] = array();
	$GLOBALS['debug'] = array();
	$GLOBALS['easter_egg'] = array();

	$GLOBALS['compare_db'] = '';

	$GLOBALS['already_deleted_old_session_ids'] = 0;

	$GLOBALS['submenu_id'] = null;

	$GLOBALS['end_html'] = 1;

	$GLOBALS['slurped_sql_file'] = 0;

	$GLOBALS['deletion_page'] = 0;

	$GLOBALS['rquery_print'] = 0;

	$GLOBALS['queries'] = array();
	$GLOBALS['function_usage'] = array();

	$GLOBALS['dbh'] = '';
	$GLOBALS['right_issue'] = array();
	$GLOBALS['reload_page'] = 0;

	$GLOBALS['get_letzte_wartung_cache'] = array();
	$GLOBALS['get_letzte_wartung_cache'][0] = array();
	$GLOBALS['get_letzte_wartung_cache'][1] = array();
	$GLOBALS['get_anlagen_cache'] = array();
	$GLOBALS['get_anlagen_data_cache'] = array();
	$GLOBALS['get_turnus_cache'] = array();
	$GLOBALS['user_role_cache'] = array();

	$GLOBALS['memoize'] = array();

	$GLOBALS['logged_in_was_tried'] = 0;
	$GLOBALS['logged_in'] = 0;
	$GLOBALS['logged_in_user_id'] = NULL;
	$GLOBALS['logged_in_data'] = NULL;
	$GLOBALS['accepted_public_data'] = NULL;

	$GLOBALS['pages'] = NULL;

	function global_exists ($name) {
		if(array_key_exists($name, $GLOBALS) && count($GLOBALS[$name])) {
			return 1;
		} else {
			return 0;
		}
	}

	$GLOBALS['adminpage'] = 'admin.php';
?>
