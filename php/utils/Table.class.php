<?php

class Table 
{

	private $db;
	private $tabela;
	private $datas = [];
	private $columns = [];
	private $comparisons = [">", ">=", "<", "<=", "=", "!=", 'ilike', 'like'];
	private $reserved = ["now()"];

	public function __construct($tabela)
	{

		$this->db = DB::getConection();
		$this->tabela = $tabela;

		$stmt = $this->db->query("SELECT column_name, udt_name, character_maximum_length, is_nullable, column_default FROM information_schema.columns WHERE table_catalog = '" . SystemConfig::getData("db") . "' AND table_name = '{$tabela}' AND table_schema IN ('" . implode("', '", SystemConfig::getData("schemas")) . "')");

		while($row = $stmt->fetch())
		{
			$this->columns[$row["column_name"]] =
			[
				$row["udt_name"],
				$row["character_maximum_length"],
				$row["is_nullable"],
				$row["column_default"]
			];

		}
        

	}

	public function __set($name, $value)
	{
		if (array_key_exists($name, $this->columns))
			$this->datas[$name] = $value;
	}

	public function __get($name)
	{
		if (array_key_exists($name, $this->columns))
			return $this->datas[$name];
	}

	private function mountConditions(array $conditions = array())
	{
		$where = "";
		if(is_array($conditions))
		{
			foreach($conditions AS $key => $value)
			{
				if(is_array($value))
				{
					if(count($value) == count($value, COUNT_RECURSIVE))
					{
						if($this->checkColumn($value[0], $value[1]) == true)
						{
							if(count($value) == 2)
								$where .= is_numeric($value[1]) ? "{$value[0]} = {$value[1]}" : "{$value[0]} = E'{$value[1]}'";
							elseif(count($value) == 3 && in_array(strtolower($value[2]), $this->comparisons))
								$where .= is_numeric($value[1]) ? "{$value[0]} {$value[2]} {$value[1]}" : "{$value[0]} {$value[2]} E'{$value[1]}'";
							else
								throw new Exception("Error Processing Request");
						}
						else
							throw new Exception("({$value[0]}={$value[1]}) Coluna ou Valor invalido");
					}
					else
						$where .= "(" . $this->mountConditions($value) . ")";
				}
				elseif(strtolower($value) == "or" || strtolower($value) == "and")
					$where .= " {$value} ";
				else
					throw new Exception("Error Processing Request");
			}
		}
		return $where;
	}

	private function checkColumn($column, $value = null)
	{

		$check_types = array
						(
							"int2"    => function($value, $length, $nullable, $default)
										 {
										 	return ((is_int($value) && !is_null($value)) || ($nullable === "YES" && is_null($value)) || ($default != "" && is_null($value))) ? true : true;
										 },
							"int4"    => function($value, $length, $nullable, $default)
										 {
										 	return ((is_int($value) && !is_null($value)) || ($nullable === "YES" && is_null($value)) || ($default != "" && is_null($value))) ? true : false;
										 },
							"int8"    => function($value, $length, $nullable, $default)
										 {
										 	return ((is_int($value) && !is_null($value)) || ($nullable === "YES" && is_null($value)) || ($default != "" && is_null($value))) ? true : false;
										 },
							"numeric"    => function($value, $length, $nullable, $default)
										 {
										 	return ((is_numeric($value) && !is_null($value)) || ($nullable === "YES" && is_null($value)) || ($default != "" && is_null($value))) ? true : false;
										 },
							"varchar" => function($value, $length, $nullable, $default)
										 {
										 	return ((is_string($value) && $length == 0) || ($length > 0 && strlen($value) <= $length && is_string($value)) || ($nullable === "YES" && is_null($value)) || ($default != "" && is_null($value))) ? true : false;
										 },
							"bpchar"  => function($value, $length, $nullable, $default)
										 {
										 	return ((is_string($value) && $length == 0) || ($length > 0 && strlen($value) <= $length && is_string($value)) || ($nullable === "YES" && is_null($value)) || ($default != "" && is_null($value))) ? true : false;
										 },
							"text"    => function($value, $length, $nullable, $default)
										 {
										 	return ((is_string($value) && $length == 0) || ($length > 0 && strlen($value) <= $length && is_string($value)) || ($nullable === "YES" && is_null($value)) || ($default != "" && is_null($value))) ? true : false;
										 },
							"date"    => function($value, $length, $nullable, $default)
										 {
										 	$date = DateTime::createFromFormat("Y-m-d", $value);
										 	return (($date && $date->format("Y-m-d") == $value) || ($nullable === "YES" && is_null($value)) || (strtolower($value) == "now()")) ? true : false;
										 },
							"timestamp" => function($value, $length, $nullable, $default)
										 {
										 	$date = DateTime::createFromFormat("Y-m-d H:i:s", $value);
										 	return (($date && $date->format("Y-m-d H:i:s") == $value) || ($nullable === "YES" && is_null($value)) || (strtolower($value) == "now()")) ? true : false;
										 },
							"_int4"   => function($value, $length, $nullable, $default)
										 {
										 	return true;
										 },
							"_numeric" => function($value, $length, $nullable, $default)
										 {
										 	return true;
										 },
							"_text"     => function($value, $length, $nullable, $default)
										 {
										 	return true;
										 },
						);

		if(array_key_exists($this->columns[$column][0], $check_types))
		{
			if(array_key_exists($column, $this->columns))
			{
				return $check_types[$this->columns[$column][0]] ( (is_null($value) && isset($this->datas[$column])) ? $this->datas[$column] : $value, $this->columns[$column][3], $this->columns[$column][2], $this->columns[$column][3]);
			}
			else
				return false;
		}
		else
			return false;

	}

