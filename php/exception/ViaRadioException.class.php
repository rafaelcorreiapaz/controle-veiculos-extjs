<?php

class ViaRadioException extends Exception
{

	public function errorMessage()
	{
		$errorMsg = $this->getMessage()."\n";
		return $errorMsg;
	}

	public function __toString()
	{
		return __CLASS__ . ": [{$this->code}]: {$this->message}";
	}


}
