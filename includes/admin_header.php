<?php
/**
 * Admin Header with Navigation
 */
?>
<nav class="admin-nav">
    <div class="container nav-container">
        <div class="nav-brand">
            <i class="fas fa-shield-halved"></i>
            <span><?php echo __('site_title'); ?></span>
        </div>
        <button class="nav-toggle" id="navToggle" aria-label="Toggle navigation">
            <i class="fas fa-bars"></i>
        </button>
        <ul class="nav-menu" id="navMenu">
            <li><a href="index.php" class="<?php echo $currentPage === 'index' ? 'active' : ''; ?>">
                <i class="fas fa-chart-line"></i> <?php echo __('dashboard'); ?>
            </a></li>
            <li><a href="signatories.php" class="<?php echo $currentPage === 'signatories' ? 'active' : ''; ?>">
                <i class="fas fa-pen-nib"></i> <?php echo __('signatories'); ?>
            </a></li>
            <li><a href="companies.php" class="<?php echo $currentPage === 'companies' ? 'active' : ''; ?>">
                <i class="fas fa-building"></i> <?php echo __('companies'); ?>
            </a></li>
            <li><a href="logs.php" class="<?php echo $currentPage === 'logs' ? 'active' : ''; ?>">
                <i class="fas fa-clipboard-list"></i> <?php echo __('access_logs'); ?>
            </a></li>
            <li>
                <a href="?lang=<?php echo __('switch_lang_code'); ?>" class="lang-switch">
                    <i class="fas fa-language"></i> <?php echo __('switch_lang'); ?>
                </a>
            </li>
            <li>
                <a href="logout.php" class="nav-logout">
                    <i class="fas fa-sign-out-alt"></i> <?php echo __('logout'); ?>
                </a>
            </li>
        </ul>
    </div>
</nav>
<main class="admin-main">
    <div class="container">
