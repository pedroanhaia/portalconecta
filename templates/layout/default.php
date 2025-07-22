<?php
	$cakeDescription = 'CGL-Conecta';
?>
<!DOCTYPE html>
<html>
<head>
	<?= $this->Html->charset() ?>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>
		<?= $cakeDescription ?>:
		<?= $this->fetch('title') ?>
	</title>
	<?= $this->Html->meta('icon', '/webroot/img/logo.png'); ?>

	<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
	<?= $this->Html->css(['https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css', 'https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css', 'https://unpkg.com/boxicons@2.1.1/css/boxicons.min.css']) ?>
	<?= $this->Html->css(['mainstyle','normalize.min', 'milligram.min', 'fonts', 'cake', 'sidebarstyle', 'buttons', 'custom-theme']) ?>

	<?= $this->Html->script(['https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js']) ?>
	<?= $this->Html->script(["https://code.jquery.com/jquery-3.7.1.min.js", 'selectfolder']) ?>

	<!-- jQuery UI para datepicker e Mask -->
	<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.inputmask/5.0.9/jquery.inputmask.min.js"></script>
	<link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">

	<!-- Selectpicker -->
	<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.3.1/js/bootstrap.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.3.1/js/bootstrap.bundle.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.13.6/js/bootstrap-select.min.js"></script>
	<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.13.6/css/bootstrap-select.min.css" rel="stylesheet" />

	<!-- Datatable -->
	<?= $this->Html->css("/plugins/datatables/datatables.min") ?>
	<?= $this->Html->script("/plugins/datatables/datatables.min") ?>

	<base href="<?= $this->Url->build('/', ['fullBase' => true]) ?>">

	<?= $this->fetch('meta') ?>
	<?= $this->fetch('css') ?>
	<?= $this->fetch('script') ?>
</head>
<body >
	<nav class="sidebar close">
		<header>
			<i class='bx bx-chevron-right toggle'></i>
		</header>
		<div class="menu-bar">
			<div class="menu">
				<ul class="menu-links">
					<li class="nav-link logo-center">
						<?= $this->Html->link(
							$this->Html->image('Logos/Logo CGL_vertical branco.png', ['alt' => 'logo', 'class' => 'mini-sidebar']) .
							$this->Html->image('Logos/Logo CGL_horizontal branco.png', ['alt' => 'logo', 'class' => 'open-sidebar']),
							'https://cglinox.com.br/',
							['escape' => false, 'target' => '_blank']
						); ?>
					</li>
					<li class="nav-link">
						<?= $this->Html->link('<i class="fa-solid fa-home icon"></i> ' . __('Home'), '/orders/index', ['escape' => false, 'class' => '']);?>
					</li>
					<li class="nav-link">
						<?= $this->Html->link('<i class="fa-solid fa-user icon"></i> ' . __('Perfil'), '/users/edit', ['escape' => false, 'class' => '']);?>
					</li>
					<li class="nav-link">
						<?= $this->Html->link('<i class="fa-solid fa-cart-shopping icon"></i> ' . __('Pedidos'), '/orders/index', ['escape' => false, 'class' => '']);?>
					</li>
					<?php if($userAdmin) { ?>
						<li class="nav-link">
							<?= $this->Html->link('<i class="fa-solid fa-industry icon"></i> ' . __('Clientes'), '/customers/index', ['escape' => false, 'class' => '']);?>
						</li>
					<?php } ?>
					<?php if($userAdmin || $userType == C_UsersTypeAdmin) { ?>
						<li class="nav-link">
							<?= $this->Html->link('<i class="fa-solid fa-gear icon"></i> ' . __('Configurações'), '/config/index', ['escape' => false, 'class' => '']);?>
						</li>
					<?php } ?>
					<li class="">
						<?= $this->Html->link('<i class="bx bx-log-out icon"></i> ' . __('<span class="text nav-text">Logout</span>'), '/Users/logout', ['escape' => false, 'class' => '']);?>
					</li>
				</ul>
			</div>
			<div class="bottom-content">
				<li class="">
				</li>
				<li class="mode">
					<div class="sun-moon">
						<i class='bx bx-moon icon moon'></i>
						<i class='bx bx-sun icon sun'></i>
					</div>
					<span class="mode-text text">Modo escuro</span>

					<div class="toggle-switch">
						<span class="switch"></span>
					</div>
				</li>
			</div>
		</div>
	</nav>
	<main class="main sidebar-collapsed">
		<div class="main-content">
			<?= $this->Flash->render() ?>
			<?= $this->element('preload'); ?>
			<?= $this->fetch('content') ?>
		</div>
	</main>
	<footer>
	</footer>
