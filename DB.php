<?php
interface iDB
{
	function connect($host, $user, $password, $dbname);
	function query($query);
	function select($table, $where, $fileds, $limitOffset, $limitCount, $orderBy, $orderType);
	function insert($table, $fields, $values);
	function update($table, $set, $where);
	function delete($table, $where);
	function numRows();
	function getResult();
	function close();
}

final class DB implements iDB
{
	private static $link;
	private static $result;

	function connect($host, $user, $password, $dbname)
	{
		if (!self::$link) @self::$link = new mysqli($host, $user, $password, $dbname);
		if (empty(self::$link->connect_error)) return self::$link;

		self::error('Connection error: (' . self::$link->connect_errno . '): ' . self::$link->connect_error) . '.';
		return false;
	}

	function query($query)
	{
		if (!self::$link)
		{
			self::error('No DB connection.');
			return false;
		}

		if (empty($query))
		{
			self::error('Request is empty.');
			return false;
		}

		$result = self::$link->query($query);

		if ($result === false) {
			self::error('Request error: (' . self::$link->errno . '): ' . self::$link->error);
			return false;
		}

		if ($result !== true) self::$result = $result;
		return true;
	}

	function select(
		$table,
		$where = '',
		$fields = '*',
		$limitOffset = 0,
		$limitCount = 30,
		$orderBy = '',
		$orderType = 'DESC')
	{
		if (empty($table)) return false;

		$query = 'SELECT ' . $fields . ' FROM ' . $table;

		if (!empty($where)) $query .= ' WHERE ' . $where;
		if ($limitOffset >= 0 && $limitCount > 0) $query .= ' LIMIT ' . $limitOffset . ', ' . $limitCount;
		if (!empty($orderBy) && !empty($orderType)) $query .= ' ORDER BY ' . $orderBy . ' ' . $orderType;

		if (DB::query($query)) return true;
		return false;
	}

	function insert($table, $fields = '', $values)
	{
		if (empty($table) || empty($values)) return false;

		$query = 'INSERT ' . $table;

		if (!empty($fields)) $query .= ' (' . $fields . ')';
		if (!empty($values)) $query .= ' VALUES (' . $values . ')';

		if (DB::query($query)) return true;
		return false;
	}

	function update($table, $set = '', $where = '')
	{
		if (empty($table)) return false;

		$query = 'UPDATE ' . $table;

		if (!empty($set)) $query .= ' SET ' . $set;
		if (!empty($where)) $query .= ' WHERE ' . $where;

		if (DB::query($query)) return true;
		return false;
	}

	function delete($table, $where = '')
	{
		if (empty($table)) return false;

		$query = 'DELETE FROM ' . $table;

		if (!empty($where)) $query .= ' WHERE ' . $where;

		if (DB::query($query)) return true;
		return false;
	}

	function numRows()
	{
		if (!self::$result) return false;
		return self::$result->num_rows;
	}

	function getResult()
	{
		if (!self::$result) return false;

		$result = array();
		while($row = self::$result->fetch_assoc()) $result[] = $row;

		self::$result->free();
		return (!empty($result)) ? $result : false;
	}

	function close()
	{
		self::$result = NULL;
		if (self::$link) self::$link->close();
	}

	private function error($msg)
	{
		if (!empty($msg))
		{
			throw new Exception($msg);
			return true;
		}

		return false;
	}

	private function __construct() {}
	private function __clone() {}
	private function __wakeup() {}
}
