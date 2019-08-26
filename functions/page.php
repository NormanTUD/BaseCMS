<?php
	function create_new_page ($name, $file, $show_in_navigation, $parent, $role_to_page, $beschreibung, $hinweis) {
		if(!check_function_rights(__FUNCTION__)) { return; }
		if($parent == "") {
			$parent = null;
		}
		$query = 'INSERT IGNORE INTO `'.$GLOBALS['dbname'].'`.`page` (`name`, `file`, `show_in_navigation`, `parent` ) VALUES ('.esc(array($name, $file, $show_in_navigation, $parent)).')';
		$result = rquery($query);
		if($result) {
			$id = null;
			$idquery = 'SELECT LAST_INSERT_ID()';
			$result = rquery($idquery);
			while ($row = mysqli_fetch_row($result)) {
				$id = $row[0];
			}

			if($id) {
				if(isset($role_to_page)) {
					update_or_create_role_to_page($id, $role_to_page);
				}

				if(isset($beschreibung)) {
					update_page_info($id, $beschreibung);
				}

				if(isset($hinweis)) {
					update_hinweis($id, $hinweis);
				}

				success('Die Seite wurde erfolgreich hinzugefügt. ');
			} else {
				error('Die letzte insert-id konnte nicht ermittelt werden, aber die Seite wurde erstellt. ');
			}
		} else {
			message('Die Seite konnte nicht erfolgreich hinzugefügt werden. ');
		}
	}

	function update_page_full($id, $name, $file, $show_in_navigation, $parent, $role_to_page, $beschreibung, $hinweis) {
		if(!check_function_rights(__FUNCTION__)) { return; }
		if($parent == "") {
			$parent = null;
		}
		$query = 'UPDATE `page` SET `name` = '.esc($name).', `file` = '.esc($file).', `show_in_navigation` = '.esc($show_in_navigation).', `parent` = '.esc($parent).' WHERE `id` = '.esc($id);
		$result = rquery($query);
		if($result) {
			if(isset($role_to_page)) {
				update_or_create_role_to_page($id, $role_to_page);
			}

			if(isset($beschreibung)) {
				update_page_info($id, $beschreibung);
			}

			if(isset($hinweis)) {
				update_hinweis($id, $hinweis);
			}

			success('Die Seite wurde erfolgreich geändert. ');
		} else {
			message('Die Seite konnte nicht geändert werden oder es waren keine Änderungen notwendig. ');
		}
	}

	function update_startseitentext ($startseitentext) {
		if(!check_function_rights(__FUNCTION__)) { return; }
		$query = '';
		if(get_startseitentext()) {
			$query = 'UPDATE `startseite` SET `text` = '.esc($startseitentext);;
		} else {
			$query = 'INSERT INTO `startseite` (`text`) VALUES ('.esc($startseitentext).');';
		}
		$result = rquery($query);

		if($result) {
			success('Startseitentext erfolgreich editiert. ');
		} else {
			success('Startseitentext konnte nicht editiert werden. ');
		}
	}

	function update_hinweis ($page_id, $hinweis) {
		if(!check_function_rights(__FUNCTION__)) { return; }
		if(get_page_name_by_id($page_id)) {
			if($hinweis) {
				$query = 'INSERT INTO `hinweise` (`page_id`, `hinweis`) VALUES ('.esc($page_id).', '.esc($hinweis).') ON DUPLICATE KEY UPDATE `hinweis` = '.esc($hinweis);
				$result = rquery($query);
				if($result) {
					success('Der neue Hinweis wurde erfolgreich geändert.');
				} else {
					message('Der Hinweis konnte nicht geändert werden oder es waren keine Änderungen notwendig.');
				}
			} else {
				message("Leerer Hinweis. ");
			}
		} else {
			error("Falsche Page-ID. ");
		}
	}

	function update_page_info ($id, $info) {
		if(!check_function_rights(__FUNCTION__)) { return; }
		$query = 'INSERT INTO `page_info` (`page_id`, `info`) VALUES ('.esc($id).', '.esc($info).') ON DUPLICATE KEY UPDATE `info` = '.esc($info);
		$result = rquery($query);
		if($result) {
			success('Die Seiteninfo wurde erfolgreich geändert.');
		} else {
			message('Die Seiteninfo konnte nicht geändert werden oder es waren keine Änderungen notwendig.');
		}
	}

	function update_text ($page_id, $text) {
		if(!check_function_rights(__FUNCTION__)) { return; }
		$query = 'INSERT INTO `seitentext` (`page_id`, `text`) VALUES ('.esc($page_id).', '.esc($text).') ON DUPLICATE KEY UPDATE `text` = '.esc($text);
		$result = rquery($query);
		if($result) {
			success('Der Seitentext wurde erfolgreich geändert.');
		} else {
			message('Der Seitentext konnte nicht geändert werden oder es waren keine Änderungen notwendig.');
		}
	}

	function delete_page ($id) {
		if(!check_function_rights(__FUNCTION__)) { return; }
		$query = 'DELETE FROM `page` WHERE `id` = '.esc($id);
		$result = rquery($query);
		if($result) {
			success('Die Seite wurde erfolgreich gelöscht.');
		} else {
			error('Die Seite konnte nicht gelöscht werden.');
		}
	}
?>
