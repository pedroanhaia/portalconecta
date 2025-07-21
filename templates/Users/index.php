<div class="content">
	<?php if($userAdmin) { ?>
		<div class="row">
			<div class="col-12">
				<?= $this->Html->link(__('Novo usuáiro'), ['action' => 'add'], ['class' => 'btn btn-lg btn-success float-right']) ?>
				<h3> Usuários </h3>
			</div>
		</div>
		<ul class="nav nav-tabs" id="orderTabs" role="tablist">
			<li class="nav-item" role="presentation">
				<button class="nav-link active" id="customers-tab" data-bs-toggle="tab" data-bs-target="#customers" type="button" role="tab" aria-controls="customers" aria-selected="true"> Clientes (<?= count($usersCustomer) ?>) </button>
			</li>
			<li class="nav-item" role="presentation">
				<button class="nav-link" id="admins-tab" data-bs-toggle="tab" data-bs-target="#admins" type="button" role="tab" aria-controls="admins" aria-selected="false"> Admins (<?= count($usersAdmin) ?>)</button>
			</li>
		</ul>
		<div class="tab-content">
			<div class="tab-pane fade show active" id="customers">
				<div class="table-responsive">
					<table class="table table-hover table-row-clickable" id="table-customers">
						<thead class="text-primary">
							<tr>
								<th> Nome </th>
								<th> E-mail </th>
								<th> Empresa </th>
								<th> Função </th>
								<th class="actions"> Ações </th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($usersCustomer as $reg): ?>
								<tr>
									<td> <?= $reg->name ?> </td>
									<td> <?= $reg->email ?> </td>
									<td> <?= $reg->customer->cnpj . ' - ' . $reg->customer->name ?> </td>
									<td> <?= UsersType($reg->type) ?> </td>
									<td class="actions">
										<?= $this->Html->link('<i class="fa fa-edit"></i>', ["action" => "edit", $reg->id], ['rel' => 'tooltip', 'title' => 'Editar', 'class' => 'btn btn-warning text-white btn-sm', 'id' => $reg->id, 'escape' => false]); ?>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			</div>
			<div class="tab-pane fade" id="admins">
				<div class="table-admins">
					<table class="table table-hover table-row-clickable" id="table-admins">
						<thead class="text-primary">
							<tr>
								<th> Nome </th>
								<th> E-mail </th>
								<th class="actions"> Ações </th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($usersAdmin as $reg): ?>
								<tr>
									<td> <?= $reg->name ?> </td>
									<td> <?= $reg->email ?> </td>
									<td class="actions">
										<?= $this->Html->link('<i class="fa fa-edit"></i>', ["action" => "edit", $reg->id], ['rel' => 'tooltip', 'title' => 'Editar', 'class' => 'btn btn-warning text-white btn-sm', 'id' => $reg->id, 'escape' => false]); ?>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	<?php } else { ?>
		<div class="row">
			<div class="col-12">
				<div class="table-responsive">
					<table class="table table-hover table-row-clickable" id="table-customers">
						<thead class="text-primary">
							<tr>
								<th> Nome </th>
								<th> E-mail </th>
								<th> Função </th>
								<th class="actions"> Ações </th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($usersCustomer as $reg): ?>
								<tr>
									<td> <?= $reg->name ?> </td>
									<td> <?= $reg->email ?> </td>
									<td> <?= UsersType($reg->type) ?> </td>
									<td class="actions">
										<?= $this->Html->link('<i class="fa fa-edit"></i>', ["action" => "edit", $reg->id], ['rel' => 'tooltip', 'title' => 'Editar', 'class' => 'btn btn-warning text-white btn-sm', 'id' => $reg->id, 'escape' => false]); ?>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	<?php }  ?>
</div>
<script>
	$(document).ready(function() {
		var dataTableAdmins = $('#table-admins').DataTable({"pageLength": 10, "language": datatableOptionsLanguage, "order" : [0, "asc"]});
		var dataTableCustomers = $('#table-customers').DataTable({"pageLength": 10, "language": datatableOptionsLanguage, "order" : [0, "asc"]});

		$('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
			var targetTab = $(e.target).attr('href');
            console.log(targetTab);
			if (targetTab === '#admins') {
				dataTableAdmins.columns.adjust().draw();
			} else if (targetTab === '#customers') {
				dataTableCustomers.columns.adjust().draw();
			} 
		});
	});
</script>
