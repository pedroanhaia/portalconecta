<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\OrdersLog[]|\Cake\Collection\CollectionInterface $ordersLog
 */
?>
<div class="ordersLog index content">
    <?= $this->Html->link(__('New Orders Log'), ['action' => 'add'], ['class' => 'button float-right']) ?>
    <h3><?= __('Orders Log') ?></h3>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th><?= $this->Paginator->sort('id') ?></th>
                    <th><?= $this->Paginator->sort('id_order') ?></th>
                    <th><?= $this->Paginator->sort('type') ?></th>
                    <th><?= $this->Paginator->sort('role') ?></th>
                    <th><?= $this->Paginator->sort('created') ?></th>
                    <th><?= $this->Paginator->sort('modified') ?></th>
                    <th class="actions"><?= __('Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($ordersLog as $ordersLog): ?>
                <tr>
                    <td><?= $this->Number->format($ordersLog->id) ?></td>
                    <td><?= $this->Number->format($ordersLog->id_order) ?></td>
                    <td><?= $this->Number->format($ordersLog->type) ?></td>
                    <td><?= $this->Number->format($ordersLog->role) ?></td>
                    <td><?= h($ordersLog->created) ?></td>
                    <td><?= h($ordersLog->modified) ?></td>
                    <td class="actions">
                        <?= $this->Html->link(__('View'), ['action' => 'view', $ordersLog->id]) ?>
                        <?= $this->Html->link(__('Edit'), ['action' => 'edit', $ordersLog->id]) ?>
                        <?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $ordersLog->id], ['confirm' => __('Are you sure you want to delete # {0}?', $ordersLog->id)]) ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="paginator">
        <ul class="pagination">
            <?= $this->Paginator->first('<< ' . __('first')) ?>
            <?= $this->Paginator->prev('< ' . __('previous')) ?>
            <?= $this->Paginator->numbers() ?>
            <?= $this->Paginator->next(__('next') . ' >') ?>
            <?= $this->Paginator->last(__('last') . ' >>') ?>
        </ul>
        <p><?= $this->Paginator->counter(__('Page {{page}} of {{pages}}, showing {{current}} record(s) out of {{count}} total')) ?></p>
    </div>
</div>
