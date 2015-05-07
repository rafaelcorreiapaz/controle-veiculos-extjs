<?php

class Veiculo 
{

	public function retornarVeiculosJSON()
	{

		$ordenar = json_decode($_GET["sort"])[0];
		$strtr = ["lt" => ">=", "gt" => "<=", "eq" => "="];

		$sql = "SELECT * FROM cv_veiculos";

		if(isset($_GET["filter"]) > 0)
		{
			$criterio = "";
			foreach($_GET["filter"] AS $key => $value)
			{
				if($value["data"]["type"] == "string")
				{
					foreach(explode(" ", $value["data"]["value"]) AS $chave => $valor)
					{
						$criterio .= strtr($value["field"], $strtr);
						$criterio .= " ILIKE ";
						$criterio .= "'%{$valor}%' AND ";
					}
				}
				elseif($value["data"]["type"] == "numeric")
				{
					$criterio .= "a." . $value["field"];
					$criterio .= strtr($value["data"]["comparison"], $strtr);
					$criterio .= $value["data"]["value"] . " AND ";

				}
			}
			$sql .= " WHERE " . substr($criterio, 0, -5);
		}

		$db = DB::getConection();

		$arrayAbastecimentos["success"]  = true;
		$arrayAbastecimentos["veiculos"] = $db->query($sql . " ORDER BY placa")->fetchAll(PDO::FETCH_ASSOC);
		$arrayAbastecimentos["total"]    = $db->query($sql)->rowCount();

		echo SystemHelper::arrayToJSON($arrayAbastecimentos);


	}

}