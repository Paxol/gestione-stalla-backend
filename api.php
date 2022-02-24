<?php
error_reporting(E_ERROR | E_PARSE);
require_once(__DIR__ . '/include/db.php');
require_once(__DIR__ . '/actions/index.php');

header('Access-Control-Allow-Origin: *');

function send_response($res)
{
	header('Content-Type: application/json; charset=utf-8');
	echo json_encode($res);
	exit;
}

function handle_db_error($db, $th)
{
	if ($db == NULL) {
		send_response(array(
			"error" => "Database connection error",
			"message" => $th->getMessage()
		));
	} else {
		$err = $db->get_last_error();
		send_response(array(
			"error" => $err[0],
			"message" => $err[1]
		));
	}
}

$db;
switch ($_REQUEST["action"]) {
	case 'get-codici-bovini-in-stalla':
		try {
			// Connessione al DB e query
			$db = Database::get_instance();
			$codici = $db->query("SELECT `Codice` AS `codice`,`Madre` AS `madre`,`DataNascita` AS `dataNascita`,`Sesso` AS `sesso` FROM `Bovino` WHERE `Destinazione` IS NULL")->fetch_all();

			// Invio risposta
			send_response(array(
				"status" => "ok",
				"data" => $codici
			));
		} catch (\Throwable $th) {
			// Gestione degli errori
			handle_db_error($db, $th);
		}
		break;
	case 'get-codici-stalle':
		try {
			// Connessione al DB e query
			$db = Database::get_instance();
			$codici_stalle = $db->query("SELECT `ID` AS `id`, `Codice` AS `codice`, `Descrizione` AS `descrizione` FROM `Luogo`")->fetch_all();

			// Invio risposta
			send_response(array(
				"status" => "ok",
				"data" => $codici_stalle
			));
		} catch (\Throwable $th) {
			// Gestione degli errori
			handle_db_error($db, $th);
		}
		break;
	case 'registra-modello-4':
		send_response(registra_modello_4());
		break;

	default:
		// Risposta di default
		send_response(array(
			"error" => "Unknown action"
		));
		break;
}
