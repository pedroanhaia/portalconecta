<?php
	// Configurações do fuso horário
	date_default_timezone_set('America/Sao_Paulo');

	// Roles 
		define('C_RoleTudo', 		99);
		define('C_RoleNada', 		0);
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

	// Quadros - Visibilidade 
		define('C_QuadrosVisibilidadeParticular',	1);
		define('C_QuadrosVisibilidadeGrupo',		2);
		define('C_QuadrosVisibilidadeLink',			2);

		define('C_QuadrosVisibilidades', [
			C_QuadrosVisibilidadeParticular => 'Particular',
			C_QuadrosVisibilidadeLink => 'Qualquer um com o link'
		]);

		function QuadrosVisibilidade($vis) {
			if ($vis == C_QuadrosVisibilidadeParticular) return 'Particular';
			if ($vis == C_QuadrosVisibilidadeGrupo)      return 'Grupo';
			if ($vis == C_QuadrosVisibilidadeGrupo)      return 'Qualquer um com o link';
		}
	// Quadros - BG Type
		define('C_BgTypeColor', 1);
		define('C_BgTypeImage', 2);

		define('C_BgTypes', [
			C_BgTypeColor => 'Cor',
			C_BgTypeImage => 'Imagem'
		]);

		function BgTypeLabel($type) {
			if ($type == C_BgTypeColor) return 'Cor';
			if ($type == C_BgTypeImage) return 'Imagem';
		}

	// 