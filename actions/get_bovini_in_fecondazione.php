<?php
require_once(__DIR__ . '/../include/common.php');
require_once(__DIR__ . '/../include/db.php');

function get_bovini_in_fecondazione()
{
  try {
    // Connessione al DB e query
    $db = Database::get_instance();
    $data = $db->query("SELECT `Codice` AS `codice`, `Data` AS `data`, `Riuscita` AS `riuscita`, `InAsciutta` AS `inAsciutta` FROM `Fecondazione` WHERE `Riuscita` IS NULL OR `Parto` IS NULL ORDER BY `data` ASC;")->fetch_all();

    // Invio risposta
    return array(
      "status" => "ok",
      "data" => $data
    );
  } catch (\Throwable $th) {
    // Gestione degli errori
    return build_db_error_response($db, $th);
  }
}