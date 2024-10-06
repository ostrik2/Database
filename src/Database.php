<?php
/**
 * Copyright (c) 2024, Igor K. Ostrovskiy
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * 1. Redistributions of source code must retain the above copyright notice, this
 *    list of conditions and the following disclaimer.
 *
 * 2. Redistributions in binary form must reproduce the above copyright notice,
 *    this list of conditions and the following disclaimer in the documentation
 *    and/or other materials provided with the distribution.
 *
 * 3. Neither the name of the copyright holder nor the names of its
 *    contributors may be used to endorse or promote products derived from
 *    this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE
 * FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
 * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
 * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

namespace ostrik2\Database;

/**
 * Class Database
 * 
 * Class for working with MySQL database using PDO.
 * Provides methods for connection, query execution and error handling.
 */
class Database
{
	/**
	 * @var string $host Database Host.
	 */
	private $host;

	/**
	 * @var string $dbName Database Name.
	 */
	private $dbName;

	/**
	 * @var string $username The user name to connect to the database.
	 */
	private $username;

	/**
	 * @var string $password Password to connect to the database.
	 */
	private $password;

	/**
	 * @var \PDO|null $pdo PDO object for database operations.
	 */
	private $pdo = null;

	/**
	 * @var string|null $errorMessage Error message if an error has occurred.
	 */
	private $errorMessage = null;

	/**
	 * @var array $options Options for configuring the PDO.
	 * 
	 * Опции включают:
	 * - PDO::ATTR_PERSISTENT: Sets the connection to be permanent (false).
	 * - PDO::ATTR_ERRMODE: Sets the error handling mode (PDO::ERRMODE_EXCEPTION).
	 * - PDO::ATTR_DEFAULT_FETCH_MODE: Sets the default sampling mode (PDO::FETCH_ASSOC).
	 * - PDO::ATTR_EMULATE_PREPARES: Disables emulation of prepared expressions (false).
	 */
	private $options = [
		\PDO::ATTR_PERSISTENT => false,
		\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
		\PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
		\PDO::ATTR_EMULATE_PREPARES => false,
	];

	/**
	 * Constructor of Database class
	 * 
	 * @param string $host Database Host.
	 * @param string $dbName Database Name.
	 * @param string $username The user name to connect to the database.
	 * @param string $password Password to connect to the database.
	 */
	public function __construct($host, $dbName, $username, $password)
	{
		$this->host = $host;
		$this->dbName = $dbName;
		$this->username = $username;
		$this->password = $password;
		$this->connect();
	}

	/**
	 * A method to connect to the database.
	 * 
	 * @return void
	 */
	private function connect()
	{
		$dsn = "mysql:host={$this->host};dbname={$this->dbName};charset=utf8mb4";

		try {
			$this->pdo = new \PDO($dsn, $this->username, $this->password, $this->options);
		} catch (\PDOException $e) {
			$this->errorMessage = "Database connection error: " . $e->getMessage();
			throw new \Exception($this->errorMessage);
		}
	}

	/**
	 * Executes an SQL query with preparation.
	 * 
	 * @param string $sql SQL query.
	 * @param array $params Parameters for the prepared query.
	 * @return \PDOStatement|false Returns PDOStatement on success or false on failure.
	 */
	public function query($sql, $params = [])
	{
		$this->ensureConnected();

		try {
			$stmt = $this->pdo->prepare($sql);
			$stmt->execute($params);
			return $stmt;
		} catch (\PDOException $e) {
			$this->errorMessage = "Query Execution Error: " . $e->getMessage();
			throw new \Exception($this->errorMessage);
		}
	}

	/**
	 * Executes a SELECT query against the database.
	 * 
	 * @param string $sql SQL query.
	 * @param array $params Parameters for the prepared query.
	 * @return array|false Returns an array with the result of the fetch or false if it fails.
	 */
	public function select($sql, $params = [])
	{
		$stmt = $this->query($sql, $params);
		return $stmt ? $stmt->fetchAll() : false;
	}

	/**
	 * Executes an INSERT/UPDATE/DELETE request to the database.
	 * 
	 * @param string $sql SQL query.
	 * @param array $params Parameters for the prepared query.
	 * @return int Returns the number of affected rows.
	 */
	public function execute($sql, $params = [])
	{
		$stmt = $this->query($sql, $params);
		return $stmt ? $stmt->rowCount() : 0;
	}

	/**
	 * Returns the ID of the last line inserted.
	 * 
	 * @return string Returns a string with the ID of the last inserted record.
	 */
	public function lastInsertId()
	{
		return $this->pdo ? $this->pdo->lastInsertId() : null;
	}

	/**
	 * Checks if the object is connected to the database.
	 * 
	 * @return bool Returns true if the connection is established, otherwise false.
	 */
	public function isConnected()
	{
		return $this->pdo !== null;
	}

	/**
	 * Returns an error message if an error occurred.
	 * 
	 * @return string|null Returns an error message or null if there are no errors.
	 */
	public function getErrorMessage()
	{
		return $this->errorMessage;
	}

	/**
	 * Checks the connection and raises an exception if there is no connection.
	 * 
	 * @throws \Exception If there is no connection.
	 */
	private function ensureConnected()
	{
		if (!$this->isConnected()) {
			throw new \Exception("No connection to the database.");
		}
	}

	/**
	 * Closes the database connection.
	 * 
	 * @return void
	 */
	public function disconnect()
	{
		$this->pdo = null;
	}

	/**
	 * Database class destructor.
	 * Automatically closes the connection to the database.
	 */
	public function __destruct()
	{
		$this->disconnect();
	}
}
