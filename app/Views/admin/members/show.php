<?= $this->extend('layouts/shell') ?>

<?php $fullName = $member['first_name'] . ' ' . $member['last_name']; ?>
<?= $this->section('title') ?><?= esc($fullName) ?><?= $this->endSection() ?>
<?= $this->section('subtitle') ?><?= esc($member['member_code']) ?>, <?= esc($member['plan_name'] ?? 'Sub-member') ?><?= $this->endSection() ?>
<?= $this->section('actions') ?>
<a class="button button--quiet" href="<?= site_url("admin/members/{$member['user_id']}/edit") ?>"><?= ui_icon('pencil', 'icon icon--sm') ?>Edit</a>
<?php if ($member['plan_id'] !== null): ?>
    <a class="button" href="<?= site_url('admin/payments/new?user_id=' . $member['user_id']) ?>"><?= ui_icon('card', 'icon icon--sm') ?>Record payment</a>
<?php endif ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php $primary = $member['plan_id'] !== null; ?>
<div class="profile-head">
    <?= ui_avatar($member['first_name'], $member['last_name'], 'avatar--lg') ?>
    <div class="profile-head__who">
        <p class="profile-head__name"><?= esc($fullName) ?> <span class="pill pill--<?= esc($member['status'], 'attr') ?>"><?= esc($member['status']) ?></span></p>
        <p class="muted"><?= esc($member['email']) ?>, <?= esc($member['cellphone'] ?? 'no phone on file') ?></p>
    </div>
</div>

<dl class="stats">
    <div class="stats__cell">
        <dt><?= ui_icon('star', 'icon icon--sm') ?>Points</dt>
        <dd class="num"><?= esc(ui_points($balance)) ?></dd>
    </div>
    <div class="stats__cell">
        <dt><?= ui_icon('calendar', 'icon icon--sm') ?>Next payment due</dt>
        <dd><?= esc(ui_date($member['next_due_on'])) ?></dd>
    </div>
    <div class="stats__cell">
        <dt><?= ui_icon('layers', 'icon icon--sm') ?>Plan</dt>
        <dd><?= esc($member['plan_name'] ?? 'Sub-member') ?></dd>
    </div>
</dl>

