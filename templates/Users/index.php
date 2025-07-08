<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\User> $users
 */
?>
<div class="users index content">
    <?= $this->Html->link(__('New User'), ['action' => 'add'], ['class' => 'button float-right']) ?>
    <h3><?= __('Users') ?></h3>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th><?= $this->Paginator->sort('id') ?></th>
                    <th><?= $this->Paginator->sort('email') ?></th>
                    <th><?= $this->Paginator->sort('role') ?></th>
                    <th><?= $this->Paginator->sort('created') ?></th>
                    <th><?= $this->Paginator->sort('nickname') ?></th>
                    <th><?= $this->Paginator->sort('totexp') ?></th>
                    <th><?= $this->Paginator->sort('idCurrentPool') ?></th>
                    <th><?= $this->Paginator->sort('hearts') ?></th>
                    <th><?= $this->Paginator->sort('money') ?></th>
                    <th><?= $this->Paginator->sort('streak') ?></th>
                    <th><?= $this->Paginator->sort('idRank') ?></th>
                    <th><?= $this->Paginator->sort('type') ?></th>
                    <th><?= $this->Paginator->sort('birth') ?></th>
                    <th class="actions"><?= __('Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= $this->Number->format($user->id) ?></td>
                    <td><?= h($user->email) ?></td>
                    <td><?= h($user->role) ?></td>
                    <td><?= date_format($user->created, 'd/m/Y') ?></td>
                    <td><?= h($user->nickname) ?></td>
                    <td><?= $user->totexp === null ? '' : $this->Number->format($user->totexp) ?></td>
                    <td><?= $user->idCurrentPool === null ? '' : $this->Number->format($user->idCurrentPool) ?></td>
                    <td><?= $user->hearts === null ? '' : $this->Number->format($user->hearts) ?></td>
                    <td><?= $user->money === null ? '' : $this->Number->format($user->money) ?></td>
                    <td><?= $user->streak === null ? '' : $this->Number->format($user->streak) ?></td>
                    <td><?= $user->idRank === null ? '' : $this->Number->format($user->idRank) ?></td>
                    <td><?= $user->type === null ? '' : $this->Number->format($user->type) ?></td>
                    <td><?= $user->birth === null ? '' : date_format($user->birth, 'd/m/Y') ?></td>
                    <td class="actions">
                        <?= $this->Html->link(__('View'), ['action' => 'view', $user->id]) ?>
                        <?= $this->Html->link(__('Edit'), ['action' => 'edit', $user->id]) ?>
                        <?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $user->id], ['confirm' => __('Você confirma a exclusão de # {0}?', $user->id)]) ?>
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
        <p><?= $this->Paginator->counter(__('Página {{page}} de {{pages}}, mostrando {{current}} resultado(s) de {{count}}')) ?></p>
    </div>
</div>
