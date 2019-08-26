<?php
	if($GLOBALS['logged_in']) { // Wichtig, damit Niemand ohne Anmeldung etwas ändern kann
		// Falls eine ID gegeben ist, dann sind bereits Daten vorhanden, die editiert oder gelöscht werden sollen.
		if(!is_null(get_post('id')) || !is_null(get_get('id'))) {
			$this_id = get_post('id');
			if(!$this_id) {
				$this_id = get_get('id');
			}
			if(get_post('delete') && !get_post('delete_for_sure')) {
				/*
					Festlegung der Tabellen, aus denen etwas gelöscht werden soll.
				 */
				$GLOBALS['deletion_page'] = 1;

				$GLOBALS['deletion_where'] = array('id' => $this_id);

				if(get_post('funktion_name')) {
					$GLOBALS['deletion_db'] = 'function_rights';
				}

				if(get_post('update_status')) {
					$GLOBALS['deletion_db'] = 'status';
				}

				if(get_post('update_turnus')) {
					$GLOBALS['deletion_db'] = 'turnus';
				}

				if(get_post('update_anlage')) {
					$GLOBALS['deletion_db'] = 'anlagen';
				}

				if(get_post('update_kunde')) {
					$GLOBALS['deletion_db'] = 'kunden';
				}

				if(get_post('update_wartungstermine')) {
					$GLOBALS['deletion_db'] = 'kunden';
				}

				if(get_post('neue_rolle') && get_post('page')) {
					$GLOBALS['deletion_db'] = 'role';
				}

				if(get_post('name') && get_post('id') && get_post('role')) {
					$GLOBALS['deletion_db'] = 'users';
				}

				if(get_post('updatepage') && get_post('id')) {
					$GLOBALS['deletion_db'] = 'page';
				}
			} else {
				if(get_post('update_anlage') && get_post('delete') && get_post('id')) {
					delete_anlage(get_post('id'));
				}

				foreach ($_POST as $this_post_key => $this_post_value) {
					if (preg_match('/^update_anlage_(\d+)_for_(\d+)$/', $this_post_key, $founds)) {
						$anlage_id = $founds[1];
						$kunde_id = $founds[2];
						$name = get_post($founds[0]);

						$turnus = get_post('turnus');
						$ibn_beendet_am = get_post('ibn_beendet_am');
						$letzte_wartung = get_post('letzte_wartung');
						$ende_gewaehrleistung = get_post('ende_gewaehrleistung');

						update_anlage($anlage_id, $name, $turnus, $ibn_beendet_am, $letzte_wartung, $ende_gewaehrleistung);
					}
				}

				if(get_post('update_status')) {
					$id = get_post('id');
					if(!get_post('delete')) {
						$name = get_post('name');
						$color = get_post('color');
						update_status($id, $name, $color);
					} else {
						delete_status($id);
					}
				}

				if(get_post('update_turnus')) {
					$id = get_post('id');
					if(!get_post('delete')) {
						$name = get_post('name');
						$anzahl_monate = get_post('anzahl_monate');
						update_turnus($id, $name, $anzahl_monate);
					} else {
						delete_turnus($id);
					}
				}

				if(get_post('newpage')) {
					$titel = get_post('titel');
					$datei = get_post('datei');
					$show_in_navigation = get_post('show_in_navigation') ? 1 : 0;
					$eltern = get_post('eltern') ? get_post('eltern') : '';
					$role_to_page = get_post('role_to_page');
					$beschreibung = get_post('beschreibung') ? get_post('beschreibung') : '';
					$hinweis = get_post('hinweis') ? get_post('hinweis') : '';

					if(isset($titel) && isset($datei) && isset($show_in_navigation) && isset($eltern) && isset($role_to_page) && isset($beschreibung) && isset($hinweis)) {

						create_new_page($titel, $datei, $show_in_navigation, $eltern, $role_to_page, $beschreibung, $hinweis);
					} else {
						error('Missing parameters!');
					}
				}

				if(get_post('updatepage')) {
					$id = get_post('id');
					if(get_post('delete')) {
						if(isset($id)) {
							delete_page($id);
						}
					} else {
						$titel = get_post('titel');
						$datei = get_post('datei');
						$show_in_navigation = get_post('show_in_navigation') ? 1 : 0;
						$eltern = get_post('eltern') ? get_post('eltern') : '';
						$role_to_page = get_post('role_to_page');
						$beschreibung = get_post('beschreibung') ? get_post('beschreibung') : '';
						$hinweis = get_post('hinweis') ? get_post('hinweis') : '';

						if(isset($id) && isset($titel) && isset($datei) && isset($show_in_navigation) && isset($eltern) && isset($role_to_page) && isset($beschreibung) && isset($hinweis)) {

							update_page_full($id, $titel, $datei, $show_in_navigation, $eltern, $role_to_page, $beschreibung, $hinweis);
						} else {
							error('Missing parameters!');
						}
					}
				}

				if(get_post('funktion_name')) {
					if(get_post('delete')) {
						delete_funktion_rights($this_id);
					} else {
						update_funktion_rights($this_id, get_post('funktion_name'));
					}
				}

				if(get_post('update_page_info')) {
					update_page_info(get_post('id'), get_post('info'));
				}

				if(get_post_multiple_check(array('name', 'plz', 'ort')) && !get_post('delete')) {
					$name = get_post('name');
					$plz = get_post('plz');
					$ort = get_post('ort');
					$strasse = get_post('strasse');
					$hausnummern = get_post('hausnummern');
					$ibn_beendet_am = get_post('ibn_beendet_am');
					$turnus = get_post('turnus');
					$letzte_wartung = get_post('letzte_wartung');
					create_kunde($name, $plz, $ort, $strasse, $hausnummern, $ibn_beendet_am, $turnus, $letzte_wartung);
					#create_user(get_post('name'), get_post('password'), get_post('role'), get_post('dozent'), get_post('institut'), $barrierefrei);
				} else if (get_post('new_user')) {
					warning('Benutzer müssen einen Namen, ein Passwort und eine Rolle haben. ');
				}

				if(get_post('neue_rolle') && get_post('page')) {
					if(get_post('delete')) {
						delete_role($this_id);
					} else {
						update_role($this_id, get_post('neue_rolle'));
						$query = 'DELETE FROM `role_to_page` WHERE `role_id` = '.esc(get_role_id(get_post('neue_rolle')));;
						rquery($query);
						foreach (get_post('page') as $key => $this_page_id) {
							if(preg_match('/^\d+$/', $this_page_id)) {
								assign_page_to_role(get_role_id(get_post('neue_rolle')), $this_page_id);
							}
						}
					}
				}

				if(get_post('id') && get_post('update_kunde')) {
					if(get_post('delete')) {
						delete_kunde(get_post('id'));
					} else {
						$id = get_post("id");
						$name = get_post("name");
						$plz = get_post("plz");
						$ort = get_post("ort");
						$strasse = get_post("strasse");
						$hausnummern = get_post("hausnummern");
						$ibn_beendet_am = get_post("ibn_beendet_am");
						$turnus = get_post("turnus");
						$letzte_wartung = get_post("letzte_wartung");
						$erinnerung = get_post('erinnerung');
						$pruefung = get_post('pruefung');
						$pruefung_abgelehnt = get_post("pruefung_abgelehnt");

						update_kunde($id, $name, $plz, $ort, $strasse, $hausnummern, $erinnerung, $pruefung, $pruefung_abgelehnt);
					}
				}

				if(get_post('name') && get_post('id') && get_post('role')) {
					if(get_post('delete')) {
						delete_user($this_id);
					} else {
						$enabled = get_account_enabled_by_id($this_id);
						if(get_post('disable_account')) {
							$enabled = 0;
						}

						if(get_post('enable_account')) {
							$enabled = 1;
						}

						$accpubdata = 1;
						if(get_post('accepted_public_data')) {
							$accpubdata = 1;
						}

						update_user(get_post('name'), get_post('id'), get_post('password'), get_post('role'), $enabled, $accpubdata);
					}
				}
			}
		} else {
			foreach ($_POST as $this_post_key => $this_post_value) {
				if(preg_match('/^create_anlage_for_(\d+)/', $this_post_key, $founds)) {
					$name = get_post($founds[0]);
					$kunde_id = $founds[1];

					$turnus_id = get_post('turnus');
					$ibn_beendet_am = get_post('ibn_beendet_am');
					$letzte_wartung = get_post('letzte_wartung');
					$ende_gewaehrleistung = get_post('ende_gewaehrleistung');

					create_anlage($kunde_id, $name, $turnus_id, $ibn_beendet_am, $letzte_wartung, $ende_gewaehrleistung);
				} else if (preg_match('/^update_anlage_(\d+)_for_(\d+)$/', $this_post_key, $founds)) {
					$anlage_id = $founds[1];
					$kunde_id = $founds[2];
					$name = get_post($founds[0]);

					$turnus = get_post('turnus');
					$ibn_beendet_am = get_post('ibn_beendet_am');
					$letzte_wartung = get_post('letzte_wartung');
					$ende_gewaehrleistung = get_post('ende_gewaehrleistung');

					update_anlage($anlage_id, $name, $turnus_id, $ibn_beendet_am, $letzte_wartung, $ende_gewaehrleistung);
				}
			}

			if(get_get('update_wartungstermine')) {
				$kunde_id = get_get('kunde_id');
				$anlage_id = get_get('anlage_id');
				if(preg_match('/^\d+$/', $kunde_id)) {
					$termine = array();
					$kommentare = array();
					$kommentare2 = array();

					foreach ($_GET as $this_get_key => $this_get_value) {
						if(preg_match('/(kommentar2?|termin)_(\d+)_(\d+)_anlage_(\d+)/', $this_get_key, $founds)) {
							$type = $founds[1];
							$year = $founds[2];
							$month = $founds[3];
							$anlage = $founds[4];
							if($type == 'termin') {
								if(preg_match('/^\d+$/', $this_get_value)) {
									$termine[$year][$month][$anlage] = $this_get_value;
								} else {
									error('Invalider Datentyp für Status!');
								}
							} else if ($type == 'kommentar') {
								if(preg_match('/.+/', $this_get_value)) {
									$kommentare[$year][$month][$anlage] = $this_get_value;
								}
							} else if ($type == 'kommentar2') {
								if(preg_match('/^.+/', $this_get_value)) {
									$kommentare2[$year][$month][$anlage] = $this_get_value;
								}

							}
						}
					}

					if(count($termine)) {
						update_wartungstermine($anlage_id, $termine, $kommentare, $kommentare2, $kunde_id);
					} else {
						warning("ACHTUNG: update_wartungstermine wird nicht ausgeführt, weil keine \$termine gefunden werden konnten! Bitte melde mir das!");
					}
				} else {
					error('Invalide Kunden-ID!');
				}
			}

			if(get_post('create_status')) {
				create_status(get_post('new_name'), get_post('new_color'));
			}

			if(get_post('create_turnus')) {
				create_turnus(get_post('new_name'), get_post('new_anzahl_monate'));
			}

			if(get_post('merge_data')) {
				if(get_get('table') && get_post('merge_from') && get_post('merge_to')) {
					merge_data(get_get('table'), get_post('merge_from'), get_post('merge_to'));
				} else {
					error(' Sowohl eine bzw. mehrere Quelle als auch ein Zielort müssen angegeben werden.');
				}
			}

			if(get_post('new_function_right')) {
				$role_id = get_post('role_id');
				if($role_id) {
					$funktion_name = get_post('funktion_name');
					if($funktion_name) {
						create_function_right($role_id, $funktion_name);
					} else {
						error('Die Funktion konnte nicht angelegt werden, da sie keinen validen Namen zugeordnet bekommen hat. ');
					}
				} else {
					error('Die Funktion konnte nicht angelegt werden, da sie keiner Rolle zugeordnet wurden ist. ');
				}
			}

			if(get_post('import_datenbank')) {
				if(array_key_exists('sql_file', $_FILES) && array_key_exists('tmp_name', $_FILES['sql_file'])) {
					SplitSQL($_FILES['sql_file']['tmp_name']);
				}
			}

			if(get_post('datenbankvergleich')) {
				if(array_key_exists('sql_file', $_FILES) && array_key_exists('tmp_name', $_FILES['sql_file'])) {
					$GLOBALS['compare_db'] = compare_db($_FILES['sql_file']['tmp_name']);
				}
			}

			if(get_post('change_own_data')) {
				$new_password = get_post('password');
				$new_password_repeat = get_post('password_repeat');
				if($new_password && strlen($new_password) >= 5) {
					if($new_password == $new_password_repeat) {
						update_own_data($new_password);
					} else {
						error('Beide Passworteingaben müssen identisch sein. ');
					}
				} else {
					$GLOBALS['error'] = 'Das Passwort muss mindestens 5 Zeichen haben. ';
				}
			}

			if(get_post('startseitentext')) {
				$startseitentext = get_post('startseitentext');
				update_startseitentext($startseitentext);
			}

			if(get_post('update_text') && get_post('page_id')) {
				update_text(get_post('page_id'), get_post('text'));
			}

			if(get_post('update_hinweis') && get_post('page_id')) {
				update_hinweis(get_post('page_id'), get_post('hinweis'));
			}

			if(get_post_multiple_check(array('new_user', 'name', 'password', 'role'))) {
				create_user(get_post('name'), get_post('password'), get_post('role'));
			} else if (get_post('new_user')) {
				warning('Benutzer müssen einen Namen, ein Passwort und eine Rolle haben. ');
			}

			if(get_post('create_new_kunde')) {
				$name = get_post('name');
				$plz = get_post('plz');
				$ort = get_post('ort');
				$strasse = get_post('strasse');
				$hausnummern = get_post('hausnummern');
				$ibn_beendet_am = get_post('ibn_beendet_am');
				$turnus = get_post('turnus');
				$letzte_wartung = get_post('letzte_wartung');

				$erinnerung = get_post('erinnerung');
				$pruefung = get_post('pruefung');
				$pruefung_abgelehnt = get_post('pruefung_abgelehnt');

				create_kunde($name, $plz, $ort, $strasse, $hausnummern, $ibn_beendet_am, $turnus, $letzte_wartung, $erinnerung, $pruefung, $pruefung_abgelehnt);
			}

			if(get_post('neue_rolle') && get_post('page')) {
				create_role(get_post('neue_rolle'));
				// Alle alten Rollendaten löschen
				$query = 'DELETE FROM `role_to_page` WHERE `role_id` = '.esc(get_role_id(get_post('neue_rolle')));
				rquery($query);
				foreach (get_post('page') as $key => $this_page_id) {
					if(preg_match('/^\d+$/', $this_page_id)) {
						assign_page_to_role(get_role_id(get_post('neue_rolle')), $this_page_id);
					}
				}
			}
		}
	}

?>
