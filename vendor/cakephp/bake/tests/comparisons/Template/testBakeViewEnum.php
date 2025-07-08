<?php
/**
 * @var \Bake\Test\App\View\AppView $this
 * @var \Cake\Datasource\EntityInterface $bakeUser
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Html->link(__('Edit Bake User'), ['action' => 'edit', $bakeUser->id], ['class' => 'side-nav-item']) ?>
            <?= $this->Form->postLink(__('Delete Bake User'), ['action' => 'delete', $bakeUser->id], ['confirm' => __('Are you sure you want to delete # {0}?', $bakeUser->id), 'class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('List Bake Users'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('New Bake User'), ['action' => 'add'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-80">
        <div class="bakeUsers view content">
            <h3><?= h($bakeUser->id) ?></h3>
            <table>
                <tr>
                    <th><?= __('Username') ?></th>
                    <td><?= h($bakeUser->username) ?></td>
                </tr>
                <tr>
                    <th><?= __('Id') ?></th>
                    <td><?= $this->Number->format($bakeUser->id) ?></td>
                </tr>
                <tr>
                    <th><?= __('Status') ?></th>
                    <td><?= $bakeUser->status === null ? '' : h($bakeUser->status->label()) ?></td>
                </tr>
                <tr>
                    <th><?= __('Created') ?></th>
                    <td><?= h($bakeUser->created) ?></td>
                </tr>
                <tr>
                    <th><?= __('Updated') ?></th>
                    <td><?= h($bakeUser->updated) ?></td>
                </tr>
            </table>
        </div>
    </div>
</div>