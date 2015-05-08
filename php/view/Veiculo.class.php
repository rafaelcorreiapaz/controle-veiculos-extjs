<?php

class Veiculo 
{

	public function retornarVeiculosJSON()
	{

		$sql = "SELECT * FROM cv_veiculos";

		$db = DB::getConection();

		$arrayAbastecimentos["success"]  = true;
		$arrayAbastecimentos["veiculos"] = $db->query($sql . " ORDER BY placa")->fetchAll(PDO::FETCH_ASSOC);
		$arrayAbastecimentos["total"]    = $db->query($sql)->rowCount();

		echo SystemHelper::arrayToJSON($arrayAbastecimentos);


	}

}