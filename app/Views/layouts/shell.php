<?= $this->extend('layouts/base') ?>

<?= $this->section('body') ?>
<?php
$user = auth()->user();
$isAdmin = (bool) $user?->inGroup('admin');
$displayName = (string) ($user?->username ?? '');
$home = $isAdmin ? 'admin' : 'portal';
?>
<a class="sr-only" href="#main">Skip to content</a>
<div class="shell">
    <aside class="sidebar" id="sidebar" aria-label="Primary">
        <div class="sidebar__top">
            <a class="brand" href="<?= site_url($home) ?>" title="FLY VIP">
                <?= view('partials/brand_mark') ?>
                <span class="brand__text">FLY VIP <small><?= $isAdmin ? 'ADMIN' : 'MEMBER' ?></small></span>
            </a>
            <button class="icon-button sidebar__collapse" type="button" data-sidebar-collapse aria-label="Collapse sidebar" aria-pressed="false" title="Collapse sidebar"><?= ui_icon('collapse') ?></button>
            <button class="icon-button sidebar__close" type="button" data-sidebar-close aria-label="Close menu" hidden><?= ui_icon('x') ?></button>
        </div>
        <nav class="nav" aria-label="Main">
            <?php foreach (ui_nav($isAdmin) as $group): ?>
                <div class="nav__group">
                    <span class="nav__label"><?= esc($group['label']) ?></span>
                    <?php foreach ($group['items'] as [$path, $label, $icon]): ?>
                        <a class="nav__link" href="<?= site_url($path) ?>" title="<?= esc($label, 'attr') ?>" <?= ui_is_current($path) ? 'aria-current="page"' : '' ?>>
                            <?= ui_icon($icon) ?><span class="nav__text"><?= esc($label) ?></span>
                        </a>
                    <?php endforeach ?>
                </div>
            <?php endforeach ?>
        </nav>
        <div class="sidebar__user">
            <?= ui_avatar($displayName) ?>
            <div class="sidebar__who">
                <strong><?= esc($displayName) ?></strong>
                <a href="<?= site_url('account/password') ?>">Account</a>
            </div>
            <form class="sidebar__signout" method="post" action="<?= url_to('logout') ?>">
                <?= csrf_field() ?>
                <button class="icon-button" type="submit" aria-label="Sign out" title="Sign out"><?= ui_icon('logout') ?></button>
            </form>
        </div>
    </aside>
    <div class="sidebar-backdrop" data-sidebar-backdrop></div>

    <div class="stage">
        <header class="stage__header">
            <div class="stage__heading">
                <button class="icon-button stage__menu" type="button" data-sidebar-open aria-controls="sidebar" aria-expanded="false" aria-label="Open menu"><?= ui_icon('menu') ?></button>
                <div>
                    <h1><?= $this->renderSection('title') ?></h1>
                    <p><?= $this->renderSection('subtitle') ?></p>
                </div>
            </div>
            <div class="stage__actions"><?= $this->renderSection('actions') ?></div>
            <a class="stage__avatar" href="<?= site_url('account/password') ?>" aria-label="Account and password" title="<?= esc($displayName, 'attr') ?>"><?= ui_avatar($displayName) ?></a>
        </header>

        <main class="stage__body" id="main">
            <div class="page">
                <?= view('partials/error_summary') ?>
                <?= $this->renderSection('content') ?>
            </div>
        </main>
    </div>
</div>
<?= $this->endSection() ?>
