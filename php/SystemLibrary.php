<?php

class SystemLibrary
{

	public static $config;
	private static $path;
	private static $library;

	private function __construct()
	{
		self::$path = (dirname(__FILE__));
		SystemAutoloader::init();
		self::$config = SystemConfig::init();


		$pathFile = self::getPath() . DIRECTORY_SEPARATOR . strtr($_GET["url"], ["/" => DIRECTORY_SEPARATOR]);


		$file = "";
		foreach(explode(DIRECTORY_SEPARATOR, $pathFile) AS $key => $value)
		{
			$file .= ($key == 0) ? $value : DIRECTORY_SEPARATOR . $value;
			if(file_exists($file . ".class.php") && is_file($file . ".class.php"))
			{
				$file  = $file . ".class.php";
				$class = $value;
				break;
			}
		}

		if(file_exists($file) && is_file($file))
		{
			include_once $file;

			if(isset($_GET["url"]))
			{
				$arrayPath   = explode(DIRECTORY_SEPARATOR, $pathFile);
				$keyClass    = array_search($class, $arrayPath);
				$arrayParams = array_slice($arrayPath, $keyClass+1);
				$method      = $arrayParams[0];

				if($class != $method)
				{

					$obj = new $class();
					if(count($arrayParams) == 1)
						$obj->{$method}();
					else
						$obj->{$method}( array_slice($arrayParams, 1) );

				}
				else
					new $class();
			}
		}


	}

	public static function init()
	{
		include_once "loader" . DIRECTORY_SEPARATOR . "SystemAutoLoader.class.php";

		if(self::$library == null)
			self::$library = new SystemLibrary();

		return self::$library;
	}

	final public static function getPath()
	{
		return self::$path;
	}


}

SystemLibrary::init();