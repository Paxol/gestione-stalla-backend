<?php
require_once(__DIR__ . '/../include/common.php');
require_once(__DIR__ . '/../include/db.php');

function valida_nascita($input)
{
  $valid = gettype($input->idParto) == "integer";
  if (!$valid) {
    return [false, "Errore nella validazione dei dati"];
  }

  $valid = gettype($input->codice) == "string" && strlen($input->codice) == 14;
  if (!$valid) {
    return [false, "Il codice deve essere di 14 caratteri"];
  }

  $valid = gettype($input->codiceMadre) == "string" && strlen($input->codiceMadre) == 14;
  if (!$valid) {
    return [false, "Il codice madre deve essere di 14 caratteri"];
  }

  $valid = $input->sesso == "M" || $input->sesso == "F";
  if (!$valid) return [$valid, 'Il sesso deve essere "M" o "F"'];

  try {
    $data = explode("-", $input->dataNascita);
    $valid = checkdate($data[1], $data[2], $data[0]);
    if (!$valid) return [$valid, "Data non valida"];
  } catch (\Throwable) {
    return [false, "Data non valida"];
  }

  return [$valid, ""];
}

function registra_nascita()
{
  $data = json_decode(file_get_contents('php://input'));

  try {
    $validazione = valida_nascita($data);

    if (!$validazione[0]) {
      return array(
        "error" => "Errore nei dati",
        "message" => $validazione[1]
      );
    }
  } catch (\Throwable) {
    return array(
      "error" => "Errore nei dati",
      "message" => "Errore nella validazione dei dati"
    );
  }

  // Connessione al DB e query
  $db = Database::get_instance();
  $escaped = $db->escape_object($data);

  try {
    // Inizio transazione
    $db->start_transaction();

    // Insert bovino
    $db->query("INSERT INTO `Bovino` (`Codice`, `Madre`, `DataNascita`, `Sesso`) VALUES 
      ('$escaped->codice', '$escaped->codiceMadre', '$escaped->dataNascita', '$escaped->sesso');");


    // Update parto
    $db->query("UPDATE `Parto` SET `CodiceFiglio` = '$escaped->codice' WHERE `Parto`.`ID` = " . $escaped->idParto);

    // Commit
    $db->commit();

    // Invio risposta
    return array(
      "status" => "ok"
    );
  } catch (\Throwable $th) {
    // Gestione degli errori
    if ($db != NULL && $db->ping()) {
      $db->rollback();
    }

    return build_db_error_response($db, $th);
  }
}
