<?php
require_once(__DIR__ . '/../include/common.php');
require_once(__DIR__ . '/../include/db.php');

function get_bovini_da_fecondare()
{
  try {
    // Connessione al DB e query
    $db = Database::get_instance();
    $codici = $db->query("SELECT `Codice` AS `codice` FROM `Bovino` WHERE `Codice` NOT IN (SELECT `Codice` FROM `Fecondazione` WHERE (`Riuscita` = 1 AND `Parto` IS NULL) OR (`Riuscita` IS NULL))")->fetch_all();

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