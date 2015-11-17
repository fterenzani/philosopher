<?php
/**
 * (c) 2015 Francesco Terenzani
 */

namespace Socrate;

/**
 * Just a bit of sugar in PDO
 */
class Pdo extends \PDO
{

	function __construct($dsn, $username = '', $password = '', array $driver_options = array()) 
	{
		parent::__construct($dsn, $username, $password, $driver_options);
		$this->setAttribute(\PDO::ATTR_ERRMODE , \PDO::ERRMODE_EXCEPTION);
		$this->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_OBJ);
	}

	function execute($sql, $params = array()) 
	{

		$stmt = $this->prepare($sql);
		if (!is_array($params)) {
			$params = array($params);
		}

		$stmt->execute($params);
		return $stmt;

	}

}

