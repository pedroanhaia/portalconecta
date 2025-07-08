<style>
	.main {
		width: 85vw !important;
		height: 95vh;
	}
</style>
<div class="col-md-12 content">
	<div class="card" >
		<div class="card-body">
			<?= $this->Form->create($user, ['class' => 'form-material mt-2']) ?>
				<div class="row">
					<?php if (!$hasGoogleOnly): ?>
						<div class="col-lg-3 col-md-6 col-sm-12 col-xs-12">
							<label class="control-label">Senha anterior</label>
							<?= $this->Form->control('old_password', [
								'class' => 'form-control',
								'label' => false,
								'required' => true,
								'placeholder' => 'Insira a senha anterior'
							]) ?>
						</div>
					<?php endif; ?>
				</div>
				<div class="row">
					<div class="col-lg-3 col-md-6 col-sm-12 col-xs-12">
						<label class="control-label">Nova senha</label>
						<?= $this->Form->control('password1', [
							'type' => 'password',
							'class' => 'form-control',
							'label' => false,
							'required' => true,
							'placeholder' => 'Insira a nova senha'
						]) ?>
					</div>
					<div class="col-lg-3 col-md-6 col-sm-12 col-xs-12">
						<label class="control-label">Confirmação da senha</label>
						<?= $this->Form->control('password2', [
							'type' => 'password',
							'class' => 'form-control',
							'label' => false,
							'required' => true,
							'placeholder' => 'Confirme a nova senha'
						]) ?>
					</div>
				</div>
				<div class="row">
					<div class="col-12">
						<?= $this->Form->button('Salvar senha', ['class' => 'btn btn-success btn-lg']) ?>
					</div>
				</div>
			<?= $this->Form->end(); ?>
		</div>
	</div>
</div>