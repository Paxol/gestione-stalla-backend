<?php
require_once(__DIR__ . '/../include/common.php');
require_once(__DIR__ . '/../include/db.php');

function get_codici_bovini_in_stalla()
{
  try {
    // Connessione al DB e query
    $db = Database::get_instance();
    $codici = $db->query("SELECT `Codice` AS `codice`,`Madre` AS `madre`,`DataNascita` AS `dataNascita`,`Sesso` AS `sesso` FROM `Bovino` WHERE `Destinazione` IS NULL")->fetch_all();

    // Invio risposta
    return array(
      "status" => "ok",
      "data" => $codici
    );
  } catch (\Throwable $th) {
    // Gestione degli errori
    return build_db_error_response($db, $th);
  }
}