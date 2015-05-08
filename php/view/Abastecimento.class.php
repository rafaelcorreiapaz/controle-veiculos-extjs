<?php

class Abastecimento 
{

	public function retornarAbastecimentosJSON()
	{

		$ordenar = json_decode($_GET["sort"])[0];
		$strtr = ["lt" => ">=", "gt" => "<=", "eq" => "="];

		$sql = "SELECT cv_abastecimentos.id, cv_abastecimentos.data, fornecedores.fornecedor, cv_abastecimentos.total, cv_abastecimentos.desconto, (cv_abastecimentos.total-cv_abastecimentos.desconto) AS resultado FROM cv_abastecimentos INNER JOIN fornecedores ON (cv_abastecimentos.fornecedor = fornecedores.id)";

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
		$arrayAbastecimentos["abastecimentos"] = $db->query($sql . " ORDER BY {$ordenar->property} {$ordenar->direction} OFFSET {$_GET["start"]} LIMIT {$_GET["limit"]}")->fetchAll(PDO::FETCH_ASSOC);
		$arrayAbastecimentos["total"]    = $db->query($sql)->rowCount();

		echo SystemHelper::arrayToJSON($arrayAbastecimentos);


	}

	public function salvarAbastecimento()
	{
		$return = [];

		try
		{

			$db = DB::getConection();

			$idAbastecimento = (int) $_POST["id"];
			$data = $_POST["data"];
			$fornecedor = $_POST["fornecedor"];
			$desconto = (float) $_POST["desconto"];

			if($idAbastecimento > 0)
			{
				$db->query("UPDATE cv_abastecimentos SET data = '{$data}', fornecedor = {$fornecedor} WHERE id = {$idAbastecimento}");
			}
			else
			{
				if($db->query("INSERT INTO cv_abastecimentos (data, fornecedor) VALUES ('{$data}', {$fornecedor})") == false)
					throw new ViaRadioException("Não foi possível inserir abastecimento!");
					
				$idAbastecimento = $db->lastInsertId("cv_abastecimentos_id_seq");
			}

			$listaAbastecimento = json_decode($_POST["lista"]);

			$total = 0;
			foreach($listaAbastecimento as $value)
			{
				if($db->query("INSERT INTO cv_listaabastecimentos (abastecimento, veiculo, condutor, km, litros, valor_total) VALUES ({$idAbastecimento}, '{$value->veiculo}', {$value->condutor}, {$value->km}, {$value->litros}, {$value->valor_total})") == false)
					throw new ViaRadioException("Não foi possível inserir lista abastecimento!" . "INSERT INTO cv_listaabastecimentos (abastecimento, veiculo, condutor, km, litros, valor_total) VALUES ({$idAbastecimento}, '{$value->veiculo}', {$value->condutor}, {$value->km}, {$value->litros}, {$value->valor_total})");
				$total += $value->valor_total;
			}

			if($db->query("UPDATE cv_abastecimentos SET total = {$total}, desconto = {$desconto} WHERE id = {$idAbastecimento}") == false)
					throw new ViaRadioException("Não foi possível inserir os totais no abastecimento!");


			$db->commit();

			$return["success"] = false;
			$return["message"] = "Abastecimento salvo com sucesso!";
			echo SystemHelper::arrayToJSON($return);

		}
		catch(ViaRadioException $e)
		{

			$db->rollBack();

			$return["success"] = false;
			$return["message"] = $e->getMessage();
			echo SystemHelper::arrayToJSON($return);

		}

	}

	public function retornarAbastecimento()
	{

		$pdf = new PDF("L", "mm", "A4");
		$pdf->setTitulo("Júpiter Telecomunicações e Informática LTDA");
		$pdf->setDescricao("www.jupiter.com.br - sac@jupiter.com.br");
		$pdf->setEndereco("Rua Simplicio Moreira, 1.485B - Centro Fone: (99) 3529-3131");
		$pdf->setData("Imperatriz - MA, " . date("d/m/Y"));
		$pdf->AliasNbPages();
		$pdf->AddPage();

		$db = DB::getConection();
		$abastecimento = $db->query("SELECT cv_abastecimentos.id, cv_abastecimentos.data, fornecedores.fornecedor, cv_abastecimentos.total, cv_abastecimentos.desconto, (cv_abastecimentos.total-cv_abastecimentos.desconto) AS resultado FROM cv_abastecimentos INNER JOIN fornecedores ON (cv_abastecimentos.fornecedor = fornecedores.id) WHERE cv_abastecimentos.id = {$_GET["id"]}")->fetch(PDO::FETCH_ASSOC);
		$abastecimento["data"] = date("d/m/Y", strtotime($abastecimento["data"]));


		$pdf->SetWidths([20, 40, 220]); // 280
		$pdf->SetAligns(["C", "L", "L"]);
		$pdf->row([$abastecimento["id"], $abastecimento["data"], $abastecimento["fornecedor"]]);

		$pdf->ln();
		$pdf->SetFont("Arial", "B", 8);
		$pdf->SetFillColor(190, 190, 190);
		$pdf->Cell(20, 5, "Veículo", 1, 0, "C", true);
		$pdf->Cell(170, 5, "Condutor", 1, 0, "C", true);
		$pdf->Cell(40, 5, "KM", 1, 0, "C", true);
		$pdf->Cell(25, 5, "Litros", 1, 0, "C", true);
		$pdf->Cell(25, 5, "Total", 1, 1, "C", true);

		$pdf->SetFont("Arial", "", 8);
		$pdf->SetWidths([20, 170, 40, 25, 25]); // 280
		$pdf->SetAligns(["C", "L", "L", "R", "R"]);

		$sql = "SELECT cv_listaabastecimentos.veiculo, fornecedores.fornecedor AS condutor, cv_listaabastecimentos.km, cv_listaabastecimentos.litros, cv_listaabastecimentos.valor_total FROM cv_listaabastecimentos INNER JOIN fornecedores ON (cv_listaabastecimentos.condutor = fornecedores.id) WHERE cv_listaabastecimentos.abastecimento = {$_GET["id"]} ORDER BY cv_listaabastecimentos.id DESC";
		foreach($db->query($sql)->fetchAll(PDO::FETCH_ASSOC) AS $abastecimento)
		{

			$array = [];

			$array[] = $abastecimento["veiculo"];
			$array[] = strtoupper($abastecimento["condutor"]);
			$array[] = $abastecimento["km"];
			$array[] = $abastecimento["litros"];
			$array[] = $abastecimento["valor_total"];

			$pdf->row($array);

		}		

		$pdf->Output();

	}

}