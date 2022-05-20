<?php
require_once(__DIR__ . "/db_config.php");

class Database
{
	protected $connection;
	protected $query;
	protected $query_closed = TRUE;
	protected $last_error = NULL;
	public $query_count = 0;

	/**
	 * Istanza della classe
	 * @var Database
	 */
	private static $_instance; //The single instance

	public function __construct($charset = 'utf8')
	{
		$config = new DB_Config();

		try {
			$this->connection = new mysqli($config->DB_HOST, $config->DB_USER, $config->DB_PASS, $config->DB_NAME);
			if ($this->connection->connect_error) {
				$this->error('Si è verificato un errore nella connessione al database', $this->connection->connect_error);
			}
			$this->connection->set_charset($charset);
		} catch (\Throwable $th) {
			$this->error('Si è verificato un errore nella connessione al database', $th->getMessage());
		}
	}

	public function __destruct()
	{
		// La classe viene distrutta, chiudo la connessione
		$this->close();
	}

	/*
	Get an instance of the Database
	@return Instance
	*/
	public static function get_instance()
	{
		if (!self::$_instance) { // If no instance then make one
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	// Magic method clone is empty to prevent duplication of connection
	private function __clone()
	{
	}

	public function ping() {
		return $this->connection->ping();
	}

	public function escape($to_escape)
	{
		return $this->connection->real_escape_string($to_escape);
	}

	public function escape_object($to_escape)
	{
		$obj = new stdClass();

		foreach ($to_escape as $key => $value) {
			$obj->{$key} = $this->escape($value);
		}

		return $obj;
	}

	public function start_transaction()
	{
		if (!$this->connection->query("START TRANSACTION")) {
			// Si è verificato un errore
			$this->error('Database error', $this->connection->error);
		}
	}

	public function commit()
	{
		if (!$this->connection->query("COMMIT")) {
			// Si è verificato un errore
			$this->error('Database error', $this->connection->error);
		}
	}

	public function rollback()
	{
		if (!$this->connection->query("ROLLBACK")) {
			// Si è verificato un errore
			$this->error('Database error', $this->connection->error);
		}
	}

	public function query($query)
	{
		try {
			// Se una query è stata lasciata aperta la chiudo
			if (gettype($this->query) != "boolean" && !$this->query_closed) {
				$this->query->close();
			}

			if ($this->query = $this->connection->query($query)) {
				// Imposto la query come aperta
				$this->query_closed = FALSE;
				$this->query_count++;
			} else {
				// Si è verificato un errore
				$this->error('Database error', $this->connection->error);
			}
		} catch (\Throwable $th) {
			$this->error('Errore query', $this->connection->error);
		}

		// Ritorno l'istanza della classe
		return $this;
	}


	public function fetch_all($resulttype = MYSQLI_ASSOC)
	{
		$result = $this->query->fetch_all($resulttype);

		$this->query->close();
		$this->query_closed = TRUE;
		return $result;
	}

	public function fetch_assoc()
	{
		$result = array();

		while ($row = $this->query->fetch_assoc()) {
			array_push($result, $row);
		}

		$this->query->close();
		$this->query_closed = TRUE;
		return $result;
	}

	public function fetch_array()
	{
		$result = array();

		while ($row = $this->query->fetch_array()) {
			array_push($result, $row);
		}

		$this->query->close();
		$this->query_closed = TRUE;
		return $result;
	}

	public function fetch_first($resulttype = MYSQLI_ASSOC)
	{
		$result = $this->query->fetch_array($resulttype);

		$this->query->close();
		$this->query_closed = TRUE;
		return $result;
	}

	public function close()
	{
		return $this->connection->close();
	}

	public function num_rows()
	{
		return $this->query->num_rows;
	}

	public function affected_rows()
	{
		return $this->connection->affected_rows;
	}

	public function last_insert_id()
	{
		return $this->connection->insert_id;
	}

	protected function error($error, $debug_message)
	{
		$this->last_error = [$error, $debug_message];
		throw new Error($error);
	}

	public function get_last_error()
	{
		return $this->last_error;
	}
}
