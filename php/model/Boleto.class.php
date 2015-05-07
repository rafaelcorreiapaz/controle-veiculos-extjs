<?php

class Boleto
{

	private $tipo = 6;

	public function retornarDadosRecebimento($id)
	{
		$db = DB::getConection();
		$boletos = $db->query("SELECT pago, sacado AS cliente, CONCAT('Pagamento do boleto NN ', TRIM(nosso_numero), ', de ', sacado, ', do contrato ', contrato, ' pelo Caixa') AS historico, timestamp_vencimento AS data_vencimento, valor, valor AS valor_receber FROM boletos WHERE id = {$id}")->fetch(PDO::FETCH_ASSOC);
		$boletos["pago"] = ($boletos["pago"] == "sim") ? 1 : 0;

		$boletos["data_vencimento"] = date("Y-m-d", $boletos["data_vencimento"]);
		if(isset($boletos["valor_receber"]))
			$boletos["valor_receber"] = SystemHelper::decimalFormat($this->retornarValorAReceber($boletos["data_vencimento"], $boletos["valor_receber"]), '.');
		return $boletos;
	}

	public function retornarDadosEstorno($id)
	{
		$db = DB::getConection();
		$boletos = $db->query("SELECT pago, sacado AS cliente, CONCAT('Estorno do boleto NN ', TRIM(nosso_numero), ', de ', sacado, ', do contrato ', contrato, ' pelo Caixa') AS historico, valor_pago AS valor_baixado FROM boletos WHERE id = {$id}")->fetch(PDO::FETCH_ASSOC);
		return $boletos;
	}

	public function retornarValorAReceber($dataVencimento, $valor)
	{
		$dataAtual = strtotime(date("Y-m-d"));
		$dataVencimento = strtotime($dataVencimento);

		if($dataAtual > $dataVencimento)
		{
			$dias  = ($dataAtual-$dataVencimento)/(60*60*24);
			$multa = ($valor*2)/100;
			return (($valor*(pow(1+1/3000, $dias)))+$multa);
		}
		else
			return $valor;
	}

	public function realizarRecebimento($id, $historico, $forma_pagamento, $valor_recebido)
	{

		$db = DB::getConection();

		if($db->query("SELECT * FROM boletos WHERE id = {$id} AND pago = 'sim'")->rowCount() != 0)
			throw new ViaRadioException("Boleto j� se encontra pago!");

		$contaMovimentacao = $db->query("SELECT a.idContaD AS Conta, a.TimesFluxo, CONCAT(b.A, '.', b.B, '.', b.C, '/', b.D, '-', b.E) AS CodigoConta FROM fluxodecapital AS a, contas AS b WHERE a.idContaD = b.CodCon AND a.status='0' AND a.Usuario='{$_SESSION["usuario"]}' ORDER BY a.TimesFluxo DESC")->fetch(PDO::FETCH_ASSOC);

		$data = date("Y-m-d", $contaMovimentacao["timesfluxo"]);
		if($db->query("INSERT INTO cx_recebimentos (conta, data, historico, valorD, objeto, numero_objeto, formadepagamento) VALUES ({$contaMovimentacao["conta"]}, '{$data}', '{$historico}', {$valor_recebido}, {$this->tipo}, {$id}, {$forma_pagamento})") == false)
			throw new ViaRadioException("N�o foi poss�vel realizar o lan�amento!");

		if($db->query("UPDATE boletos SET pago = 'sim', valor_pago = {$valor_recebido}, juro = {$valor_recebido} - valor, conta = '{$contaMovimentacao["codigoconta"]}', timestamp_pagamento = {$contaMovimentacao["timesfluxo"]}, data_pagamento = '" . date("d/m/Y", $contaMovimentacao["timesfluxo"]) . "' WHERE id = {$id} AND pago != 'sim'") == false)
			throw new ViaRadioException("N�o foi poss�vel pagar o boleto!");

	}

