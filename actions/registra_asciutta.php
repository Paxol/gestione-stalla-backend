<?php
require_once(__DIR__ . '/../include/common.php');
require_once(__DIR__ . '/../include/db.php');

function valida_asciutta($input)
{
  $valid = gettype($input->id) == "integer";
  if (!$valid) {
    return [$valid, "Errore nella validazione dei dati"];
  }

  return [$valid, ""];
}

function registra_asciutta()
{
  $data = json_decode(file_get_contents('php://input'));

  try {
    $validazione = valida_asciutta($data);

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

  try {
    // Connessione al DB e query
    $db = Database::get_instance();
    $escaped = $db->escape_object($data);

    $db->query("UPDATE `Fecondazione` SET `InAsciutta` = '1' WHERE `Fecondazione`.`ID` = " . $escaped->id);

    // Invio risposta
    return array(
      "status" => "ok"
    );
  } catch (\Throwable $th) {
    // Gestione degli errori
    return build_db_error_response($db, $th);
  }
}
