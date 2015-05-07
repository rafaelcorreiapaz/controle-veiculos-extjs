<?php

class SystemHelper
{

	public static function decimalFormat($numeric, $decimal = ',', $thousand = '.', $decimais = 2)
	{
		if(is_numeric($numeric))
		{
			$numeric = (float) $numeric;
			$numeric = (string) number_format($numeric, $decimais, $decimal, $thousand);
		}
		return $numeric;
	}

	public static function retornarProximoDiaUtil($data)
	{
		$data = date("Y-m-d", strtotime($data)+(24*60*60));
		$db = DB::getConection();
		$stmt = $db->query("SELECT * FROM vr_feriados WHERE data = '{$data}'");
		if($stmt->rowCount() > 0 || date("w", strtotime($data)) == 0)
		    return SystemHelper::retornarProximoDiaUtil($data);
		else
		    return $data;
	}

	public static function arrayToJSON($array = array())
	{
		if(is_array($array))
		{
			array_walk_recursive($array, function(&$value){
				$value = utf8_encode($value);
			});
		}
		return json_encode($array);
	}

	public static function formatDate($date, $formatDate = 'd/m/Y', $newFormatDate = 'Y-m-d')
	{
		$objDate = DateTime::createFromFormat($formatDate, $date);
		return $objDate->format($newFormatDate);
	}


	public static function maskValue($valor, $mascara)
	{
		$valor_formatado = "";
		$index = 0;
		for($i=0; $i<strlen($mascara); $i++)
			$valor_formatado .= ($mascara[$i] == "#") ? $valor[$index++] : $mascara[$i];
		return $valor_formatado;
	}

	public static function onlyNumber($value)
	{
		return preg_replace("/[^0-9]/", "", $value); 
	}

	public static function httprequest($endereco, $post, $parametros = [], $ssl = false, $chavePublica = '', $chavePrivada = '', $porta = 443)
	{

		$sessao_curl = curl_init();

		//  CURLOPT_CONNECTTIMEOUT
		//  o tempo em segundos de espera para obter uma conexão
		curl_setopt($sessao_curl, CURLOPT_CONNECTTIMEOUT, 10);

		curl_setopt($sessao_curl, CURLOPT_URL, $endereco);
		curl_setopt($sessao_curl, CURLOPT_PORT, $porta);

		curl_setopt($sessao_curl, CURLOPT_VERBOSE, true);
		// curl_setopt($sessao_curl, CURLOPT_HEADER, true);

		curl_setopt($sessao_curl, CURLOPT_FAILONERROR, true);

		if($ssl != false)
		{

			curl_setopt($sessao_curl, CURLOPT_SSLVERSION, 3);

			//  CURLOPPT_SSL_VERIFYHOST
			//  verifica se a identidade do servidor bate com aquela informada no certificado
			curl_setopt($sessao_curl, CURLOPT_SSL_VERIFYHOST, 2);

			//  CURLOPT_SSL_VERIFYPEER
			//  verifica a validade do certificado
			curl_setopt($sessao_curl, CURLOPT_SSL_VERIFYPEER, false);

			curl_setopt($sessao_curl, CURLOPT_SSLCERT, $chavePublica);
			curl_setopt($sessao_curl, CURLOPT_SSLKEY, $chavePrivada);

		}

		//  CURLOPT_TIMEOUT
		//  o tempo máximo em segundos de espera para a execução da requisição (curl_exec)
		// curl_setopt($sessao_curl, CURLOPT_TIMEOUT, 40);

		//  CURLOPT_RETURNTRANSFER
		//  TRUE para curl_exec retornar uma string de resultado em caso de sucesso, ao
		//  invés de imprimir o resultado na tela. Retorna FALSE se há problemas na requisição
		curl_setopt($sessao_curl, CURLOPT_RETURNTRANSFER, true);

		curl_setopt($sessao_curl, CURLOPT_POST, true);
		curl_setopt($sessao_curl, CURLOPT_POSTFIELDS, $post);

		if(count($parametros) > 0)
			curl_setopt($sessao_curl, CURLOPT_HTTPHEADER, $parametros);

		$resultado = curl_exec($sessao_curl);
		
		curl_close($sessao_curl);

		if($resultado)
			return $resultado;
		else
			return false;
	}

}