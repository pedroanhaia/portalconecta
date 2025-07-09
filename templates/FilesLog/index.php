<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\FilesLog[]|\Cake\Collection\CollectionInterface $filesLog
 */
?>
<div class="filesLog index content">
    <?= $this->Html->link(__('New Files Log'), ['action' => 'add'], ['class' => 'button float-right']) ?>
    <h3><?= __('Files Log') ?></h3>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th><?= $this->Paginator->sort('id') ?></th>
                    <th><?= $this->Paginator->sort('id_file') ?></th>
                    <th><?= $this->Paginator->sort('type') ?></th>
                    <th><?= $this->Paginator->sort('role') ?></th>
                    <th><?= $this->Paginator->sort('created') ?></th>
                    <th><?= $this->Paginator->sort('modified') ?></th>
                    <th class="actions"><?= __('Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($filesLog as $filesLog): ?>
                <tr>
                    <td><?= $this->Number->format($filesLog->id) ?></td>
                    <td><?= $this->Number->format($filesLog->id_file) ?></td>
                    <td><?= $this->Number->format($filesLog->type) ?></td>
                    <td><?= $this->Number->format($filesLog->role) ?></td>
                    <td><?= h($filesLog->created) ?></td>
                    <td><?= h($filesLog->modified) ?></td>
                    <td class="actions">
                        <?= $this->Html->link(__('View'), ['action' => 'view', $filesLog->id]) ?>
                        <?= $this->Html->link(__('Edit'), ['action' => 'edit', $filesLog->id]) ?>
                        <?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $filesLog->id], ['confirm' => __('Are you sure you want to delete # {0}?', $filesLog->id)]) ?>
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
