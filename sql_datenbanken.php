<?php
	$GLOBALS['databases'] = array(
		'function_rights' => 'CREATE TABLE `function_rights` (
		  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
		  `name` varchar(255) DEFAULT NULL,
		  `role_id` int(10) unsigned NOT NULL,
		  PRIMARY KEY (`id`),
		  UNIQUE KEY `name_role_id` (`name`,`role_id`),
		  KEY `role_id` (`role_id`),
		  CONSTRAINT `function_rights_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `role` (`id`) ON DELETE CASCADE
		) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;',

		'hinweise' => 'CREATE TABLE `hinweise` (
		  `page_id` int(10) unsigned NOT NULL,
		  `hinweis` text,
		  PRIMARY KEY (`page_id`),
		  CONSTRAINT `hinweise_ibfk_1` FOREIGN KEY (`page_id`) REFERENCES `page` (`id`) ON DELETE CASCADE
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;',

		'page' => "CREATE TABLE `page` (
		  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
		  `name` varchar(50) NOT NULL,
		  `file` varchar(50) DEFAULT NULL,
		  `show_in_navigation` enum('0','1') NOT NULL DEFAULT '0',
		  `parent` int(10) unsigned DEFAULT NULL,
		  PRIMARY KEY (`id`),
		  UNIQUE KEY `name` (`name`),
		  UNIQUE KEY `file` (`file`),
		  KEY `page` (`parent`),
		  CONSTRAINT `page_ibfk_1` FOREIGN KEY (`parent`) REFERENCES `page` (`id`) ON DELETE SET NULL
		) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;",

		'page_info' => 'CREATE TABLE `page_info` (
		  `page_id` int(10) unsigned NOT NULL,
		  `info` varchar(1000) DEFAULT NULL,
		  PRIMARY KEY (`page_id`),
		  KEY `page_id` (`page_id`),
		  CONSTRAINT `page_info_ibfk_1` FOREIGN KEY (`page_id`) REFERENCES `page` (`id`) ON DELETE CASCADE
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;',

		'right_issues' => "CREATE TABLE `right_issues` (
		  `function` varchar(100) NOT NULL DEFAULT '',
		  `user_id` int(10) unsigned NOT NULL,
		  `date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
		  PRIMARY KEY (`function`,`user_id`,`date`),
		  KEY `user_id` (`user_id`),
		  CONSTRAINT `right_issues_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;",

		'right_issues_pages' => "CREATE TABLE `right_issues_pages` (
		  `user_id` int(10) unsigned NOT NULL,
		  `page_id` int(10) unsigned NOT NULL,
		  `date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
		  PRIMARY KEY (`user_id`,`page_id`,`date`),
		  KEY `page_id` (`page_id`),
		  CONSTRAINT `right_issues_pages_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
		  CONSTRAINT `right_issues_pages_ibfk_2` FOREIGN KEY (`page_id`) REFERENCES `page` (`id`) ON DELETE CASCADE
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;",

		'role' => 'CREATE TABLE `role` (
		  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
		  `name` varchar(100) DEFAULT NULL,
		  PRIMARY KEY (`id`),
		  UNIQUE KEY `name` (`name`)
		) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;',

		'role_to_page' => 'CREATE TABLE `role_to_page` (
		  `role_id` int(10) unsigned NOT NULL,
		  `page_id` int(10) unsigned NOT NULL,
		  PRIMARY KEY (`role_id`,`page_id`),
		  KEY `page_id` (`page_id`),
		  CONSTRAINT `role_to_page_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `role` (`id`) ON DELETE CASCADE,
		  CONSTRAINT `role_to_page_ibfk_2` FOREIGN KEY (`page_id`) REFERENCES `page` (`id`) ON DELETE CASCADE
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;',

		'role_to_user' => 'CREATE TABLE `role_to_user` (
		  `role_id` int(10) unsigned NOT NULL,
		  `user_id` int(10) unsigned NOT NULL,
		  PRIMARY KEY (`role_id`,`user_id`),
		  UNIQUE KEY `name` (`user_id`),
		  CONSTRAINT `role_to_user_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `role` (`id`) ON DELETE CASCADE,
		  CONSTRAINT `role_to_user_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;',

		'seitentext' => 'CREATE TABLE `seitentext` (
		  `page_id` int(10) unsigned NOT NULL,
		  `text` varchar(10000) DEFAULT NULL,
		  PRIMARY KEY (`page_id`),
		  CONSTRAINT `seitentext_ibfk_1` FOREIGN KEY (`page_id`) REFERENCES `page` (`id`) ON DELETE CASCADE
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;',

		'session_ids' => 'CREATE TABLE `session_ids` (
		  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
		  `session_id` varchar(1024) NOT NULL,
		  `user_id` int(10) unsigned NOT NULL,
		  `creation_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
		  PRIMARY KEY (`id`),
		  KEY `user_id` (`user_id`),
		  CONSTRAINT `session_ids_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
		) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;',

		'ua_browser' => 'CREATE TABLE `ua_browser` (
		  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
		  `name` varchar(100) NOT NULL,
		  PRIMARY KEY (`id`),
		  UNIQUE KEY `name` (`name`)
		) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;',


		'ua_call' => "CREATE TABLE `ua_call` (
		  `specific_os_id` int(10) unsigned NOT NULL DEFAULT '0',
		  `specific_browser_id` int(10) unsigned NOT NULL DEFAULT '0',
		  `c` int(10) unsigned DEFAULT NULL,
		  `month` int(10) unsigned NOT NULL,
		  `year` int(10) unsigned NOT NULL,
		  `day` int(10) unsigned DEFAULT NULL,
		  PRIMARY KEY (`specific_os_id`,`specific_browser_id`,`month`,`year`),
		  KEY `specific_browser_id` (`specific_browser_id`),
		  CONSTRAINT `ua_call_ibfk_1` FOREIGN KEY (`specific_os_id`) REFERENCES `ua_specific_os` (`id`) ON DELETE CASCADE,
		  CONSTRAINT `ua_call_ibfk_2` FOREIGN KEY (`specific_browser_id`) REFERENCES `ua_specific_browser` (`id`) ON DELETE CASCADE
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;",

		'ua_os' => 'CREATE TABLE `ua_os` (
		  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
		  `name` varchar(100) NOT NULL,
		  PRIMARY KEY (`id`),
		  UNIQUE KEY `name` (`name`)
		) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;',

		'ua_specific_browser' => 'CREATE TABLE `ua_specific_browser` (
		  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
		  `name_id` int(10) unsigned DEFAULT NULL,
		  `version` varchar(100) NOT NULL,
		  PRIMARY KEY (`id`),
		  UNIQUE KEY `name_version` (`name_id`,`version`),
		  CONSTRAINT `ua_specific_browser_ibfk_1` FOREIGN KEY (`name_id`) REFERENCES `ua_browser` (`id`) ON DELETE CASCADE
		) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;',

		'ua_specific_os' => 'CREATE TABLE `ua_specific_os` (
		  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
		  `name_id` int(10) unsigned DEFAULT NULL,
		  `version` varchar(100) NOT NULL,
		  PRIMARY KEY (`id`),
		  UNIQUE KEY `name_version` (`name_id`,`version`),
		  CONSTRAINT `ua_specific_os_ibfk_1` FOREIGN KEY (`name_id`) REFERENCES `ua_os` (`id`) ON DELETE CASCADE
		) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;',

		'users' => "CREATE TABLE `users` (
		  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
		  `username` varchar(100) DEFAULT NULL,
		  `dozent_id` int(10) unsigned DEFAULT NULL,
		  `institut_id` int(10) unsigned DEFAULT NULL,
		  `password_sha256` varchar(256) DEFAULT NULL,
		  `salt` varchar(100) NOT NULL,
		  `enabled` enum('0','1') NOT NULL DEFAULT '1',
		  `accepted_public_data` enum('0','1') NOT NULL DEFAULT '0',
		  PRIMARY KEY (`id`),
		  UNIQUE KEY `name` (`username`),
		  UNIQUE KEY `dozent_id` (`dozent_id`),
		  KEY `institut_id` (`institut_id`),
		  CONSTRAINT `users_ibfk_1` FOREIGN KEY (`dozent_id`) REFERENCES `dozent` (`id`) ON DELETE CASCADE,
		  CONSTRAINT `users_ibfk_2` FOREIGN KEY (`institut_id`) REFERENCES `institut` (`id`) ON DELETE CASCADE
		) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;"
	);

	$GLOBALS['views'] = array(
		'ua_overview' => 'CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `ua_overview` AS select `o`.`name` AS `os_name`,`so`.`version` AS `os_version`,`b`.`name` AS `browser_name`,`sb`.`version` AS `browser_version`,`c`.`c` AS `c`,`c`.`year` AS `year`,`c`.`month` AS `month`,`c`.`day` AS `day` from ((((`ua_call` `c` left join `ua_specific_browser` `sb` on((`sb`.`id` = `c`.`specific_browser_id`))) left join `ua_browser` `b` on((`b`.`id` = `sb`.`name_id`))) left join `ua_specific_os` `so` on((`so`.`id` = `c`.`specific_os_id`))) left join `ua_os` `o` on((`o`.`id` = `so`.`name_id`)))',
		'view_account_to_role_pages' => 'CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_account_to_role_pages` AS select `p`.`id` AS `page_id`,`p`.`name` AS `name`,`p`.`file` AS `file`,`ru`.`user_id` AS `user_id`,`p`.`show_in_navigation` AS `show_in_navigation`,`p`.`parent` AS `parent` from ((`role_to_user` `ru` join `role_to_page` `rp` on((`rp`.`role_id` = `ru`.`role_id`))) join `page` `p` on((`p`.`id` = `rp`.`page_id`)))',
		'view_page_and_hinweis' => 'CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_page_and_hinweis` AS select `p`.`id` AS `id`,`p`.`name` AS `name`,`p`.`show_in_navigation` AS `show_in_navigation`,`h`.`hinweis` AS `hinweis` from (`page` `p` left join `hinweise` `h` on((`h`.`page_id` = `p`.`id`)))',
		'view_page_and_text' => 'CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_page_and_text` AS select `p`.`id` AS `id`,`p`.`name` AS `name`,`p`.`show_in_navigation` AS `show_in_navigation`,`h`.`text` AS `text` from (`page` `p` left join `seitentext` `h` on((`h`.`page_id` = `p`.`id`)))',
		'view_user_session_id' => 'CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_user_session_id` AS select `s`.`id` AS `session_id_id`,`u`.`id` AS `user_id`,`s`.`session_id` AS `session_id`,`s`.`creation_time` AS `creation_time`,`u`.`username` AS `username`,`u`.`dozent_id` AS `dozent_id`,`u`.`institut_id` AS `institut_id`,`u`.`enabled` AS `enabled`,`u`.`accepted_public_data` AS `accepted_public_data` from (`users` `u` left join `session_ids` `s` on((`s`.`user_id` = `u`.`id`)))',
		'view_user_to_role' => 'CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_user_to_role` AS select `u`.`id` AS `user_id`,`u`.`username` AS `username`,`ru`.`role_id` AS `role_id`,`r`.`name` AS `name`,`u`.`dozent_id` AS `dozent_id`,`u`.`institut_id` AS `institut_id`,`u`.`enabled` AS `enabled` from ((`users` `u` left join `role_to_user` `ru` on((`u`.`id` = `ru`.`user_id`))) join `role` `r` on((`r`.`id` = `ru`.`role_id`)))',

	);

	$GLOBALS['install_queries'] = array(
		'SET FOREIGN_KEY_CHECKS=0;',
		'insert into page (id, name, file, show_in_navigation, parent) values (1, "Willkommen!", "welcome.php", "0", NULL);',
		'insert into page (id, name, file, show_in_navigation, parent) values (2, "Accounts", "accounts.php", "1", 3);',
		'insert into page (id, name, file, show_in_navigation, parent) values (3, "System", NULL, "1", NULL);',
		'insert into page (id, name, file, show_in_navigation, parent) values (4, "Eigene Daten ändern", "password.php", "1", NULL);',
		'insert into page (id, name, file, show_in_navigation, parent) values (5, "Rollen", "roles.php", "1", 3);',
		'insert into page (id, name, file, show_in_navigation, parent) values (6, "Seiteninformationen", "edit_page_info.php", "1", 3);',
		'insert into page (id, name, file, show_in_navigation, parent) values (7, "Rechteprobleme", "right_issues.php", "1", 3);',
		'insert into page (id, name, file, show_in_navigation, parent) values (8, "Query-Analyzer", "query_analyzer.php", "0", 3);',
		'insert into page (id, name, file, show_in_navigation, parent) values (9, "DB-Backup", "backup.php", "1", 3);',
		'insert into page (id, name, file, show_in_navigation, parent) values (10, "DB-Backup-Export", "backup_export.php", "0", 3);',
		'insert into page (id, name, file, show_in_navigation, parent) values (11, "DB-Diff", "dbdiff.php", "1", 3);',
		'insert into page (id, name, file, show_in_navigation, parent) values (12, "User-Agents", "useragents.php", "1", 3);',
		'insert into page (id, name, file, show_in_navigation, parent) values (13, "Seiten", "newpage.php", "1", 3);',

		'insert into page_info (page_id, info) values (1, "Willkommen!")',
		'insert into page_info (page_id, info) values (2, "Accounts")',
		'insert into page_info (page_id, info) values (3, "System")',
		'insert into page_info (page_id, info) values (4, "Eigene Daten ändern");',
		'insert into page_info (page_id, info) values (5, "Rollen")',
		'insert into page_info (page_id, info) values (6, "Seiteninformationen")',
		'insert into page_info (page_id, info) values (7, "Rechteprobleme")',
		'insert into page_info (page_id, info) values (8, "Query-Analyzer")',
		'insert into page_info (page_id, info) values (9, "DB-Backup")',
		'insert into page_info (page_id, info) values (10, "DB-Backup-Export")',
		'insert into page_info (page_id, info) values (11, "DB-Diff")',
		'insert into page_info (page_id, info) values (12, "User-Agents")',
		'insert into page_info (page_id, info) values (13, "Seiten")',

		'insert into function_rights (name, role_id) values ("add_leading_zero", 1);',
		'insert into function_rights (name, role_id) values ("assign_page_to_role", 1);',
		'insert into function_rights (name, role_id) values ("backup_tables", 1);',
		'insert into function_rights (name, role_id) values ("compare_db", 1);',
		'insert into function_rights (name, role_id) values ("create_new_page", 1);',
		'insert into function_rights (name, role_id) values ("create_page", 1);',
		'insert into function_rights (name, role_id) values ("create_role", 1);',
		'insert into function_rights (name, role_id) values ("create_select", 1);',
		'insert into function_rights (name, role_id) values ("create_table_one_dependency", 1);',
		'insert into function_rights (name, role_id) values ("create_user", 1);',
		'insert into function_rights (name, role_id) values ("delete_page", 1);',
		'insert into function_rights (name, role_id) values ("delete_role", 1);',
		'insert into function_rights (name, role_id) values ("delete_user", 1);',
		'insert into function_rights (name, role_id) values ("get_and_create_salt", 1);',
		'insert into function_rights (name, role_id) values ("get_cached", 1);',
		'insert into function_rights (name, role_id) values ("get_page_id_by_filename", 1);',
		'insert into function_rights (name, role_id) values ("merge_data", 1);',
		'insert into function_rights (name, role_id) values ("merge_table_data", 1);',
		'insert into function_rights (name, role_id) values ("query_analyzer", 1);',
		'insert into function_rights (name, role_id) values ("show_output", 1);',
		'insert into function_rights (name, role_id) values ("simple_edit", 1);',
		'insert into function_rights (name, role_id) values ("SplitSQL", 1);',
		'insert into function_rights (name, role_id) values ("update_hinweis", 1);',
		'insert into function_rights (name, role_id) values ("update_nachpruefung", 1);',
		'insert into function_rights (name, role_id) values ("update_or_create_role_to_page", 1);',
		'insert into function_rights (name, role_id) values ("update_own_data", 1);',
		'insert into function_rights (name, role_id) values ("update_own_password", 1);',
		'insert into function_rights (name, role_id) values ("update_page", 1);',
		'insert into function_rights (name, role_id) values ("update_page_full", 1);',
		'insert into function_rights (name, role_id) values ("update_page_info", 1);',
		'insert into function_rights (name, role_id) values ("update_role", 1);',
		'insert into function_rights (name, role_id) values ("update_startseitentext", 1);',
		'insert into function_rights (name, role_id) values ("update_text", 1);',
		'insert into function_rights (name, role_id) values ("update_user", 1);',
		'insert into function_rights (name, role_id) values ("update_user_role", 1);',
		'insert into function_rights (name, role_id) values ("user_is_allowed_to_access", 1);',
		'insert into role_to_page (role_id, page_id) values (1, 1);',
		'insert into role_to_page (role_id, page_id) values (1, 2);',
		'insert into role_to_page (role_id, page_id) values (1, 3);',
		'insert into role_to_page (role_id, page_id) values (1, 4);',
		'insert into role_to_page (role_id, page_id) values (1, 5);',
		'insert into role_to_page (role_id, page_id) values (1, 6);',
		'insert into role_to_page (role_id, page_id) values (1, 7);',
		'insert into role_to_page (role_id, page_id) values (1, 8);',
		'insert into role_to_page (role_id, page_id) values (1, 9);',
		'insert into role_to_page (role_id, page_id) values (1, 10);',
		'insert into role_to_page (role_id, page_id) values (1, 11);',
		'insert into role_to_page (role_id, page_id) values (1, 12);',
		'insert into role_to_page (role_id, page_id) values (1, 13);',
		'SET FOREIGN_KEY_CHECKS=1;'
	);
?>
