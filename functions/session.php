<?php
	function delete_old_session_ids ($user_id = null) {
		if($GLOBALS['already_deleted_old_session_ids']) {
			return;
		}

		$query = 'DELETE FROM `session_ids` WHERE `creation_time` <= now() - INTERVAL 1 DAY';
		rquery($query);
		if($user_id) {
			$query = 'DELETE FROM `session_ids` WHERE `user_id` = '.esc($user_id);
			rquery($query);
		}
		$GLOBALS['already_deleted_old_session_ids'] = 1;
	}


?>
