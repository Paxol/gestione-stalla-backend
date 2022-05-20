<?php
class DB_Config
{
	public $DB_HOST = "";
	public $DB_USER = "";
	public $DB_PASS = "";
	public $DB_NAME = "";

	function __construct()
	{
		$this->DB_HOST = getenv("DB_HOST");
		$this->DB_USER = getenv("DB_USER");
		$this->DB_PASS = getenv("DB_PASS");
		$this->DB_NAME = getenv("DB_NAME");
	}
}
