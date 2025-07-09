<div class="users form">
	<?= $this->Flash->render() ?>
	<?= $this->Form->create(null) ?>
		<fieldset>
			<?= $this->Form->control('email', ['required' => true]) ?>
			<?= $this->Form->control('password', ['required' => true]) ?>
		</fieldset>
		<div class="div-btns">
			<?= $this->Form->button('Login', ['class'=>'btn btn-login']) ?> 
		</div>
	<?= $this->Form->end() ?>
</div>