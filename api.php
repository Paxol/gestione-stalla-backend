<?php
error_reporting(E_ERROR | E_PARSE);
require_once(__DIR__ . '/actions/index.php');

header('Access-Control-Allow-Origin: *');

function send_response($res)
{
	header('Content-Type: application/json; charset=utf-8');
	echo json_encode($res);
	exit;
}

switch ($_REQUEST["action"]) {
	case 'conferma-fecondazione':
		send_response(conferma_fecondazione());
		break;
	case 'get-bovini-da-fecondare':
		send_response(get_bovini_da_fecondare());
		break;
	case 'get-codici-bovini-in-stalla':
		send_response(get_codici_bovini_in_stalla());
		break;
	case 'get-codici-stalle':
		send_response(get_codici_stalle());
		break;
	case 'get-fecondazioni-da-confermare':
		send_response(get_fecondazioni_da_confermare());
		break;
	case 'registra-fecondazione':
		send_response(registra_fecondazione());
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
