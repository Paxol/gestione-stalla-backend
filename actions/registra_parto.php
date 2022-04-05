<?php
require_once(__DIR__ . '/../include/common.php');
require_once(__DIR__ . '/../include/db.php');

function valida_parto($input)
{
  $valid = gettype($input->id) == "integer";
  if (!$valid) {
    return [false, "Errore nella validazione dei dati"];
  }

  try {
    $data = explode("-", $input->data);
    $valid = checkdate($data[1], $data[2], $data[0]);
    if (!$valid) return [$valid, "Data non valida"];
  } catch (\Throwable) {
    return [false, "Data non valida"];
  }

  return [$valid, ""];
}

function registra_parto()
{
  $data = json_decode(file_get_contents('php://input'));

  try {
    $validazione = valida_parto($data);

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

    // Insert parto
    $db->query("INSERT INTO `Parto` (`ID`, `Data`, `CodiceFiglio`) VALUES (NULL, '" . $escaped->data . "', NULL)");
    $fk_parto = $db->last_insert_id();

    // Update fecondazione
    $db->query("UPDATE `Fecondazione` SET `Parto` = '$fk_parto' WHERE `Fecondazione`.`ID` = " . $escaped->id);

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
