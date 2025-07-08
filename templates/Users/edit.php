<style>
	.main {
		width: 85vw !important;
		height: 95vh;
	}
</style>

<div class="col-md-12 content">
	<div class="card" >
		<div class="card-body">
			<?= $this->Form->create($user, ['class' => 'form-material  mt-2', 'type' => 'file']) ?>
				<div class="row">
					<div class="col-lg-3 col-md-6 col-sm-12 col-xs-12">
						<label class="control-label"> Nome Completo </label>
						<?= $this->Form->control('name', ['class' => 'form-control', 'label' => false, 'required' => true, 'placeholder' => 'Insira o seu nome']) ?>
					</div>
					<div class="col-lg-3 col-md-6 col-sm-12 col-xs-12">
						<label class="control-label"> Nickname </label>
						<?= $this->Form->control('nickname', ['class' => 'form-control', 'label' => false, 'required' => true, 'placeholder' => 'Insira o seu nickname']) ?>
					</div>
				</div>
				<div class="row">
					<div class="col-lg-3 col-md-6 col-sm-12 col-xs-12">
						<label class="control-label"> E-mail </label>
						<?= $this->Form->control('email', ['class' => 'form-control', 'label' => false, 'required' => true, 'placeholder' => 'Insira o seu e-mail']) ?>
					</div>
				</div>
				<div class="row">
					<div class="col-12">
						<?= $this->Form->control('img_edit', ['type'  => 'file', 'label' => 'Insira uma imagem para alterar a sua foto de perfil!', 'id' => 'img_edit',]);
						?>
					</div>
				</div>
				<div class="row">
					<div class="col-12">
						<?= $this->Form->button('Salvar ', ['class' => 'btn btn-success btn-lg']) ?>
						<?= $this->Html->link('Alterar senha ', ['controller' => 'users', 'action' => 'changepassword'], ['class' => 'btn btn-info btn-lg']) ?>
					</div>
				</div>
			<?= $this->Form->end(); ?>
		</div>
	</div>
</div>