<?php

	// conex�o com o banco de dados
	$SystemConfig["host"]    = "189.90.40.21";
	$SystemConfig["port"]    = "18035";
	$SystemConfig["user"]    = "postgres";
	$SystemConfig["pass"]    = "";
	$SystemConfig["db"]      = "viaradio";
	$SystemConfig["schemas"] = ["public", $_SESSION["_sistema"]];

	if($_SESSION["_sistema"] == "_imperatriz")
	{
		date_default_timezone_set("America/Belem");
		define("ENTIDADE", "J�piter Imperatriz");
	}
	elseif($_SESSION["_sistema"] == "_acailandia")
	{
		date_default_timezone_set("Etc/GMT-2");
		define("ENTIDADE", "J�piter A�ail�ndia");
	}
	elseif($_SESSION["_sistema"] == "_grajau")
	{
		date_default_timezone_set("America/Belem");
		define("ENTIDADE", "J�piter Graja�");
	}
