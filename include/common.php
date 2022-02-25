<?php
function build_db_error_response($db, $th)
{
  if ($db == NULL) {
		return array(
			"error" => "Database connection error",
			"message" => $th->getMessage()
		);
	} else {
		$err = $db->get_last_error();
		return array(
			"error" => $err[0],
			"message" => $err[1]
		);
	}
}