<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\FilesLog $filesLog
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Html->link(__('Edit Files Log'), ['action' => 'edit', $filesLog->id], ['class' => 'side-nav-item']) ?>
            <?= $this->Form->postLink(__('Delete Files Log'), ['action' => 'delete', $filesLog->id], ['confirm' => __('Are you sure you want to delete # {0}?', $filesLog->id), 'class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('List Files Log'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('New Files Log'), ['action' => 'add'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column-responsive column-80">
        <div class="filesLog view content">
            <h3><?= h($filesLog->id) ?></h3>
            <table>
                <tr>
                    <th><?= __('Id') ?></th>
                    <td><?= $this->Number->format($filesLog->id) ?></td>
                </tr>
                <tr>
                    <th><?= __('Id File') ?></th>
                    <td><?= $this->Number->format($filesLog->id_file) ?></td>
                </tr>
                <tr>
                    <th><?= __('Type') ?></th>
                    <td><?= $this->Number->format($filesLog->type) ?></td>
                </tr>
                <tr>
                    <th><?= __('Role') ?></th>
                    <td><?= $this->Number->format($filesLog->role) ?></td>
                </tr>
                <tr>
                    <th><?= __('Created') ?></th>
                    <td><?= h($filesLog->created) ?></td>
                </tr>
                <tr>
                    <th><?= __('Modified') ?></th>
                    <td><?= h($filesLog->modified) ?></td>
                </tr>
            </table>
            <div class="text">
                <strong><?= __('Message') ?></strong>
                <blockquote>
                    <?= $this->Text->autoParagraph(h($filesLog->message)); ?>
                </blockquote>
            </div>
        </div>
    </div>
</div>
