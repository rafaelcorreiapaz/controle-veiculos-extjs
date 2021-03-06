<?php

class Servico
{

	private $tipo = 4;

	public function retornarDadosRecebimento($id)
	{
		$db = DB::getConection();
		$servico = $db->query("SELECT COALESCE(a.Baixado, 0) AS pago, b.nome AS cliente, CONCAT('Recebimento Servi�o N� ', a.CodS, ', ', TRIM(historico)) AS historico, (a.valor-COALESCE(a.valorbaixado, 0)) AS valor, (a.valor-COALESCE(a.valorbaixado, 0)) AS valor_receber FROM \"\$servicos\" AS a, rm_clients AS b WHERE a.Cliente = b.id AND a.CodS = {$id}")->fetch(PDO::FETCH_ASSOC);
		return $servico;
	}

	public function retornarDadosEstorno($id)
	{
		$db = DB::getConection();
		$servico = $db->query("SELECT b.nome AS cliente, CONCAT('Estorno Servi�o N� ', a.CodS, ', ', TRIM(historico)) AS historico, COALESCE(a.valorbaixado, 0) AS valor_baixado FROM \"\$servicos\" AS a, rm_clients AS b WHERE a.Cliente = b.id AND a.CodS = {$id}")->fetch(PDO::FETCH_ASSOC);
		return $servico;
	}

	public function realizarRecebimento($id, $historico, $forma_pagamento, $valor_recebido)
	{

		$db = DB::getConection();

		if($db->query("SELECT * FROM \"\$servicos\" WHERE CodS = {$id} AND baixado = 1")->rowCount() != 0)
			throw new ViaRadioException("Solicita��o de Pedido j� se encontra pago!");

		if($db->query("SELECT * FROM \"\$servicos\" WHERE CodS = {$id} AND Valor < COALESCE(ValorBaixado, 0) + {$valor_recebido}")->rowCount() != 0)
			throw new ViaRadioException("O valor recebido supera o valor da Solicita��o de Pedido!");

		$contaMovimentacao = $db->query("SELECT a.idContaD AS Conta, a.TimesFluxo, CONCAT(b.A, '.', b.B, '.', b.C, '/', b.D, '-', b.E) AS CodigoConta FROM fluxodecapital AS a, contas AS b WHERE a.idContaD = b.CodCon AND a.status='0' AND a.Usuario='{$_SESSION["usuario"]}' ORDER BY a.TimesFluxo DESC")->fetch(PDO::FETCH_ASSOC);

		$data = date("Y-m-d", $contaMovimentacao["timesfluxo"]);
		if($db->query("INSERT INTO cx_recebimentos (conta, data, historico, valorD, objeto, numero_objeto, formadepagamento) VALUES ({$contaMovimentacao["conta"]}, '{$data}', '{$historico}', {$valor_recebido}, {$this->tipo}, {$id}, {$forma_pagamento})") == false)
			throw new ViaRadioException("N�o foi poss�vel realizar o lan�amento!");

		if($db->query("UPDATE \"\$servicos\" SET ValorBaixado = COALESCE(ValorBaixado, 0) + {$valor_recebido} WHERE CodS = {$id} AND COALESCE(baixado, 0) != 1") == false)
			throw new ViaRadioException("N�o foi poss�vel pagar a Solicita��o de Pedido!");		

		if($db->query("UPDATE \"\$servicos\" SET Baixado = 1 WHERE CodS = {$id} AND Valor = ValorBaixado") == false)
			throw new ViaRadioException("N�o foi poss�vel pagar a Solicita��o de Pedido!");		


	}

