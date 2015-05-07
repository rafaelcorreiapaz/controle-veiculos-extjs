<?php

class SystemAutoLoader
{

	public static $loader;

	private static $dirs =
	[
		'config',
		'exception',
		'helper',
		'loader',
		'model',
		'service',
		'tools',
		'utils',
	];

	private function __construct()
	{
		if(function_exists('__autoload'))
			spl_autoload_register('__autoload');

		spl_autoload_register([$this, 'addClass']);
	}

	public static function init()
	{
		if (!function_exists('spl_autoload_register'))
			throw new Exception("PaymentLibrary: Standard PHP Library (SPL) is required.");

		if (self::$loader == null)
			self::$loader = new SystemAutoLoader();

		return self::$loader;

	}

	private function addClass($class)
	{

		foreach(self::$dirs AS $key => $dir) 
		{
			$file = SystemLibrary::getPath() . DIRECTORY_SEPARATOR . $dir . DIRECTORY_SEPARATOR . $class . '.class.php';
			if(file_exists($file) && is_file($file))
				include_once $file;
		}
	}

}