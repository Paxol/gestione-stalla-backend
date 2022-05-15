<?php
require_once(__DIR__ . '/../include/common.php');
require_once(__DIR__ . '/../include/db.php');

function get_notifica_fecondazioni_da_confermare()
{

  try {
    // Connessione al DB e query
    $db = Database::get_instance();
    $codici = $db->query("SELECT `ID` AS `id`, `Codice` AS `codice`, `Data` AS `data` FROM `Fecondazione` WHERE `Riuscita` IS NULL AND DATE_ADD(`Data`, INTERVAL 20 DAY) < now();")->fetch_all();

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