<div class="detail">
    <div class="detail__main">
        <?php if ($primary): ?>
            <?php
            $flights = view('partials/flight_list', [
                'reservations' => $reservations,
                'linkPattern' => 'admin/reservations/{id}',
                'actionsFor' => static fn (array $reservation): array => [],
            ]);
            ?>
            <?php if ($reservations === []): ?>
                <section class="page-section">
                    <div class="section-head"><h2>Reservations</h2></div>
                    <?= view('partials/empty', ['icon' => 'plane', 'title' => 'No flights yet', 'text' => 'Book a flight for this member and it will show up here.', 'actionUrl' => site_url('admin/reservations/new'), 'actionLabel' => 'New reservation']) ?>
                </section>
            <?php else: ?>
                <?= $flights ?>
            <?php endif ?>

            <section class="page-section">
                <div class="section-head">
                    <h2>Points history</h2>
                    <?= ui_sparkline(array_column(array_reverse($ledger), 'balance_after'), 140, 32) ?>
                </div>
                <?php if ($ledger === []): ?>
                    <?= view('partials/empty', ['icon' => 'star', 'title' => 'No points movements', 'text' => 'Payments and bookings will build the ledger.']) ?>
                <?php else: ?>
                    <?= view('partials/ledger', ['entries' => $ledger]) ?>
                <?php endif ?>
            </section>

            <section class="page-section">
                <div class="section-head"><h2>Payments</h2></div>
                <?php if ($payments === []): ?>
                    <?= view('partials/empty', ['icon' => 'card', 'title' => 'No payments recorded', 'text' => 'The joining fee activates the membership.', 'actionUrl' => site_url('admin/payments/new?user_id=' . $member['user_id']), 'actionLabel' => 'Record payment']) ?>
                <?php else: ?>
                    <div class="table-wrap">
                        <table class="data">
                            <thead><tr><th>Payment</th><th>Date</th><th>Type</th><th>Remark</th><th class="num">Amount</th></tr></thead>
                            <tbody>
                                <?php foreach ($payments as $payment): ?>
                                    <tr>
                                        <td><a href="<?= site_url("admin/payments/{$payment['id']}") ?>">#<?= (int) $payment['id'] ?></a></td>
                                        <td><?= esc(ui_date($payment['paid_on'])) ?></td>
                                        <td><span class="pill pill--accent pill--plain"><?= esc($payment['type']) ?></span></td>
                                        <td><?= esc($payment['remark']) ?></td>
                                        <td class="num"><?= esc(ui_points($payment['amount'])) ?></td>
                                    </tr>
                                <?php endforeach ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif ?>
            </section>

            <section class="page-section">
                <div class="section-head"><h2>Sub-members</h2></div>
                <?php if ($children !== []): ?>
                    <ul class="rows">
                        <?php foreach ($children as $child): ?>
                            <li>
                                <a class="row row--person" href="<?= site_url("admin/members/{$child['user_id']}") ?>">
                                    <?= ui_avatar($child['first_name'], $child['last_name']) ?>
                                    <span>
                                        <span class="row__title"><?= esc($child['first_name'] . ' ' . $child['last_name']) ?></span>
                                        <span class="row__sub"><?= esc($child['member_code']) ?>, <?= esc($child['username']) ?></span>
                                    </span>
                                    <span class="pill pill--accent pill--plain"><?= esc($child['contact_type']) ?></span>
                                </a>
                            </li>
                        <?php endforeach ?>
                    </ul>
                <?php endif ?>
                <details class="disclosure"<?= $children === [] || ui_error('username') !== '' ? ' open' : '' ?>>
                    <summary><?= ui_icon('plus', 'icon icon--sm') ?>Add sub-member</summary>
                    <form class="form" method="post" action="<?= site_url("admin/members/{$member['user_id']}/sub-members") ?>">
                        <?= csrf_field() ?>
                        <div class="fields">
                            <?= ui_field('first_name', 'First name', 'text', null, ['required' => true]) ?>
                            <?= ui_field('last_name', 'Last name', 'text', null, ['required' => true]) ?>
                            <?= ui_field('username', 'Username', 'text', null, ['required' => true]) ?>
                            <?= ui_field('email', 'Email', 'email', null, ['required' => true]) ?>
                            <?= ui_field('password', 'Password', 'password', null, ['autocomplete' => 'new-password', 'required' => true]) ?>
                            <?= ui_select('contact_type_id', 'Relationship', $contactTypes) ?>
                        </div>
                        <div class="form-actions"><button class="button" type="submit">Add sub-member</button></div>
                    </form>
                </details>
            </section>
        <?php endif ?>
    </div>

    <aside class="detail__aside">
        <section class="page-section">
            <h2>Details</h2>
            <dl class="facts">
                <div><dt>Username</dt><dd><?= esc($member['username']) ?></dd></div>
                <div><dt>Email</dt><dd><?= esc($member['email']) ?></dd></div>
                <div><dt>Phone</dt><dd><?= esc($member['cellphone'] ?? '—') ?></dd></div>
                <div><dt>Company</dt><dd><?= esc($member['company'] ?? '—') ?></dd></div>
                <div><dt>Passport</dt><dd><?= esc($member['passport_number'] ?? '—') ?><br><span class="muted">Due <?= esc(ui_date($member['passport_due_on'])) ?></span></dd></div>
            </dl>
        </section>

        <section class="page-section">
            <h2>Membership status</h2>
            <form class="form" method="post" action="<?= site_url("admin/members/{$member['user_id']}/status") ?>">
                <?= csrf_field() ?>
                <?= ui_segmented('status', 'Status', ['inactive' => 'Inactive', 'active' => 'Active', 'banned' => 'Banned'], $member['status']) ?>
                <div class="form-actions form-actions--bare"><button class="button button--quiet" type="submit">Update status</button></div>
            </form>
        </section>

        <section class="page-section danger-zone">
            <h2>Remove member</h2>
            <p class="muted">Removes this member and their sub-members from the club.</p>
            <form method="post" action="<?= site_url("admin/members/{$member['user_id']}/delete") ?>" data-confirm="This removes <?= esc($fullName, 'attr') ?> and every sub-member linked to them." data-confirm-title="Remove this member?" data-confirm-action="Remove member">
                <?= csrf_field() ?>
                <button class="button button--danger" type="submit"><?= ui_icon('trash', 'icon icon--sm') ?>Remove member</button>
            </form>
        </section>
    </aside>
</div>
<?= $this->endSection() ?>
