<?php
require_once(__DIR__ . '/../include/common.php');
require_once(__DIR__ . '/../include/db.php');

function get_notifica_bovini_da_fecondare()
{
  try {
    // Connessione al DB e query
    $db = Database::get_instance();
    $codici = $db->query("SELECT `B`.`Codice` AS `codice`, `B`.`DataNascita` AS `dataNascita` FROM `Bovino` AS `B` WHERE `B`.`Codice` NOT IN (SELECT `Codice` FROM `Fecondazione` AS `F` LEFT JOIN `Parto` AS `P` ON `F`.`Parto` = `P`.`ID` WHERE (DATE_ADD(`P`.`Data`, INTERVAL 2 MONTH) > now()) OR (`F`.`Riuscita` IS NULL OR `F`.`Parto` IS NULL)) AND DATE_ADD(`B`.`DataNascita`, INTERVAL 16 MONTH) < now();")->fetch_all();

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