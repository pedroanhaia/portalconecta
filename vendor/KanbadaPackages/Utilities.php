<?php
	require_once (ROOT . DS . 'vendor' . DS  . 'KanbadaPackages' . DS . 'Constants.php');
	
	// Opt == 0 -> Janeiro
	// Opt == 1 -> Jan
	function mesDesc($mes, $opt = 0) {
		if($mes == 1) return $opt == 0 ? 'Janeiro' : 'Jan';
		else if($mes == 2) return $opt == 0 ? 'Fevereiro' : 'Fev';
		else if($mes == 3) return $opt == 0 ? 'Março' : 'Mar';
		else if($mes == 4) return $opt == 0 ? 'Abril' : 'Abr';
		else if($mes == 5) return $opt == 0 ? 'Maio' : 'Mai';
		else if($mes == 6) return $opt == 0 ? 'Junho' : 'Jun';
		else if($mes == 7) return $opt == 0 ? 'Julho' : 'Jul';
		else if($mes == 8) return $opt == 0 ? 'Agosto' : 'Ago';
		else if($mes == 9) return $opt == 0 ? 'Setembro' : 'Setp';
		else if($mes == 10) return $opt == 0 ? 'Outubro' : 'Out';
		else if($mes == 11) return $opt == 0 ? 'Novembro' : 'Nov';
		else if($mes == 12) return $opt == 0 ? 'Dezembro' : 'Dez';
	}

	function corEhClaraOuEscura($hexCor) {
		// Remove o # se houver
		$hexCor = ltrim($hexCor, '#');

		// Converte para RGB
		if (strlen($hexCor) === 3) {
			// Exemplo: #abc => #aabbcc
			$r = hexdec(str_repeat($hexCor[0], 2));
			$g = hexdec(str_repeat($hexCor[1], 2));
			$b = hexdec(str_repeat($hexCor[2], 2));
		} elseif (strlen($hexCor) === 6) {
			$r = hexdec(substr($hexCor, 0, 2));
			$g = hexdec(substr($hexCor, 2, 2));
			$b = hexdec(substr($hexCor, 4, 2));
		} else {
			return null; // formato inválido
		}

		// Calcula o brilho com fórmula de luminância perceptual
		$luminancia = ($r * 299 + $g * 587 + $b * 114) / 1000;

		// Threshold: 128 (ou use 186 para mais contraste)
		return $luminancia > 128 ? 'clara' : 'escura';
	}
