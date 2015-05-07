<?php

class DB
{

	private static $con;

	public static function getConection()
	{

		if(!isset(self::$con))
		{

			//try
			//{

				$host    = SystemConfig::getData("host");
				$port    = SystemConfig::getData("port");
				$user    = SystemConfig::getData("user");
				$pass    = SystemConfig::getData("pass");
				$db      = SystemConfig::getData("db");
				$schemas = SystemConfig::getData("schemas");

				self::$con = new PDO("pgsql:dbname={$db} host={$host} port={$port}", $user, "");
				self::$con->query("SET search_path TO " . implode(", ", $schemas));
				self::$con->beginTransaction();

			//}
			//catch(PDOException $e)
			//{
			//	self::$con = $e->getMessage();
			//}

		}

		return self::$con;

	}

}