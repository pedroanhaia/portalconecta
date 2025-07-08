<script src="https://accounts.google.com/gsi/client" async defer></script>
<script src="https://cdn.jsdelivr.net/npm/jwt-decode@3.1.2/build/jwt-decode.min.js"></script>
<div id="g_id_onload"
	data-client_id="327034918972-lr0uuu25pbtpevqeja1fg7q4etpeu9j9.apps.googleusercontent.com"
	data-context="signin"
	data-ux_mode="popup"
	data-callback="handleCredentialResponse"
	data-auto_prompt="false">
</div>
<div class="users form">
	<?= $this->Flash->render() ?>
	<?= $this->Form->create(null) ?>
		<fieldset>
			<?= $this->Form->control('email', ['required' => true]) ?>
			<?= $this->Form->control('password', ['required' => true, 'label' => 'Senha']) ?>
		</fieldset>
		<div class="div-btns">
			<?= $this->Form->button('Login', ['class'=>'btn btn-login']) ?> 
		</div>
	<?= $this->Form->end() ?>
</div>