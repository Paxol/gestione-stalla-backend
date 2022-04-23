<?php
require_once(__DIR__ . '/../include/common.php');
require_once(__DIR__ . '/../include/db.php');

function get_nascite_non_registrate()
{
  try {
    // Connessione al DB e query
    $db = Database::get_instance();
    $nascite = $db->query("SELECT `F`.`Codice` AS `codiceMadre`, `P`.`ID` AS `id`, `P`.`Data` AS `data` FROM `Parto` AS `P` JOIN `Fecondazione` AS `F` ON `P`.`ID` = `F`.`Parto` WHERE `P`.`CodiceFiglio` IS NULL;")->fetch_all();

    // Invio risposta
    return array(
      "status" => "ok",
      "data" => $nascite
    );
  } catch (\Throwable $th) {
    // Gestione degli errori
    return build_db_error_response($db, $th);
  }
}