<?php

class Fornecedor 
{

	public function retornarFornecedoresJSON()
	{

		$sqlCriterio = "SELECT id, fornecedor AS nome FROM fornecedores";

		if(isset($_GET["query"]))
		{
			$_GET["query"] = trim(utf8_decode($_GET["query"]));
			$arrayCriterio = [];
			foreach(explode(" ", $_GET["query"]) AS $valor)
			{
				$arrayCriterio[] = "to_ascii(fornecedor) ilike to_ascii('%" . $valor . "%')";
			}
			$sqlCriterio .= " WHERE " . implode(" AND ", $arrayCriterio);
		}

		$sqlCriterio .= " ORDER BY posicao(to_ascii(fornecedor), to_ascii('{$_GET["query"]}')), fornecedor DESC";

		if(isset($_GET["start"]) && is_numeric($_GET["start"]))
			$sqlLimite = $sqlCriterio . " OFFSET " . $_GET["start"];

		if(isset($_GET["limit"]) && is_numeric($_GET["limit"]))
			$sqlLimite .= " LIMIT " . $_GET["limit"];

		$db = DB::getConection();
		$arrayFornecedores["success"]  = true;
		$arrayFornecedores["fornecedores"] = $db->query($sqlLimite)->fetchAll(PDO::FETCH_ASSOC);
		$arrayFornecedores["total"]    = $db->query($sqlCriterio)->rowCount();
		echo SystemHelper::arrayToJSON($arrayFornecedores);


	}

}