</body>
</html>
<script>
	// Sidebar 
		if (typeof body === 'undefined') {
			document.addEventListener("DOMContentLoaded", function () {
				const body = document.querySelector("body");
				const main = body.querySelector("main");
				const sidebar = body.querySelector("nav.sidebar");
				const toggle = body.querySelector(".toggle");
				const modeSwitch = body.querySelector(".toggle-switch");
				const modeText = body.querySelector(".mode-text");

				// Alternar sidebar
				toggle.addEventListener("click", () => {
					sidebar.classList.toggle("close");
                    main.classList.toggle("sidebar-collapsed");
				});

                // Verifica o estado inicial da sidebar para aplicar a classe ao body
                if (sidebar.classList.contains("close")) {
                    main.classList.add("sidebar-collapsed");
                }

				// Alternar modo escuro
				modeSwitch.addEventListener("click", () => {
					const isDarkMode = body.classList.toggle("dark");

					// Atualiza texto
					modeText.innerText = isDarkMode ? "Modo claro" : "Modo escuro";

					// Gerencia o carregamento do CSS
					const darkCssId = "dark-mode-css";
					let existingLink = document.getElementById(darkCssId);

					if (isDarkMode && !existingLink) {
						const link = document.createElement("link");
						link.rel = "stylesheet";
						link.href = "<?= $this->Url->css('dark') ?>";
						link.id = darkCssId;
						document.head.appendChild(link);
					} else if (!isDarkMode && existingLink) {
						existingLink.remove();
					}

					// Salva preferência
					$.ajax({
						url: '<?= $this->Url->build(["controller" => "Users", "action" => "darkmode"]) ?>',
						method: 'POST',
						data: { darkmode: isDarkMode ? 1 : 0 },
						success: function () {
							console.log('Preferência de tema salva.');
						},
						error: function () {
							console.log('Erro ao salvar o tema.');
						}
					});
				});

				// Se já for modo escuro ao carregar
				<?php if (!empty($userDarkMode) && $userDarkMode === 1): ?>
					const darkCssId = "dark-mode-css";
					const link = document.createElement("link");
					link.rel = "stylesheet";
					link.href = "<?= $this->Url->css('dark') ?>";
					link.id = darkCssId;
					document.head.appendChild(link);
					body.classList.add("dark");
					if (modeText) modeText.innerText = "Modo claro";
				<?php endif; ?>
			});

		}
	// Datatable 
		var datatableOptions = {
			"pageLength": 10,
			"language": {
				"sProcessing":    "Procesando...",
				"sLengthMenu":    "Mostrar _MENU_ registros",
				"sZeroRecords":   "Nenhum registro encontrado",
				"sEmptyTable":    "Nenhum dado disponível",
				"sInfo":          "Mostrando registros de _START_ até _END_ de um total de _TOTAL_ registros",
				"sInfoEmpty":     "Mostrando registros de 0 a 0 de um total de 0 registros",
				"sInfoFiltered":  "(filtrado de um total de _MAX_ registros)",
				"sInfoPostFix":   "",
				"sSearch":        "Buscar:",
				"sUrl":           "",
				"sInfoThousands":  ",",
				"sLoadingRecords": "Carregando...",
				"oPaginate": {
					"sFirst":    "<<",
					"sLast":    ">>",
					"sNext":    ">",
					"sPrevious": "<"
				},
				"oAria": {
					"sSortAscending":  ": Ordem Ascendente",
					"sSortDescending": ": Ordem descendente"
				},
			}
		};

		var datatableOptionsLanguage = {
			"sProcessing":    "Procesando...",
			"sLengthMenu":    "Mostrar _MENU_ registros",
			"sZeroRecords":   "Nenhum registro encontrado",
			"sEmptyTable":    "Nenhum dado disponível",
			"sInfo":          "Mostrando registros de _START_ até _END_ de um total de _TOTAL_ registros",
			"sInfoEmpty":     "Mostrando registros de 0 a 0 de um total de 0 registros",
			"sInfoFiltered":  "(filtrado de um total de _MAX_ registros)",
			"sInfoPostFix":   "",
			"sSearch":        "Buscar:",
			"sUrl":           "",
			"sInfoThousands":  ",",
			"sLoadingRecords": "Carregando...",
			"oPaginate": {
				"sFirst":    "<<",
				"sLast":    ">>",
				"sNext":    ">",
				"sPrevious": "<"
			},
			"oAria": {
				"sSortAscending":  ": Ordem Ascendente",
				"sSortDescending": ": Ordem descendente"
			},
		};
	//
</script>