<?php
	function enum_to_array($database, $table, $field) {    
		$query = "SHOW FIELDS FROM `{$database}`.`{$table}` LIKE '{$field}'";
		$result = rquery($query);
		$enum = NULL;
		while ($row = mysqli_fetch_row($result)) {
			preg_match('#^enum\((.*?)\)$#ism', $row[1], $matches);
			$enum = str_getcsv($matches[1], ",", "'");
		}
		return $enum;
	}

	function create_select_str ($data, $chosen, $name, $allow_empty = 0) {
		$str = '<select name="'.htmlentities($name).'">';
		if($allow_empty) {
			$str .= '<option value="">&mdash;</option>';
		}
		foreach ($data as $datum) {
			if(is_array($datum)) {
				$str .= '<option value="'.$datum[0].'"'.(($chosen && $datum[0] == $chosen) ? ' selected' : '').'>'.htmlentities($datum[1]).'</option>';
			} else {
				$str .= '<option value="'.$datum.'" '.(($chosen && $datum == $chosen) ? ' selected' : '').'>'.htmlentities($datum).'</option>';
			}
		}
		$str .= '</select>';
		return $str;
	}

	function create_select ($data, $chosen, $name, $allow_empty = 0) {
?>
		<select name="<?php print htmlentities($name); ?>">
<?php
			if($allow_empty) {
?>
				<option value="">&mdash;</option>
<?php
			}
			foreach ($data as $datum) {
				if(is_array($datum)) {
?>
					<option value="<?php print $datum[0]; ?>" <?php print ($chosen && $datum[0] == $chosen) ? 'selected' : ''; ?>><?php print htmlentities($datum[1]); ?></option>
<?php
				} else {
?>
					<option value="<?php print $datum; ?>" <?php print ($chosen && $datum == $chosen) ? 'selected' : ''; ?>><?php print htmlentities($datum); ?></option>
<?php
				}
			}
?>
		</select>
<?php
	}

	function simple_edit ($columnnames, $table, $columns, $page, $datanames, $block_user_id, $htmlentities = 1, $special_input = array(), $order_by = null, $classes = array()) {
		$query = 'SELECT `id`, `'.join('`, `', $columnnames).'` FROM `'.$table.'`';
		if($order_by) {
			$query .= ' ORDER BY `'.join('`, `', $order_by).'`';
		}
		$result = rquery($query);

?>
			<table>
				<tr>
<?php
				foreach ($columns as $c) {
?>
					<th><?php print $c ?></th>
<?php
				}
?>
				<tr>
<?php
		while($row = mysqli_fetch_row($result)) {
?>
			<tr>
				<form class="form" method="post" action="<?php $GLOBALS['adminpage']; ?>?page=<?php print htmlentities($GLOBALS['this_page_number']); ?>">
					<input type="hidden" name="update_<?php print $table; ?>" value="1" />
<?php
					$i = 0;
					foreach ($datanames as $c) {
						if(!is_null($special_input) && is_array($special_input) && array_key_exists($i, $special_input)) {
							print $special_input[$i];
						} else {
							if($i == 0) {
?>
								<input type="hidden" value="<?php print htmlentities($row[0]); ?>" name="<?php print htmlentities($datanames[0]); ?>" />
<?php
							} else {
								$class = '';
								if(array_key_exists($i, $classes)) {
									$class = " class='".$classes[$i]."'";
								}
?>
								<td><input <?php print $class; ?> style="width: 500px;" type="<?php print $c == 'password' ? 'password' : 'text'; ?>" name="<?php print $c; ?>" placeholder="<?php print $c; ?>" value="<?php print $c == 'password' ? '' : ($htmlentities ? htmlentities($row[$i]) : $row[$i]); ?>" /></td>
<?php
							}
						}
						$i++;
					}
?>
					<td><input type="submit"  value="Speichern" /></td>
<?php
					if($block_user_id && $GLOBALS['logged_in_data'][0] == $row[0]) {
?>
						<td><button name="delete" value="1" disabled>Löschen</button></td>
<?php
					} else {
?>
						<td><input type="submit" name="delete" value="Löschen" /></td>
<?php
					}
?>
				</form>
			</tr>
<?php
		}
?>
			<tr>
				<form class="form" method="post" action="<?php $GLOBALS['adminpage']; ?>?page=<?php print htmlentities($GLOBALS['this_page_number']); ?>">
					<input type="hidden" name="create_<?php print $table; ?>" value="1" />
<?php
					$i = 0;
					foreach ($datanames as $c) {
						if($i != 0) {
?>
							<td><input type="<?php print $c == 'password' ? 'password' : 'text'; ?>" name="new_<?php print $c; ?>" placeholder="<?php print $c; ?>" /></td>
<?php
						}
						$i++;
					}
?>
					<td><input type="submit" class="submit" value="Speichern" /></td>
					<td>&mdash;</td>
				</form>
			</tr>
		</table>
<?php
	}

	function convert_anything_to_boolean ($anything) {
		if($anything) {
			return 1;
		} else {
			return 0;
		}
	}
?>
