<?php $this->layout('base', ['activeItem' => 'connections']); ?>
<?php $this->start('content'); ?>
    <h1><?=$this->t('Connections'); ?></h1>
    <?php foreach ($vpnConnections as $profileId => $connectionList): ?>
        <h2 id="<?=$this->e($profileId); ?>"><?=$this->e($idNameMapping[$profileId]); ?></h2>
        <?php if (0 === count($connectionList)): ?>
            <p class="plain"><?=$this->t('No clients connected.'); ?></p>
        <?php else: ?>
            <table class="tbl">
            <thead>
                <tr>
                    <th><?=$this->t('User ID'); ?></th>
                    <th><?=$this->t('Name'); ?></th>
                    <th><?=$this->t('IP address'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($connectionList as $connection): ?>
                <tr>
                    <td>
                        <a href="<?=$this->e($requestRoot); ?>user?user_id=<?=$this->e($connection['user_id'], 'rawurlencode'); ?>" title="<?=$this->e($connection['user_id']); ?>"><?=$this->etr($connection['user_id'], 25); ?></a>
                    </td>
                    <td>
                        <span title="<?=$this->e($connection['display_name']); ?>"><?=$this->etr($connection['display_name'], 25); ?></span>
                    </td>
                    <td>
                        <ul>
                            <?php foreach ($connection['virtual_address'] as $ip): ?>
                            <li><code><?=$this->e($ip); ?></code></li>
                            <?php endforeach; ?>
                        </ul>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            </table>
        <?php endif; ?>
    <?php endforeach; ?>
<?php $this->stop('content'); ?>
