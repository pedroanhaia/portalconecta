<div class="card-container">
	<?= $this->Html->link(
		'<div class="card-config">
			<i class="fas fa-users card-icon"></i>
			<h3 class="card-title">Usuários</h3>
			<p class="card-description">Lista de usuários do sistema</p>
		</div>',
		['controller' => 'Users', 'action' => 'index'],
		['escape' => false]
	) ?>

	<?= $this->Html->link(
		'<div class="card-config">
			<i class="fas fa-folder card-icon"></i>
			<h3 class="card-title">Diretórios</h3>
			<p class="card-description">Caminhos do sistema</p>
		</div>',
		['controller' => 'Config', 'action' => 'path'],
		['escape' => false]
	) ?>
</div>