	public function realizarEstornoRecebimento($id, $historico, $forma_estorno, $valor_estornado)
	{

		$db = DB::getConection();

		if($db->query("SELECT * FROM \"\$servicos\" WHERE CodS = {$id} AND COALESCE(ValorBaixado, 0) < {$valor_estornado}")->rowCount() != 0)
			throw new ViaRadioException("O valor estornado supera o valor que j� foi pago!");

		$contaMovimentacao = $db->query("SELECT a.idContaD AS Conta, a.TimesFluxo, CONCAT(b.A, '.', b.B, '.', b.C, '/', b.D, '-', b.E) AS CodigoConta FROM fluxodecapital AS a, contas AS b WHERE a.idContaD = b.CodCon AND a.status='0' AND a.Usuario='{$_SESSION["usuario"]}' ORDER BY a.TimesFluxo DESC")->fetch(PDO::FETCH_ASSOC);

		$data = date("Y-m-d", $contaMovimentacao["timesfluxo"]);
		if($db->query("INSERT INTO cx_recebimentos (conta, data, historico, valorC, objeto, numero_objeto, formadepagamento) VALUES ({$contaMovimentacao["conta"]}, '{$data}', '{$historico}', {$valor_estornado}, {$this->tipo}, {$id}, {$forma_estorno})") == false)
			throw new ViaRadioException("N�o foi poss�vel realizar o lan�amento!");

		if($db->query("UPDATE \"\$servicos\" SET ValorBaixado = COALESCE(ValorBaixado, 0) - {$valor_estornado} WHERE CodS = {$id} AND COALESCE(ValorBaixado, 0) >= {$valor_estornado}") == false)
			throw new ViaRadioException("N�o foi poss�vel estornar o Servi�o!");

		if($db->query("UPDATE \"\$servicos\" SET Baixado = 0 WHERE CodS = {$id} AND Valor != ValorBaixado") == false)
			throw new ViaRadioException("N�o foi poss�vel estornar o Servi�o!");

	}

	public function deletarLancamento($id, $valorD, $valorC)
	{

		$db = DB::getConection();

		if($valorD > 0)
		{

			if($db->query("SELECT * FROM \"\$servicos\" WHERE CodS = {$id} AND COALESCE(ValorBaixado, 0) < {$valorD}")->rowCount() != 0)
				throw new ViaRadioException("O valor estornado supera o valor baixado do Servi�o!");

			if($db->query("UPDATE \"\$servicos\" SET ValorBaixado = COALESCE(ValorBaixado, 0) - {$valorD} WHERE CodS = {$id}") == false)
				throw new ViaRadioException("N�o foi poss�vel deletar o Lan�amento!");

			if($db->query("UPDATE \"\$servicos\" SET Baixado = 0 WHERE CodS = {$id} AND Valor != ValorBaixado") == false)
				throw new ViaRadioException("N�o foi poss�vel deletar o Lan�amento!");
			
		}

		if($valorC > 0)
		{

			if($db->query("SELECT * FROM \"\$servicos\" WHERE CodS = {$id} AND (Valor-COALESCE(ValorBaixado, 0)) < {$valorC}")->rowCount() != 0)
				throw new ViaRadioException("O valor estornado supera o valor baixado do Servi�o!");

			if($db->query("UPDATE \"\$servicos\" SET ValorBaixado = COALESCE(ValorBaixado, 0) + {$valorC} WHERE CodS = {$id}") == false)
				throw new ViaRadioException("N�o foi poss�vel deletar o Lan�amento!");

			if($db->query("UPDATE \"\$servicos\" SET Baixado = 1 WHERE CodS = {$id} AND Valor = ValorBaixado") == false)
				throw new ViaRadioException("N�o foi poss�vel deletar o Lan�amento!");

		}


	}

	public function lancamentoContabilRecebimento($id, $conta, $data, $historico, $valorD, $valorC)
	{

		$lc = new Lancamentos($this->tipo);
		$lc->setarCodTipo($id);
		$lc->setarTipoLancamento(1);
		$lc->setarLancamento($data, $conta, $historico, $valorD, $valorC);
		$lc->setarLancamento($data, "1.1.2/01-000003", $historico, $valorC, $valorD);

		if($lc->retornarErros() > 0)
			throw new ViaRadioException("N�o foi poss�vel realizar os lan�amentos cont�beis do Servi�o {$id}!");

		$lc = null;

	}

	
}