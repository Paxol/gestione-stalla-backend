<?php
require_once(__DIR__ . '/../include/common.php');
require_once(__DIR__ . '/../include/db.php');

function valida_conferma_fecondazione($input)
{
  $valid = gettype($input->id) == "integer";
  if (!$valid) return [$valid, "ID non valido"];

  $valid = $input->riuscita == 0 || $input->riuscita == 1;
  if (!$valid) return [$valid, 'Campo "Riuscita" non valido'];

  return [$valid, ""];
}

function conferma_fecondazione()
{
  $data = json_decode(file_get_contents('php://input'));

  try {
    $validazione = valida_conferma_fecondazione($data);

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

    $db->query("UPDATE `Fecondazione` SET `Riuscita` = '" . $escaped->riuscita . "' WHERE `Fecondazione`.`ID` = " . $escaped->id);

    // Invio risposta
    return array(
      "status" => "ok"
    );
  } catch (\Throwable $th) {
    // Gestione degli errori
    return build_db_error_response($db, $th);
  }
}
