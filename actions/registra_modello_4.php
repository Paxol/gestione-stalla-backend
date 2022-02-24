<?php
require_once(__DIR__ . '/../include/db.php');

function valida_modello($modello)
{
  $valid = $modello->tipologia == 0 || $modello->tipologia == 1;
  if (!$valid) return [$valid, "Tipologia non valida"];

  $valid = gettype($modello->capi) === "array" && count($modello->capi) > 0;
  if (!$valid) return [$valid, "Deve essere selezionato almeno un bovino"];

  try {
    $data = explode("-", $modello->data);
    $valid = checkdate($data[1], $data[2], $data[0]);
    if (!$valid) return [$valid, "Data non valida"];
  } catch (\Throwable) {
    return [false, "Data non valida"];
  }

  $valid = $modello->serie > 0;
  if (!$valid) return [$valid, "Serie non valida"];

  $valid = $modello->numero > 0;
  if (!$valid) return [$valid, "Numero non valido"];

  $valid = gettype($modello->cartaceo) == "boolean";
  if (!$valid) return [$valid, 'Campo "Cartaceo" non valido'];

  if ($modello->nuovoLuogo) {
    $valid_arr = valida_luogo($modello->luogo);
    if (!$valid_arr[0]) return $valid_arr;
  } else {
    $valid = gettype($modello->luogo) == "integer";
    if (!$valid) return [$valid, 'Luogo non valido'];
  }

  return valida_capi($modello->tipologia, $modello->capi);
}

function valida_luogo($luogo)
{
  $valid = gettype($luogo->codice) == "string" &&
    strlen($luogo->codice) > 0 &&
    strlen($luogo->codice) <= 10;
  if (!$valid) return [$valid, "Il codice del luogo non può essere vuoto e deve essere di massimo 10 caratteri"];

  $valid = gettype($luogo->descrizione) == "string" &&
    strlen($luogo->descrizione) <= 256;
  if (!$valid) return [$valid, "La descrizione del luogo non può essere di più di 256 caratteri"];

  return [true, ""];
}

function valida_capi($mode, $capi)
{
  if ($mode == 0) {
    $valid = true;
    for ($i = 0; $valid && $i < count($capi); $i++) {
      $valid = gettype($capi[$i]) == "string" && strlen($capi[$i]) == 14;
    }

    if (!$valid) return [$valid, "Ogni capo deve avere un codice di 14 caratteri"];
    return [true, ""];
  } else {
    $valid_arr = [true, ""];
    for ($i = 0; $valid_arr[0] && $i < count($capi); $i++) {
      $valid_arr = valida_bovino($capi[$i]);
    }

    return $valid_arr;
  }
}

function valida_bovino($bovino)
{
  $valid = gettype($bovino->codice) == "string" && strlen($bovino->codice) == 14;
  if (!$valid) return [$valid, "Il codice deve essere di 14 caratteri"];

  $valid = gettype($bovino->madre) == "string" && strlen($bovino->madre) == 14;
  if (!$valid) return [$valid, "Il codice della madre deve essere di 14 caratteri"];

  try {
    $data = explode("-", $bovino->dataNascita);
    $valid = checkdate($data[1], $data[2], $data[0]);
    if (!$valid) return [$valid, "Data di nascita non valida"];
  } catch (\Throwable) {
    return [false, "Data di nascita non valida"];
  }

  $valid = $bovino->sesso == "M" || $bovino->sesso == "F";
  if (!$valid) return [$valid, 'Il sesso deve essere "M" o "F"'];

  return [$valid, ""];
}

function registra_modello_4()
{
  $data = json_decode(file_get_contents('php://input'));

  try {
    $validazione = valida_modello($data);

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

  $db = Database::get_instance();
  try {
    $db->start_transaction();

    $luogo = 0;
    if ($data->nuovoLuogo) {
      $esc_luogo = $db->escape_object($data->luogo);
  
      $db->query("INSERT INTO `Luogo` (`Codice`, `Descrizione`) VALUES ('".$esc_luogo->codice."', '".$esc_luogo->descrizione."')");
      $luogo = $db->last_insert_id();
    } else {
      $luogo = $data->luogo;
    }
  
    $escaped = $db->escape_object(array(
      "data" => $data->data,
      "serie" => $data->serie,
      "numero" => $data->numero,
      "luogo" => $luogo
    ));
    
    $cartaceo = $data->cartaceo ? 1 : 0;
    $provenienza = $data->tipologia == 0 ? "NULL" : "'$luogo'";
    $destinazione = $data->tipologia == 1 ? "NULL" : "'$luogo'";
  
    $db->query("INSERT INTO `Modello4` (`Serie`, `Numero`, `Cartaceo`, `Data`, `Provenienza`, `Destinazione`) VALUES ('".$escaped->serie."', '".$escaped->numero."', '".$cartaceo."', '".$escaped->data."', ".$provenienza.", ".$destinazione.")");
    $id_modello = $db->last_insert_id();
  
    if ($data->tipologia == 0) {
      // UPDATE
      for ($i=0; $i < count($data->capi); $i++) { 
        $codice = $data->capi[$i];
        $db->query("UPDATE `Bovino` SET `Destinazione` = $destinazione WHERE `Bovino`.`Codice` = '$codice'");
      }
    } else if ($data->tipologia == 1) {
      // INSERT
      for ($i=0; $i < count($data->capi); $i++) { 
        $bovino = $data->capi[$i];
        $esc_bovino = $db->escape_object($bovino);
        $db->query("INSERT INTO `Bovino` (`Codice`, `Madre`, `DataNascita`, `Sesso`, `Provenienza`) VALUES ('".$esc_bovino->codice."', '".$esc_bovino->madre."', '".$esc_bovino->dataNascita."', '".$esc_bovino->sesso."', '".$id_modello."')");
      }
    }

    $db->commit();

    return array(
      "status" => "ok"
    );
  } catch (\Throwable $th) {
    if ($db->ping()) {
      $db->rollback();
    }

    var_dump($th);
    var_dump($db->get_last_error());

    return array(
      "error" => "Database error",
      "message" => "Si è verificato un problema col database"
    );
  }
}
