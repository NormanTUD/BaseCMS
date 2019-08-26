<?php
	function check_page_rights ($page, $log = 1) {
		$log = 0;
		if((array_key_exists('user_role_id', $GLOBALS) && isset($GLOBALS['user_role_id']))) {
			$role_id = $GLOBALS['user_role_id'];
			return check_page_rights_role_id($page, $role_id, $log);
		} else {
			return 0;
		}
	}

	function check_function_rights ($function, $log = 1) {
		$role_id = $GLOBALS['user_role_id'];
		return check_function_rights_role_id($function, $role_id, $log);
	}

	function check_function_rights_role_id ($function, $role_id, $log = 1) {
		if(!$role_id || is_null($role_id)) {
			$role_id = $GLOBALS['user_role_id'];
		}

		$return = 0;
		if(isset($GLOBALS['logged_in_user_id'])) {
			$query = 'SELECT `id` FROM `function_rights` WHERE `name` = '.esc($function).' AND `role_id` = '.esc($role_id);
			$result = rquery($query);
			
			$rights_id = null;
			while ($row = mysqli_fetch_row($result)) {
				$rights_id = $row[0];
			}

			if(!is_null($rights_id)) {
				$return = 1;
			}
		}

		if($log) {
			if(!$return) {
				right_issue("Die Funktion $function darf mit den aktuellen Rechten nicht ausgeführt werden. ");
				$query = 'INSERT IGNORE INTO `right_issues` (`user_id`, `function`, `date`) VALUES ('.esc($GLOBALS['logged_in_user_id']).', '.esc($function).', now())';
				rquery($query);
				right_issue("Der Vorfall wird gespeichert und der Administrator informiert. ");
			}
		}

		return $return;
	}

	function check_page_rights_role_id ($page_id, $role_id, $log = 1) {
		if( (isset($role_id) || is_null($role_id) ) && (array_key_exists('user_role_id', $GLOBALS) && isset($GLOBALS['user_role_id'])) ) {
			$role_id = $GLOBALS['user_role_id'];
		}

		if(!$role_id) {
			return 0;
		}

		if(is_array($page_id)) {
			$query = 'SELECT `page_id` FROM `role_to_page` WHERE `page_id` IN ('.multiple_esc_join($page_id).') AND `role_id` = '.esc($role_id);
			$result = rquery($query);
			
			$rights_id = array();
			while ($row = mysqli_fetch_row($result)) {
				$rights_id[] = $row[0];
			}

			return $rights_id;
		} else {
			if(!preg_match('/^\d+$/', $page_id)) {
				$page_id = get_page_id_by_filename($page_id);
			}
			$return = 0;
			$key = "$page_id----$role_id";
			if(array_key_exists($key, $GLOBALS['user_role_cache'])) {
				$return = $GLOBALS['user_role_cache'][$key];
			} else {
				if(isset($GLOBALS['logged_in_user_id'])) {
					$query = 'SELECT `page_id` FROM `role_to_page` WHERE `page_id` = '.esc($page_id).' AND `role_id` = '.esc($role_id);
					$result = rquery($query);
					
					$rights_id = null;
					while ($row = mysqli_fetch_row($result)) {
						$rights_id = $row[0];
					}

					if(!is_null($rights_id)) {
						$return = 1;
					}
				}
			}
			$GLOBALS['user_role_cache'][$key] = $return;

			if($log) {
				if(!$return) {
					right_issue("Die Seite mit der ID `$page_id` darf mit den aktuellen Rechten nicht ausgeführt werden. ");
					$query = 'INSERT IGNORE INTO `right_issues_pages` (`user_id`, `page_id`, `date`) VALUES ('.esc($GLOBALS['logged_in_user_id']).', '.esc($page_id).', now())';
					rquery($query);
					right_issue("Der Vorfall wird gespeichert und der Administrator informiert. ");
				}
			}

			return $return;
		}
	}

	function create_rollen_array () {
		$rollen = array();
		$query = 'SELECT `id`, `name` FROM `role`';
		$result = rquery($query);
		while ($row = mysqli_fetch_row($result)) {
			$rollen[$row[0]] = array($row[0], $row[1]);
		}
		return $rollen;
	}

	function get_role_id_by_user ($name) {
		$key = "get_role_id_by_user($name)";
		if(array_key_exists($key, $GLOBALS['memoize'])) {
			$return = $GLOBALS['memoize'][$key];
		} else {
			$query = 'SELECT `role_id` FROM `role_to_user` `ru` LEFT JOIN `users` `u` ON `ru`.`user_id` = `u`.`id` WHERE `u`.`id` = '.esc($name);
			$result = rquery($query);

			$return = NULL;

			while ($row = mysqli_fetch_row($result)) {
				$return = $row[0];
			}
			$GLOBALS['memoize'][$key] = $return;
		}

		return $return;
	}

	function get_account_enabled_by_id ($id) {
		$query = 'select enabled from users where id = '.esc($id);

		$result = rquery($query);

		$id = NULL;

		while ($row = mysqli_fetch_row($result)) {
			$id = $row[0];
		}

		return $id;
	}

	function get_role_id ($name) {
		$query = 'SELECT `id` FROM `role` WHERE `name` = '.esc($name).' limit 1';
		$result = rquery($query);

		$id = NULL;

		while ($row = mysqli_fetch_row($result)) {
			$id = $row[0];
		}

		return $id;
	}

	function get_user_id ($name) {
		$query = 'SELECT `id` FROM `users` WHERE `username` = '.esc($name);
		$result = rquery($query);

		$id = NULL;

		while ($row = mysqli_fetch_row($result)) {
			$id = $row[0];
		}

		return $id;
	}

	function get_salt ($id) {
		$query = 'SELECT `salt` FROM `users` WHERE `id` = '.esc($id);
		$result = rquery($query);

		$id = NULL;

		while ($row = mysqli_fetch_row($result)) {
			$id = $row[0];
		}

		return $id;

	}

	function get_and_create_salt ($id) {
		if(!check_function_rights(__FUNCTION__)) { return; }

		$result = get_salt($id);

		if($result) {
			return $result;
		} else {
			$salt = generate_random_string(100);
			$query = 'UPDATE `users` SET `salt` = '.esc($salt).' WHERE `id` = '.esc($id);
			$results = rquery($query);

			if($results) {
				$id = get_salt($name, $studiengang);
				if($id) {
					message('Salt eingefügt. ');
					return $id;
				} else {
					message('Salt konnte nicht eingefügt werden. ');
					return null;
				}
			} else {
				die(mysqli_error());
			}
		}
	}

	function assign_page_to_role ($role_id, $page_id) {
		if(!check_function_rights(__FUNCTION__)) { return; }
		$query = 'INSERT IGNORE INTO `role_to_page` (`role_id`, `page_id`) VALUES ('.esc($role_id).', '.esc($page_id).')';
		$result = rquery($query);
		if($result) {
			success("Die Seite wurde erfolgreich zur Rolle hinzugefügt. ");
			if($GLOBALS['user_role_id'] == $role_id) {
				$GLOBALS['reload_page'] = 1;
			}
		} else {
			error("Die Seite konnte nicht zur Rolle hinzugefügt werden. ");
		}
	}

	function get_roles_for_page ($pageid) {
		$rollen = array();
		$query = 'SELECT `role_id` FROM `role_to_page` WHERE `page_id` = '.esc($pageid);
		$result = rquery($query);
		while ($row = mysqli_fetch_row($result)) {
			$rollen[] = $row[0];
		}
		return $rollen;
	}

	/*
		id ist die page-id
		role_to_page muss ein array sein mit ids von rollen, die der seite
		zugeordnet werden sollen
	 */
	function update_or_create_role_to_page ($id, $role_to_page) {
		if(!check_function_rights(__FUNCTION__)) { return; }

		if(isset($role_to_page) && !is_array($role_to_page)) {
			$temp = array();
			$temp[] = $role_to_page;
		}

		if(is_array($role_to_page) && count($role_to_page)) {
			$at_least_one_role_set = 0;
			foreach ($role_to_page as $trole) {
				$rname = get_role_name($trole);
				if($rname) {
					$at_least_one_role_set = 1;
				}
			}

			$roles_cleared = 0;
			if($at_least_one_role_set) {
				$query = 'DELETE FROM `'.$GLOBALS['dbname'].'`.`role_to_page` WHERE `page_id` = '.esc($id);
				$result = rquery($query);
				if($result) {
					success("Die Rollen wurden erfolgreich geklärt. ");
					$roles_cleared = 1;
				} else {
					error("Die Rollen wurden NICHT erfolgreich geklärt. ");
				}
			}

			if($roles_cleared) {
				foreach ($role_to_page as $trole) {
					$rname = get_role_name($trole);
					if($rname) {
						$query = 'INSERT IGNORE INTO `'.$GLOBALS['dbname'].'`.`role_to_page` (`role_id`, `page_id`) VALUES ('.esc($trole).', '.esc($id).')';
						$result = rquery($query);
						if($result) {
							success("Die Rolle $rname wurde erfolgreich hinzugefügt. ");
						} else {
							error("Die Rolle $rname konnte nicht eingefügt werden. ");
						}
					} else {
						error("Die Rolle mit der ID $trole existiert nicht. ");
					}
				}
			}
		}
	}

	function update_funktion_rights ($id, $name) {
		if(!check_function_rights(__FUNCTION__)) { return; }
		$query = 'UPDATE `function_rights` SET `name` = '.esc($name).' WHERE `id` = '.esc($id);
		$result = rquery($query);
		if($result) {
			success('Das Funktionsrecht wurde erfolgreich geändert.');
		} else {
			message('Das Funktionsrecht konnte nicht geändert werden oder es waren keine Änderungen notwendig.');
		}
	}

	function update_user ($name, $id, $password, $role, $enable, $accpubdata) {
		if(!check_function_rights(__FUNCTION__)) { return; }
		$salt = get_and_create_salt($id);
		$enabled = 1;
		if(!$enable) {
			$enabled = 0;
		}
		$query = '';
		if($password) {
			$query = 'UPDATE `users` SET `username` = '.esc($name).', `password_sha256` = '.esc(hash('sha256', $password.$salt)).', `enabled` = '.esc($enabled).', `accepted_public_data` = '.esc($accpubdata).' WHERE `id` = '.esc($id);
		} else {
			$query = 'UPDATE `users` SET `username` = '.esc($name).', `enabled` = '.esc($enabled).', `accepted_public_data` = '.esc($accpubdata).' WHERE `id` = '.esc($id);
		}
		$result = rquery($query);
		if($result) {
			$query = 'INSERT INTO `role_to_user` (`role_id`, `user_id`) VALUES ('.esc($role).', '.esc($id).') ON DUPLICATE KEY UPDATE `role_id` = '.esc($role);
			$result = rquery($query);
			if($result) {
				success('Die Benutzerdaten und Rollenzuordnungen wurden erfolgreich geändert. ');
			} else {
				success('Die Benutzerdaten wurden erfolgreich geändert, aber die Rollenänderung hat nicht geklappt. ');
			}
		} else {
			message('Die Benutzerdaten konnten nicht geändert werden oder es waren keine Änderungen notwendig.');
		}
		$GLOBALS['reload_page'] = 1;
	}

	function update_own_data ($password) {
		if(!check_function_rights(__FUNCTION__)) { return; }
		$salt = get_and_create_salt($GLOBALS['logged_in_user_id']);
		$query = 'UPDATE `users` SET `password_sha256` = '.esc(hash('sha256', $password.$salt)).' WHERE `id` = '.esc($GLOBALS['logged_in_user_id']);
		$result = rquery($query);
		if($result) {
			success('Ihr Passwort wurde erfolgreich geändert. ');
		} else {
			message('Die Benutzerdaten konnten nicht geändert werden oder es waren keine Änderungen notwendig.');
		}
	}

	function update_role ($id, $name) {
		if(!check_function_rights(__FUNCTION__)) { return; }
		$query = 'UPDATE `role` SET `name` = '.esc($name).' WHERE `id` = '.esc($id);;
		$result = rquery($query);
		if($result) {
			success('Die Rolle wurde erfolgreich geändert. ');
		} else {
			message('Die Rolle konnte nicht geändert werden oder es waren keine Änderungen notwendig. ');
		}
	}

	function create_user_array ($role = 0, $specific_role = null) {
		$user = array();
		if($role) {
			$query = 'SELECT `u`.`id`, `u`.`username`, `r`.`role_id` FROM `users` `u` JOIN `role_to_user` `r` ON `r`.`user_id` = `u`.`id`';
			if(isset($specific_role)) {
				$query .= ' WHERE `role_id` = '.esc($specific_role);
			}
			$result = rquery($query);
			while ($row = mysqli_fetch_row($result)) {
				$user[$row[0]] = array($row[0], $row[1], $row[2]);
			}
			return $user;
		} else {
			$query = 'SELECT `id`, `username` FROM `users`';
			$result = rquery($query);
			while ($row = mysqli_fetch_row($result)) {
				$user[$row[0]] = array($row[0], $row[1]);
			}
			return $user;
		}
	}

	function delete_funktion_rights ($id) {
		if(!check_function_rights(__FUNCTION__)) { return; }
		$query = 'DELETE FROM `function_rights` WHERE `id` = '.esc($id);
		$result = rquery($query);
		if($result) {
			success('Das Funktionsrecht wurde erfolgreich gelöscht.');
		} else {
			error('Das Funktionsrecht konnte nicht gelöscht werden.');
		}
	}

	function delete_role ($id) {
		if(!check_function_rights(__FUNCTION__)) { return; }
		$query = 'DELETE FROM `role` WHERE `id` = '.esc($id);
		$result = rquery($query);
		if($result) {
			success('Die Rolle wurde erfolgreich gelöscht.');
		} else {
			error('Die Rolle konnte nicht gelöscht werden.');
		}
	}

	function delete_user ($id) {
		if(!check_function_rights(__FUNCTION__)) { return; }
		$query = 'DELETE FROM `users` WHERE `id` = '.esc($id);
		$result = rquery($query);
		if($result) {
			success('Der Benutzer wurde erfolgreich gelöscht.');
		} else {
			error('Der Benutzer konnte nicht gelöscht werden.');
		}
	}

	function create_function_right ($role_id, $name) {
		if(!check_function_rights(__FUNCTION__)) { return; }
		$query = 'INSERT IGNORE INTO `function_rights` (`name`, `role_id`) VALUES ('.esc($name).', '.esc($role_id).')';
		$result = rquery($query);
		if($result) {
			success('Das Funktionsrecht wurde erfolgreich eingetragen.');
		} else {
			error('Das Funktionsrecht konnte nicht eingetragen werden.');
		}
	}

	function create_role($role) {
		if(!check_function_rights(__FUNCTION__)) { return; }
		$query = 'INSERT IGNORE INTO `role` (`name`) VALUES ('.esc($role).')';
		$result = rquery($query);
		if($result) {
			success('Die Rolle wurde erfolgreich eingetragen.');
		} else {
			error('Die Rolle konnte nicht eingetragen werden.');
		}
	}

	function create_user($name, $password, $role) {
		if(!check_function_rights(__FUNCTION__)) { return; }
		$salt = generate_random_string(100);
		$query = 'INSERT IGNORE INTO `users` (`username`, `password_sha256`, `salt`) VALUES ('.esc($name).', '.esc(hash('sha256', $password.$salt)).', '.esc($salt).')';
		$result = rquery($query);
		if($result) {
			$id = get_user_id($name);
			$query = 'INSERT IGNORE INTO `role_to_user` (`role_id`, `user_id`) VALUES ('.esc($role).', '.esc($id).')';
			$result = rquery($query);

			if($result) {
				success('Der User wurde mit seiner Rolle erfolgreich eingetragen.');
			} else {
				error('Der User konnte eingefügt, aber nicht seiner Rolle zugeordnet werden. ');
			}
		} else {
			error('Der User konnte nicht eingetragen werden. ');
		}
	}

	function get_role_name ($id) {
		$query = 'SELECT `name` FROM `role` WHERE `id` = '.esc($id).' limit 1';
		$result = rquery($query);

		$id = NULL;

		while ($row = mysqli_fetch_row($result)) {
			$id = $row[0];
		}

		return $id;
	}

	function get_user_name ($id) {
		$query = 'SELECT `username` FROM `users` WHERE `id` = '.esc($id);
		$result = rquery($query);

		$name = NULL;

		while ($row = mysqli_fetch_row($result)) {
			$name = $row[0];
		}

		return $name;
	}
?>
