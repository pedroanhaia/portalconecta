<!DOCTYPE html>
<html>
	<head>
		<?= $this->Html->charset() ?>
		<meta name="viewport" content="width=device-width, initial-scale=1">

		<title> Portal Conecta </title>
		<?= $this->Html->meta('icon', '/img/Logos/logoconectaverticalpreto.jpeg'); ?>

		<link href="https://fonts.googleapis.com/css?family=Raleway:400,700" rel="stylesheet">
		<?= $this->Html->css(['https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css', 'https://unpkg.com/boxicons@2.1.1/css/boxicons.min.css']) ?>

		<?=	$this->Html->script(['https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js','https://code.jquery.com/jquery-3.6.0.min.js'])	?>
		<?= $this->Html->css(['normalize.min', 'milligram.min', 'cake', 'loginstyle', 'portal-conecta']) ?>

		<?= $this->fetch('meta') ?>
		<?= $this->fetch('css') ?>
		<?= $this->fetch('script') ?>
	</head>
	<body>
		<div class="container mt-5">
			<div class="row justify-content-center">
				<div class="col-md-6">
					<div class="card" style="background:linear-gradient(145deg,#080b13,#111d32); color:white; padding: 42px; border:1px solid #18436b; border-radius:18px; box-shadow:0 24px 65px rgba(0,70,145,.24)">
						<div class="logo-center">
							<?= $this->Html->image('Logos/logoconectaverticalpreto.jpeg', ['alt' => 'Portal Conecta']) ?>
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

