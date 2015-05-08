<?php

	session_start();
	ini_set('session.gc_maxlifetime', 3*60*60);
	$_SESSION["_sistema"] = "_imperatriz";

	include_once "SystemLibrary.php";
