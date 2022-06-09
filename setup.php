<?php
require_once(__DIR__ . "/include/db_config.php");

function WriteResponse($mysqli, $res)
{
	$mysqli->close();

	echo $res;
	exit;
}

$config = new DB_Config();
$mysqli = new mysqli($config->DB_HOST, $config->DB_USER, $config->DB_PASS);

$sql_db_exist = "SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '" . $config->DB_NAME . "'";
$res_db_exist = $mysqli->query($sql_db_exist);

if ($res_db_exist == false) {
	WriteResponse($mysqli, "Errore controllo database. " . $sql_db_exist);
}

$exist = $mysqli->query($sql_db_exist)->num_rows;

if ($exist > 0) {
	WriteResponse($mysqli, "Setup già effettuato");
}

if ($mysqli->query("START TRANSACTION;")) {
	$res = $mysqli->multi_query("
		CREATE DATABASE `" . $config->DB_NAME . "`;
		USE `" . $config->DB_NAME . "`;

		CREATE TABLE IF NOT EXISTS `Bovino` (
			`Codice` char(14) NOT NULL,
			`Madre` char(14) NOT NULL,
			`DataNascita` date NOT NULL,
			`Sesso` enum('M','F') NOT NULL,
			`Provenienza` int(11) DEFAULT NULL,
			`Destinazione` int(11) DEFAULT NULL,
			PRIMARY KEY (`Codice`),
			KEY `FK_Provenienza_Bovino` (`Provenienza`),
			KEY `FK_Destinazione_Bovino` (`Destinazione`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;

		CREATE TABLE IF NOT EXISTS `Fecondazione` (
			`ID` int(11) NOT NULL AUTO_INCREMENT,
			`Codice` char(14) NOT NULL,
			`Data` date NOT NULL,
			`Riuscita` tinyint(1) DEFAULT NULL,
			`InAsciutta` tinyint(1) DEFAULT NULL,
			`Parto` int(11) DEFAULT NULL,
			PRIMARY KEY (`ID`),
			UNIQUE KEY `Parto` (`Parto`),
			KEY `FK_Fecondazione_Bovino` (`Codice`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;

		CREATE TABLE IF NOT EXISTS `Luogo` (
			`ID` int(11) NOT NULL AUTO_INCREMENT,
			`Codice` varchar(10) NOT NULL,
			`Descrizione` varchar(256) NOT NULL,
			PRIMARY KEY (`ID`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;

		CREATE TABLE IF NOT EXISTS `Modello4` (
			`ID` int(11) NOT NULL AUTO_INCREMENT,
			`Serie` int(11) NOT NULL,
			`Numero` int(11) NOT NULL,
			`Cartaceo` tinyint(1) NOT NULL,
			`Data` date NOT NULL,
			`Provenienza` int(11) DEFAULT NULL,
			`Destinazione` int(11) DEFAULT NULL,
			PRIMARY KEY (`ID`),
			KEY `FK_Luogo_Provenienza` (`Provenienza`),
			KEY `FK_Luogo_Destinazione` (`Destinazione`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;

		CREATE TABLE IF NOT EXISTS `Parto` (
			`ID` int(11) NOT NULL AUTO_INCREMENT,
			`Data` date NOT NULL,
			`CodiceFiglio` char(14) DEFAULT NULL,
			PRIMARY KEY (`ID`),
			KEY `FK_PartoBovino` (`CodiceFiglio`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;

		ALTER TABLE `Bovino`
			ADD CONSTRAINT `FK_Destinazione_Bovino` FOREIGN KEY (`Destinazione`) REFERENCES `Modello4` (`ID`),
			ADD CONSTRAINT `FK_Provenienza_Bovino` FOREIGN KEY (`Provenienza`) REFERENCES `Modello4` (`ID`);

		ALTER TABLE `Fecondazione`
			ADD CONSTRAINT `FK_Fecondazione_Bovino` FOREIGN KEY (`Codice`) REFERENCES `Bovino` (`Codice`),
			ADD CONSTRAINT `FK_Fecondazione_Parto` FOREIGN KEY (`Parto`) REFERENCES `Parto` (`ID`);

		ALTER TABLE `Modello4`
			ADD CONSTRAINT `FK_Luogo_Destinazione` FOREIGN KEY (`Destinazione`) REFERENCES `Luogo` (`ID`),
			ADD CONSTRAINT `FK_Luogo_Provenienza` FOREIGN KEY (`Provenienza`) REFERENCES `Luogo` (`ID`);

		ALTER TABLE `Parto`
			ADD CONSTRAINT `FK_PartoBovino` FOREIGN KEY (`CodiceFiglio`) REFERENCES `Bovino` (`Codice`);
		COMMIT;");

	if ($res) {
		WriteResponse($mysqli, "Setup completato con successo");
	} else {
		$mysqli->query("ROLLBACK");
		WriteResponse($mysqli, "Errore nella creazione delle tabelle. " . $mysqli->error);
	}
} else {
	WriteResponse($mysqli, "Errore nella comunicazione col database");
}
