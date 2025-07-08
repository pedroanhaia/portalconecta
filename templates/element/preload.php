<!DOCTYPE html>
<style>
	.overlay {
		position: fixed;
		top: 0;
		left: 0;
		width: 100%;
		height: 100%;
		background: rgba(0, 0, 0, 0.7); /* Cor de fundo opaca */
		display: block;
		justify-content: center;
		align-items: center;
		display: none; /* Inicialmente oculto */
		z-index: 9999; /* Valor alto para garantir que fique por cima de outros elementos */
		opacity: 0; /* Inicialmente transparente */
		transition: opacity 1s; /* Transição de 1 segundo para a propriedade opacity */
	}

	.overlay.show {
		opacity: 1; /* Ao adicionar a classe 'show', a opacidade torna-se 1 (fade-in) */
	}

	.centered-gif {
		max-width: 50%;
		max-height: 50%;
	}

	.centered-gif-gira {
		max-width: 50%;
		max-height: 50%;
		margin-left: 28%;
	}

	#overlayMsg {
		color: white;
		text-align: center;
	}
</style>
<html lang="en">
	<body>
		<div class="overlay" id='overlayLogin'>
			<?php 
				$arrLogos = ['/webroot/img/Logo 4 - Tinta.gif', "/webroot/img/Logo 5 - 3D (Site's edition).gif"];
				$img = $arrLogos[rand(0, 1)];
			?>
			<img src="<?= $this->Url->build($img, ['fullBase' => true]) ?>" alt="Logo" class="centered-gif">
		</div>
		<div class="overlay" id='overlayGira'>
			<img src="<?= $this->Url->build('/webroot/img/Logo 3d transparente.gif', ['fullBase' => true]) ?>" alt="Logo" class="centered-gif-gira">
			<h3 id='overlayMsg'></h3>
		</div>
	</body>
</html>
<script>
	function preLoad() {
		var overlay = document.querySelector('#overlayLogin');

		// Mostra a overlay quando a página é carregada
		overlay.style.display = 'flex';

		// Adiciona a classe 'show' para iniciar o fade-in
		overlay.classList.add('show');

		// Define um tempo limite de 1000 milissegundos (1 segundo)
		setTimeout(function() {
			// Remove a classe 'show' para iniciar o fade-out
			overlay.classList.remove('show');

			// Define um segundo temporizador para ocultar a overlay após o fade-out
			setTimeout(function() {
				overlay.style.display = 'none';
			}, 2800);
		}, 2800);
	}

	function preLoadTurn(acao, msg) {
		var overlay = document.querySelector('#overlayGira');

		if(acao == 1) {
			$('#overlayMsg').text(msg);
			// Mostra a overlay quando a página é carregada
			overlay.style.display = 'block';
	
			// Adiciona a classe 'show' para iniciar o fade-in
			overlay.classList.add('show');
		} else {
			overlay.classList.remove('show');
			overlay.style.display = 'none';
		}
	}

	<?php if(isset($_SESSION['bLogin'])) { ?>
		document.addEventListener('DOMContentLoaded', function() {
			preLoad();
		});
	<?php 
		}
		unset($_SESSION['bLogin']);
	?>
</script>