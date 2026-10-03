<?= $this->extend('layouts/shell') ?>

<?= $this->section('title') ?>Members<?= $this->endSection() ?>
<?= $this->section('subtitle') ?><?= count($members) ?> in the club<?= $this->endSection() ?>
<?= $this->section('actions') ?>
<a class="button" href="<?= site_url('admin/members/new') ?>"><?= ui_icon('plus', 'icon icon--sm') ?>Add member</a>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
$today = date('Y-m-d');
$statusCounts = array_count_values(array_column($members, 'status'));
?>
<?php if ($members === []): ?>
    <?= view('partials/empty', ['icon' => 'users', 'title' => 'No members yet', 'text' => 'Add your first member, then record the joining fee to activate the membership.', 'actionUrl' => site_url('admin/members/new'), 'actionLabel' => 'Add member']) ?>
<?php else: ?>
    <div data-filter>
        <div class="toolbar">
            <label class="search">
                <span class="sr-only">Search members</span>
                <?= ui_icon('search', 'icon icon--sm') ?>
                <input type="search" placeholder="Search name, code or email" data-filter-input autocomplete="off">
            </label>
            <div class="chips" role="group" aria-label="Filter by status">
                <button class="chip" type="button" data-filter-chip="" aria-pressed="true">All<small><?= count($members) ?></small></button>
                <?php foreach (['active', 'inactive', 'banned'] as $status): ?>
                    <button class="chip" type="button" data-filter-chip="<?= $status ?>" aria-pressed="false"><?= ucfirst($status) ?><small><?= (int) ($statusCounts[$status] ?? 0) ?></small></button>
                <?php endforeach ?>
            </div>
        </div>

        <ul class="rows members">
            <?php foreach ($members as $member): ?>
                <?php
                $name = $member['first_name'] . ' ' . $member['last_name'];
                $overdue = $member['next_due_on'] !== null && $member['next_due_on'] < $today;
                ?>
                <li data-filter-item data-filter-status="<?= esc($member['status'], 'attr') ?>" data-filter-text="<?= esc(strtolower($name . ' ' . $member['member_code'] . ' ' . $member['email']), 'attr') ?>">
                    <a class="row row--member" href="<?= site_url("admin/members/{$member['user_id']}") ?>">
                        <?= ui_avatar($member['first_name'], $member['last_name']) ?>
                        <span class="row__identity">
                            <span class="row__title"><?= esc($name) ?></span>
                            <span class="row__sub"><?= esc($member['member_code']) ?>, <?= esc($member['email']) ?></span>
                        </span>
                        <span class="row__metric row__metric--plan">
                            <span class="row__label">Plan</span>
                            <span><?= esc($member['plan_name']) ?></span>
                        </span>
                        <span class="row__metric">
                            <span class="row__label">Points</span>
                            <span class="num"><?= esc(ui_points($balances[$member['user_id']] ?? 0)) ?></span>
                        </span>
                        <span class="row__metric row__metric--due<?= $overdue ? ' row__metric--late' : '' ?>">
                            <span class="row__label">Next due</span>
                            <span><?= esc(ui_date($member['next_due_on'])) ?></span>
                        </span>
                        <span class="pill pill--<?= esc($member['status'], 'attr') ?>"><?= esc($member['status']) ?></span>
                        <?= ui_icon('chevron', 'icon icon--sm row__chevron') ?>
                    </a>
                </li>
            <?php endforeach ?>
        </ul>
        <div hidden data-filter-empty>
            <?= view('partials/empty', ['icon' => 'search', 'title' => 'No members match', 'text' => 'Try a different name or clear the status filter.']) ?>
        </div>
    </div>
<?php endif ?>
<?= $this->endSection() ?>
