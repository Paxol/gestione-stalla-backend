<?php
require_once(__DIR__ . '/../include/common.php');
require_once(__DIR__ . '/../include/db.php');

function valida_fecondazione($input)
{
  $valid = gettype($input->codice) == "string" && strlen($input->codice) == 14;
  if (!$valid) return [$valid, "Il codice deve essere di 14 caratteri"];

  try {
    $data = explode("-", $input->data);
    $valid = checkdate($data[1], $data[2], $data[0]);
    if (!$valid) return [$valid, "Data non valida"];
  } catch (\Throwable) {
    return [false, "Data non valida"];
  }

  return [$valid, ""];
}

function registra_fecondazione()
{
  $data = json_decode(file_get_contents('php://input'));

  try {
    $validazione = valida_fecondazione($data);

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

    $db->query("SELECT `Codice` FROM `Fecondazione` WHERE `Codice` = '" . $escaped->codice . "' AND ((`Riuscita` = 1 AND `Parto` IS NULL) OR (`Riuscita` IS NULL))");
    $rows = $db->num_rows();

    if ($rows > 0) {
      return array(
        "error" => "Errore nei dati",
        "message" => "È già stata registrata una fecondazione per questo bovino"
      );
    }
    
    $db->query("INSERT INTO `Fecondazione` (`Codice`, `Data`) VALUES ('" . $escaped->codice . "', '" . $escaped->data . "')");

    // Invio risposta
    return array(
      "status" => "ok"
    );
  } catch (\Throwable $th) {
    // Gestione degli errori
    return build_db_error_response($db, $th);
  }
}
