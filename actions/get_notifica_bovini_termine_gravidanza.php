<?php
require_once(__DIR__ . '/../include/common.php');
require_once(__DIR__ . '/../include/db.php');

function get_notifica_bovini_termine_gravidanza()
{
  try {
    // Connessione al DB e query
    $db = Database::get_instance();
    
    // 250 giorni => 8 mesi e 10 gioni
    $codici = $db->query("SELECT `ID` AS `id`, `Codice` AS `codice`, `Data` AS `data` FROM `Fecondazione` WHERE `Riuscita` = 1 AND `Parto` IS NULL AND DATE_ADD(`Data`, INTERVAL 260 DAY) < now();")->fetch_all();

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