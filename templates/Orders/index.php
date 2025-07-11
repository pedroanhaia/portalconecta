<div class="content">
	<div class="row">
		<div class="col-12">
			<?= $this->Html->link(__('Novo pedido'), ['action' => 'add'], ['class' => 'btn btn-lg btn-success float-right']) ?>
			<h3> Pedidos </h3>
		</div>
	</div>
	<div class="table-responsive">
		<table class="table table-hover table-row-clickable" id="table-orders">
			<thead class="text-primary">
				<tr>
					<th> # </th>
					<th> Empresa </th>
					<th> Status </th>
					<th> Última alteração  </th>
					<th class="actions"> Ações </th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($orders as $reg): ?>
					<tr>
						<td> <?= $reg->number ?> </td>
						<td> <?= $reg->customer->cnpj . ' - ' . $reg->customer->name ?> </td>
						<td> <?= PedidosStatus($reg->status) ?> </td>
						<td> <?= date_format($reg->modified, 'd/m/Y - H:i') ?> </td>
						<td class="actions">
							<?= $this->Html->link('<i class="fa fa-eye"></i>', ["action" => "view", $reg->id], ['rel' => 'tooltip', 'title' => 'Visualizar', 'class' => 'btn btn-info text-white btn-sm', 'id' => $reg->id, 'escape' => false]); ?>
							<?= $this->Html->link('<i class="fa fa-edit"></i>', ["action" => "edit", $reg->id], ['rel' => 'tooltip', 'title' => 'Editar', 'class' => 'btn btn-warning text-white btn-sm', 'id' => $reg->id, 'escape' => false]); ?>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
</div>
<script>
	$(document).ready(function() {
		var dataTableCustomers = $('#table-orders').DataTable({"pageLength": 10, "language": datatableOptionsLanguage, "order" : [0, "asc"]});
	});
</script>