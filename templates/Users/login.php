<div class="users form">
	<?= $this->Flash->render() ?>
	<?= $this->Form->create(null) ?>
		<fieldset>
			<?= $this->Form->control('email', ['required' => true, 'style'=>'background-color: white']) ?>
			<?= $this->Form->control('password', ['required' => true, 'style'=>'background-color: white']) ?>
		</fieldset>
		<div class="div-btns">
			<?= $this->Form->button('Login', ['class'=>'btn btn-login']) ?> 
		</div>
	<?= $this->Form->end() ?>
</div>