	private function checkAllColumns()
	{

		$i = 0;
		foreach($this->columns AS $key => $value)
		{
			if($this->checkColumn($key) == false)
				++$i;
		}
		return ($i > 0) ? false : true;

	}

	private function checkAllColumnsDatas()
	{

		$i = 0;
		foreach($this->datas AS $key => $value)
		{
			if($this->checkColumn($key) == false)
			{
				echo $key . " - " . $value . "<br>";
				++$i;
			}
		}
		return ($i > 0) ? false : true;
	}

	private function checkCriterios(array $criterios)
	{
		$i = 0;
		foreach($criterios AS $key => $value)
		{
			if($value != "OR" AND $value != "AND")
				if($this->checkColumn($key) == false)
					++$i;
		}
		return ($i > 0) ? false : true;
	}

	public function insert()
	{

		if($this->checkAllColumns() == true)
		{
			$datas = array();
			foreach($this->datas AS $key => $value)
			{
				$datas[$key] = is_numeric($value) || in_array($value, $this->reserved) ? $value : "E'{$value}'";
			}

			$sql = "INSERT INTO {$this->tabela} (" . implode(", ", array_keys($datas)) . ") VALUES (" . implode(", ", $datas) . ")";
			return $this->db
				 		->query($sql);
		}
		else
			return false;
			
	}

	public function delete(array $criterios = array())
	{
		$sql = "DELETE FROM {$this->tabela}";
		if(count($criterios) > 0)
		{
			$sql .= " WHERE " . $this->mountConditions($criterios);
		}
		return $this->db
					->query($sql);

	}

	public function update(array $criterios = array())
	{
		if($this->checkAllColumnsDatas() == true)
		{

			$datas = array();
			foreach($this->datas AS $key => $value)
			{
				$datas[$key] = is_numeric($value) || in_array($value, $this->reserved) ? $value : "E'{$value}'";
			}

			$sql = "UPDATE {$this->tabela} SET ";
			if(count($datas) > 0)
			{
				foreach ($datas as $key => $value)
				{
					$sql .= "{$key}={$value}, ";
				}
				$sql = substr($sql, 0, -2);
			}

			if(count($criterios) > 0)
			{
				$sql .= " WHERE " . $this->mountConditions($criterios);
			}

			return $this->db
				 		->query($sql);
			
		}
		else
			return false;

	}

	public function select(array $criterios = array(), array $columns = array("*"), $limit = null, $offset = null)
	{
		$sql = "SELECT " . implode(", ", $columns) . " FROM {$this->tabela}";
		if(count($criterios) > 0)
		{
			$sql .= " WHERE " . $this->mountConditions($criterios);
		}
		if(is_numeric($limit))
			$sql .= " LIMIT " . $limit;

		if(is_numeric($offset))
			$sql .= " OFFSET " . $offset;

		return $this->db
					->query($sql);
	}

	public function query($sql)
	{
		return $this->db
					->query($sql);
		
	}

}