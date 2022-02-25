<?php
require_once(__DIR__ . '/../include/common.php');
require_once(__DIR__ . '/../include/db.php');

function get_codici_stalle()
{
  try {
    // Connessione al DB e query
    $db = Database::get_instance();
    $codici_stalle = $db->query("SELECT `ID` AS `id`, `Codice` AS `codice`, `Descrizione` AS `descrizione` FROM `Luogo`")->fetch_all();

    // Invio risposta
    return array(
      "status" => "ok",
      "data" => $codici_stalle
    );
  } catch (\Throwable $th) {
    // Gestione degli errori
    return build_db_error_response($db, $th);
  }
}