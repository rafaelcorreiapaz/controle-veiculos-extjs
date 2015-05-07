<?php

class SolicitacaoDePedido
{

	private $tipo = 9;

	public function retornarDadosRecebimento($id)
	{
		$db = DB::getConection();
		$servico = $db->query("SELECT COALESCE(a.Baixado, 0) AS pago, b.nome AS cliente, CONCAT('Recebimento Solicitação de Pedido Nº ', a.CodSP, ', ', TRIM(historico)) AS historico, (a.valor-COALESCE(a.valorbaixado, 0)) AS valor, (a.valor-COALESCE(a.valorbaixado, 0)) AS valor_receber FROM \"\$solicitacoesdepedido\" AS a, rm_clients AS b WHERE a.Cliente = b.id AND a.CodSP = {$id}")->fetch(PDO::FETCH_ASSOC);
		return $servico;
	}

	public function retornarDadosEstorno($id)
	{
		$db = DB::getConection();
		$servico = $db->query("SELECT b.nome AS cliente, CONCAT('Estorno Solicitação de Pedido Nº ', a.CodSP, ', ', TRIM(historico)) AS historico, COALESCE(a.valorbaixado, 0) AS valor_baixado FROM \"\$solicitacoesdepedido\" AS a, rm_clients AS b WHERE a.Cliente = b.id AND a.CodSP = {$id}")->fetch(PDO::FETCH_ASSOC);
		return $servico;
	}

	public function realizarRecebimento($id, $historico, $forma_pagamento, $valor_recebido)
	{	

		$db = DB::getConection();

		if($db->query("SELECT * FROM \"\$solicitacoesdepedido\" WHERE CodSP = {$id} AND baixado = 1")->rowCount() != 0)
			throw new ViaRadioException("Solicitação de Pedido já se encontra pago!");

		if($db->query("SELECT * FROM \"\$solicitacoesdepedido\" WHERE CodSP = {$id} AND Valor < COALESCE(ValorBaixado, 0) + {$valor_recebido}")->rowCount() != 0)
			throw new ViaRadioException("O valor recebido supera o valor da Solicitação de Pedido!");

		$contaMovimentacao = $db->query("SELECT a.idContaD AS Conta, a.TimesFluxo, CONCAT(b.A, '.', b.B, '.', b.C, '/', b.D, '-', b.E) AS CodigoConta FROM fluxodecapital AS a, contas AS b WHERE a.idContaD = b.CodCon AND a.status='0' AND a.Usuario='{$_SESSION["usuario"]}' ORDER BY a.TimesFluxo DESC")->fetch(PDO::FETCH_ASSOC);

		$data = date("Y-m-d", $contaMovimentacao["timesfluxo"]);
		if($db->query("INSERT INTO cx_recebimentos (conta, data, historico, valorD, objeto, numero_objeto, formadepagamento) VALUES ({$contaMovimentacao["conta"]}, '{$data}', '{$historico}', {$valor_recebido}, {$this->tipo}, {$id}, {$forma_pagamento})") == false)
			throw new ViaRadioException("Não foi possível realizar o lançamento!");

		if($db->query("UPDATE \"\$solicitacoesdepedido\" SET ValorBaixado = COALESCE(ValorBaixado, 0) + {$valor_recebido} WHERE CodSP = {$id} AND COALESCE(baixado, 0) != 1") == false)
			throw new ViaRadioException("Não foi possível pagar a Solicitação de Pedido!");		

		if($db->query("UPDATE \"\$solicitacoesdepedido\" SET Baixado = 1 WHERE CodSP = {$id} AND Valor = ValorBaixado") == false)
			throw new ViaRadioException("Não foi possível pagar a Solicitação de Pedido!");		

	}

