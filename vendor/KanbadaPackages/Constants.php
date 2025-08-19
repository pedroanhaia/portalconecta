<?php
	// Configurações do fuso horário
	date_default_timezone_set('America/Sao_Paulo');

	// Roles 
		define('C_RoleTudo',	99);
		define('C_RoleNada',	0);
	// Meses e Anos 
		define('C_MesesOpt', [
			1 => 'Janeiro',
			2 => 'Fevereiro',
			3 => 'Março',
			4 => 'Abril',
			5 => 'Maio',
			6 => 'Junho',
			7 => 'Julho',
			8 => 'Agosto',
			9 => 'Setembro',
			10 => 'Outubro',
			11 => 'Novembro',
			12 => 'Dezembro',
		]);

		function strMes($mes) {
			if($mes == 1) return 'Janeiro';
			if($mes == 2) return 'Fevereiro';
			if($mes == 3) return 'Março';
			if($mes == 4) return 'Abril';
			if($mes == 5) return 'Maio';
			if($mes == 6) return 'Junho';
			if($mes == 7) return 'Julho';
			if($mes == 8) return 'Agosto';
			if($mes == 9) return 'Setembro';
			if($mes == 10) return 'Outubro';
			if($mes == 11) return 'Novembro';
			if($mes == 12) return 'Dezembro';
		}

		$anos = range(2025, date('Y'));
		$options = [];
		foreach ($anos as $ano) $anosOpt[$ano] = $ano;

		define('C_AnosOpt', $anosOpt);

	// Users - Função na empresa 
		define('C_UsersTypeCliente',	1);
		define('C_UsersTypeAdmin',		2);
		define('C_UsersTypeSupervisor',		99);

		define('C_UsersTypeOpt', [
			C_UsersTypeCliente => 'Cliente',
			C_UsersTypeAdmin => 'Administrador',
			C_UsersTypeSupervisor => 'Supervisor'
		]);

		function UsersType($type) {
			if ($type == C_UsersTypeCliente) return 'Cliente';
			if ($type == C_UsersTypeAdmin)	return 'Administrador';
			if ($type == C_UsersTypeSupervisor)	return 'Supervisor';
		}
	// Pedidos - Status 
		define('C_PedidosStatusRecebido',		1);
		define('C_PedidosStatusEmProjeto',		2);
		define('C_PedidosStatusEmFabricação',	3);
		define('C_PedidosStatusEmTransporte',	4);
		define('C_PedidosStatusEntregue',		5);

		define('C_PedidosStatusOpt', [
			C_PedidosStatusRecebido => 		'Recebido',
			C_PedidosStatusEmProjeto =>		'Em projeto',
			C_PedidosStatusEmFabricação => 	'Em fabricação',
			C_PedidosStatusEmTransporte => 	'Em transporte',
			C_PedidosStatusEntregue => 		'Entregue',
		]);

		define('C_PedidosStatusView', [
			C_PedidosStatusRecebido => [
				'text' => 'Recebido',
				'icon' => '<i class="fa-solid fa-file-circle-check"></i>',
			],
			C_PedidosStatusEmProjeto => [
				'text' => 'Em projeto',
				'icon' => '<i class="fa-solid fa-pen-ruler"></i>',
			],
			C_PedidosStatusEmFabricação => [
				'text' => 'Em fabricação',
				'icon' => '<i class="fa-solid fa-gears"></i>',
			],
			C_PedidosStatusEmTransporte => [
				'text' => 'Em transporte',
				'icon' => '<i class="fa-solid fa-truck"></i>',
			],
			C_PedidosStatusEntregue => [
				'text' => 'Entregue',
				'icon' => '<i class="fa-solid fa-flag"></i>',
			],
		]);

		function PedidosStatus($status) {
			if ($status == C_PedidosStatusRecebido)		return 'Recebido';
			if ($status == C_PedidosStatusEmProjeto)	return 'Em projeto';
			if ($status == C_PedidosStatusEmFabricação)	return 'Em fabricação';
			if ($status == C_PedidosStatusEmTransporte)	return 'Em transporte';
			if ($status == C_PedidosStatusEntregue)		return 'Entregue';
		}

		function getStatusBadgeClass($status) {
			switch ($status) {
				case C_PedidosStatusRecebido:
					return 'primary';
				case C_PedidosStatusEmProjeto:
					return 'warning';
				case C_PedidosStatusEmFabricação:
					return 'info';
				case C_PedidosStatusEmTransporte:
					return 'orange';
				case C_PedidosStatusEntregue:
					return 'success';
				default:
					return 'secondary';
			}
		}
		
		function statusHasComments($status, $comments) {
			foreach ($comments as $c) {
				if ($c['status'] === $status) return true;
			}
			return false;
		}
	// Pedidos - Notificações
		define('C_PedidosNotificacaoTodas',			1);
		define('C_PedidosNotificacaoArquivos',		2);
		define('C_PedidosNotificacaoComentarios',	3);
		define('C_PedidosNotificacaoNenhuma',		4);

		define('C_PedidosNotificacaoTipoOpt', [
			C_PedidosNotificacaoTodas => 'Todas',
			C_PedidosNotificacaoArquivos => 'Arquivos',
			C_PedidosNotificacaoComentarios => 'Comentários',
			C_PedidosNotificacaoNenhuma => 'Nenhuma',
		]);

		function PedidosNotificacaoTipo($status) {
			if ($status == C_PedidosNotificacaoTodas) 		return 'Todas';
			if ($status == C_PedidosNotificacaoArquivos)	return 'Arquivos';
			if ($status == C_PedidosNotificacaoComentarios)	return 'Comentários';
			if ($status == C_PedidosNotificacaoNenhuma)		return 'Nenhuma';
		}
