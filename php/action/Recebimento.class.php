<?php

class Recebimento
{

	public function realizarRecebimento()
	{

		try
		{

			$db = DB::getConection();

			$objeto = $_POST["objeto"];
			$numero_objeto = $_POST["numero_objeto"];
			$historico = utf8_decode($_POST["historico"]);
			$forma_pagamento = $_POST["forma_pagamento"];
			$valor_cobrado = $_POST["valor_cobrado"];

			$stmt = $db->query("SELECT * FROM fluxodecapital WHERE status='0' AND Usuario='{$_SESSION["usuario"]}'");
			if($stmt->rowCount() !== 1)
				throw new ViaRadioException("Você possui mais de uma conta em aberto!");

			$classe = SystemConfig::getData("obj")[$_POST["objeto"]]["classe"];
			$obj    = new $classe();
			$obj->realizarRecebimento($numero_objeto, $historico, $forma_pagamento, $valor_cobrado);

			$db->commit();

			$return["success"] = true;
			$return["message"] = "Recebimento realizado com sucesso!";
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

	public function deletarLancamento()
	{

		try
		{

			$db = DB::getConection();

			$id = $_POST["id"];

			$recebimento = $db->query("SELECT * FROM cx_recebimentos WHERE id = {$id}")->fetch(PDO::FETCH_ASSOC);
			if(!isset($recebimento["id"]))
				throw new ViaRadioException("Recebimento não encontrado!");

			$classe = SystemConfig::getData("obj")[$recebimento["objeto"]]["classe"];
			$obj    = new $classe();
			$obj->deletarLancamento($recebimento["numero_objeto"], $recebimento["valord"], $recebimento["valorc"]);

			if($db->query("DELETE FROM cx_recebimentos WHERE id = {$id}") == false)
				throw new ViaRadioException("Não foi possível deletar o recebimento!");

			$db->commit();

			$return["success"] = true;
			$return["message"] = "Deleção realizada com sucesso!";
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

	public function realizarEstornoPagamento()
	{

		try
		{

			$db = DB::getConection();

			$objeto = $_POST["objeto"];
			$numero_objeto = $_POST["numero_objeto"];
			$historico = utf8_decode($_POST["historico"]);
			$forma_estorno = $_POST["forma_estorno"];
			$valor_estornado = $_POST["valor_estornado"];

			$stmt = $db->query("SELECT * FROM fluxodecapital WHERE status='0' AND Usuario='{$_SESSION["usuario"]}'");
			if($stmt->rowCount() !== 1)
				throw new ViaRadioException("Você possui mais de uma conta em aberto!");

			$fluxo = $stmt->fetch(PDO::FETCH_ASSOC);

			$data = date("Y-m-d", $fluxo["timesfluxo"]);
			if($db->query("SELECT * FROM cx_recebimentos WHERE objeto = {$objeto} AND numero_objeto = {$numero_objeto} AND data = '{$data}'")->rowCount() > 0)
				throw new ViaRadioException("Você não pode estornar um Objeto que está lançado no caixa atual, use o botão deletar!");

			$classe = SystemConfig::getData("obj")[$_POST["objeto"]]["classe"];
			$obj    = new $classe();
			$obj->realizarEstornoRecebimento($numero_objeto, $historico, $forma_estorno, $valor_estornado);

			$db->commit();

			$return["success"] = true;
			$return["message"] = "Estorno realizado com sucesso!";
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

	public function fecharMovimentacao()
	{
		try
		{

			$db = DB::getConection();

			$sth = $db->query("SELECT * FROM fluxodecapital WHERE status='0' AND usuario='{$_SESSION["usuario"]}'");

			if($sth == false)
				throw new ViaRadioException("Não foi possível selecionar a data de movimentação!");

			if($sth->rowCount() !== 1)
				throw new ViaRadioException("Você possui mais de uma conta em aberto!");

			$movimentacao = $sth->fetch(PDO::FETCH_ASSOC);

			$recebimentos = $db->query("SELECT a.id, CONCAT(b.A, '.', b.B, '.', b.C, '/', b.D, '-', b.E) AS conta, a.objeto, a.numero_objeto, a.data, a.historico, a.valord, a.valorc FROM cx_recebimentos AS a, contas AS b WHERE a.conta = b.codcon AND a.conta = {$movimentacao["idcontad"]} AND a.data = '" . date("Y-m-d", $movimentacao["timesfluxo"]) . "'")->fetchAll(PDO::FETCH_ASSOC);
			foreach($recebimentos AS $recebimento)
			{

				$classe = SystemConfig::getData("obj")[$recebimento["objeto"]]["classe"];
				$obj    = new $classe();
				$obj->lancamentoContabilRecebimento($recebimento["numero_objeto"], $recebimento["conta"], $recebimento["data"], $recebimento["historico"], $recebimento["valord"], $recebimento["valorc"]);

				unset($obj);

			}

			$time = time();
			if($db->query("UPDATE fluxodecapital SET status = '1', timesfechar = '{$time}' WHERE status = '0' AND Usuario='{$_SESSION["usuario"]}'") == false)
				throw new ViaRadioException("Não foi possível fechar a movimentação!");

			$db->commit();

			$return["success"] = true;
			$return["message"] = "Movimentação fechada com sucesso!";
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


}