	public function realizarEstornoRecebimento($id, $historico, $forma_estorno, $valor_estornado)
	{

		$db = DB::getConection();

		if($db->query("SELECT * FROM boletos WHERE id = {$id} AND pago != 'sim' AND valor_pago != {$valor_estornado}")->rowCount() != 0)
			throw new ViaRadioException("Boleto n�o se encontra pago!");

		$contaMovimentacao = $db->query("SELECT a.idContaD AS Conta, a.TimesFluxo, CONCAT(b.A, '.', b.B, '.', b.C, '/', b.D, '-', b.E) AS CodigoConta FROM fluxodecapital AS a, contas AS b WHERE a.idContaD = b.CodCon AND a.status='0' AND a.Usuario='{$_SESSION["usuario"]}' ORDER BY a.TimesFluxo DESC")->fetch(PDO::FETCH_ASSOC);

		$data = date("Y-m-d", $contaMovimentacao["timesfluxo"]);
		if($db->query("INSERT INTO cx_recebimentos (conta, data, historico, valorC, objeto, numero_objeto, formadepagamento) VALUES ({$contaMovimentacao["conta"]}, '{$data}', '{$historico}', {$valor_estornado}, {$this->tipo}, {$id}, {$forma_estorno})") == false)
			throw new ViaRadioException("N�o foi poss�vel realizar o lan�amento!");

		if($db->query("UPDATE boletos SET pago = 'n�o', valor_pago = null, juro = null, conta = null, timestamp_pagamento = null, data_pagamento = null WHERE id = {$id} AND pago = 'sim' AND valor_pago = {$valor_estornado}") == false)
			throw new ViaRadioException("N�o foi poss�vel estornar o boleto!");

	}


	public function deletarLancamento($id, $valorD, $valorC)
	{

		$db = DB::getConection();

		if($valorD > 0)
		{

			if($db->query("UPDATE boletos SET pago = 'n�o', valor_pago = null, juro = null, conta = null, timestamp_pagamento = null, data_pagamento = null WHERE id = {$id} AND pago = 'sim' AND valor_pago = {$valorD}") == false)
				throw new ViaRadioException("N�o foi poss�vel estornar o boleto!");

		}

		if($valorC > 0)
		{

			$ultimoLancamento = $db->query("SELECT a.*, CONCAT(b.A, '.', b.B, '.', b.C, '/', b.D, '-', b.E) AS CodigoConta FROM cx_recebimentos AS a, contas AS b WHERE a.conta = b.CodCon AND a.objeto = {$this->tipo} AND a.numero_objeto = {$id} ORDER BY a.id DESC LIMIT 1 OFFSET 1")->fetch(PDO::FETCH_ASSOC);
			if($ultimoLancamento["valorc"] > 0)
				throw new ViaRadioException("N�o entendi o que voc� quis fazer!");

			$timestamp_pagamento = strtotime($ultimoLancamento["data"]);
			$data_pagamento = date("d/m/Y", $timestamp_pagamento);

			if($db->query("UPDATE boletos SET pago = 'sim', valor_pago = {$ultimoLancamento["valord"]}, juro = {$ultimoLancamento["valord"]}-valor, conta = '{$ultimoLancamento["codigoconta"]}', timestamp_pagamento = {$timestamp_pagamento}, data_pagamento = '{$data_pagamento}' WHERE id = {$id} AND pago != 'sim'") == false)
				throw new ViaRadioException("N�o foi poss�vel deletar o lan�amento!");

		}

	}

	public function lancamentoContabilRecebimento($id, $conta, $data, $historico, $valorD, $valorC)
	{

		$db = DB::getConection();

		$sth = $db->query("SELECT * FROM boletos WHERE id = {$id}");
		if($sth == false)
			throw new ViaRadioException("N�o foi poss�vel selecionar o Boleto {$id}!");

		$boleto = $sth->fetch(PDO::FETCH_ASSOC);

		$lc = new Lancamentos($this->tipo);
		$lc->setarCodTipo($id);
		$lc->setarTipoLancamento(1);
		$lc->setarLancamento($data, $conta, $historico, $valorD, $valorC);
		if($valorD > 0)
		{
			$lc->setarLancamento($data, "1.1.2/01-000001", $historico, 0, $boleto["valor"]);
			if($valorD > $boleto["valor"])
				$lc->setarLancamento($data, "4.1.1/03-000001", $historico, 0, ($valorD-$boleto["valor"]));
		}
		if($valorC > 0)
		{
			$lc->setarLancamento($data, "1.1.2/01-000001", $historico, $boleto["valor"], 0);
			if($valorC > $boleto["valor"])
				$lc->setarLancamento($data, "4.1.1/03-000001", $historico, ($valorC-$boleto["valor"]), 0);
		}

		if($lc->retornarErros() > 0)
			throw new ViaRadioException("N�o foi poss�vel realizar os lan�amentos cont�beis do boleto {$id}!");

		$lc = null;

	}

}