	public function realizarEstornoRecebimento($id, $historico, $forma_estorno, $valor_estornado)
	{

		$db = DB::getConection();

		if($db->query("SELECT * FROM \"\$solicitacoesdepedido\" WHERE CodSP = {$id} AND COALESCE(ValorBaixado, 0) < {$valor_estornado}")->rowCount() != 0)
			throw new ViaRadioException("O valor estornado supera o valor que já foi pago!");

		$contaMovimentacao = $db->query("SELECT a.idContaD AS Conta, a.TimesFluxo, CONCAT(b.A, '.', b.B, '.', b.C, '/', b.D, '-', b.E) AS CodigoConta FROM fluxodecapital AS a, contas AS b WHERE a.idContaD = b.CodCon AND a.status='0' AND a.Usuario='{$_SESSION["usuario"]}' ORDER BY a.TimesFluxo DESC")->fetch(PDO::FETCH_ASSOC);

		$data = date("Y-m-d", $contaMovimentacao["timesfluxo"]);
		if($db->query("INSERT INTO cx_recebimentos (conta, data, historico, valorC, objeto, numero_objeto, formadepagamento) VALUES ({$contaMovimentacao["conta"]}, '{$data}', '{$historico}', {$valor_estornado}, {$this->tipo}, {$id}, {$forma_estorno})") == false)
			throw new ViaRadioException("Não foi possível realizar o lançamento!");

		if($db->query("UPDATE \"\$solicitacoesdepedido\" SET ValorBaixado = COALESCE(ValorBaixado, 0) - {$valor_estornado} WHERE CodSP = {$id} AND COALESCE(ValorBaixado, 0) >= {$valor_estornado}") == false)
			throw new ViaRadioException("Não foi possível estornar a Solicitação de Pedido!");		

		if($db->query("UPDATE \"\$solicitacoesdepedido\" SET Baixado = 0 WHERE CodSP = {$id} AND Valor != ValorBaixado") == false)
			throw new ViaRadioException("Não foi possível estornar a Solicitação de Pedido!");		

	}

	public function deletarLancamento($id, $valorD, $valorC)
	{

		$db = DB::getConection();

		if($valorD > 0)
		{

			if($db->query("SELECT * FROM \"\$solicitacoesdepedido\" WHERE CodSP = {$id} AND COALESCE(ValorBaixado, 0) < {$valorD}")->rowCount() != 0)
				throw new ViaRadioException("O valor estornado supera o valor baixado da Solicitação de Pedido!");

			if($db->query("UPDATE \"\$solicitacoesdepedido\" SET ValorBaixado = COALESCE(ValorBaixado, 0) - {$valorD} WHERE CodSP = {$id}") == false)
				throw new ViaRadioException("Não foi possível deletar o Lançamento!");

			if($db->query("UPDATE \"\$solicitacoesdepedido\" SET Baixado = 0 WHERE CodSP = {$id} AND Valor != ValorBaixado") == false)
				throw new ViaRadioException("Não foi possível deletar o Lançamento!");

		}

		if($valorC > 0)
		{

			if($db->query("SELECT * FROM \"\$solicitacoesdepedido\" WHERE CodSP = {$id} AND (Valor-COALESCE(ValorBaixado, 0)) < {$valorC}")->rowCount() != 0)
				throw new ViaRadioException("O valor estornado supera o valor baixado da Solicitação de Pedido!");

			if($db->query("UPDATE \"\$solicitacoesdepedido\" SET ValorBaixado = COALESCE(ValorBaixado, 0) + {$valorC} WHERE CodSP = {$id}") == false)
				throw new ViaRadioException("Não foi possível deletar o Lançamento!");

			if($db->query("UPDATE \"\$solicitacoesdepedido\" SET Baixado = 1 WHERE CodSP = {$id} AND Valor = ValorBaixado") == false)
				throw new ViaRadioException("Não foi possível deletar o Lançamento!");
		}

	}

	public function lancamentoContabilRecebimento($id, $conta, $data, $historico, $valorD, $valorC)
	{

		$lc = new Lancamentos($this->tipo);
		$lc->setarCodTipo($id);
		$lc->setarTipoLancamento(1);
		$lc->setarLancamento($data, $conta, $historico, $valorD, $valorC);
		$lc->setarLancamento($data, "1.1.2/01-000002", $historico, $valorC, $valorD);

		if($lc->retornarErros() > 0)
			throw new ViaRadioException("Não foi possível realizar os lançamentos contábeis da Solicitação de Pedido {$id}!");

		$lc = null;

	}


}