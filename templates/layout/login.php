<!DOCTYPE html>
<html>
	<head>
		<?= $this->Html->charset() ?>
		<meta name="viewport" content="width=device-width, initial-scale=1">

		<title> CGL Conecta </title>
		<?= $this->Html->meta('icon', '/webroot/img/logo.png'); ?>

		<link href="https://fonts.googleapis.com/css?family=Raleway:400,700" rel="stylesheet">
		<?= $this->Html->css(['https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css', 'https://unpkg.com/boxicons@2.1.1/css/boxicons.min.css']) ?>

		<?=	$this->Html->script(['https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js','https://code.jquery.com/jquery-3.6.0.min.js'])	?>
		<?= $this->Html->css(['normalize.min', 'milligram.min', 'cake', 'loginstyle']) ?>

		<?= $this->fetch('meta') ?>
		<?= $this->fetch('css') ?>
		<?= $this->fetch('script') ?>
	</head>
	<body>
		<div class="container mt-5">
			<div class="row justify-content-center">
				<div class="col-md-6">
					<div class="card" style="background-color:#1a2f33; color:white; padding: 50px">
						<div class="logo-center">
							<?= $this->Html->image('logos/logoconectaverticalpreto.jpeg', ['alt' => 'Portal Conecta']) ?>
						</div>
						<main class="main card-body">
							<div class="container">
								<?= $this->Flash->render() ?>
								<?= $this->fetch('content') ?>
							</div>
						</main>
					</div>
				</div>
			</div>
		</div>
	</body>
</html>

