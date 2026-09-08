<?php defined('WPINC') || die; ?>
<div class="gp-top-bar--root <?php echo !empty($alsnp__gp_sidebar_has_adaptive_margin) ? 'gp-sidebar--adaptive-margin' : ''; ?>">
    <a href="<?php echo admin_url('admin-post.php?action=all_snippets__postaction__button_update__vsh0_0_4'); ?>"
        class="gp-top-bar--refresh-license-btn">
        <span class="dashicons dashicons-update"></span>
        Refresh License
    </a>
    <a href="https://allsnippets.com/my-account" target="_blank" class="gp-top-bar--activate-license-btn">
        Activate License
        <span class="dashicons dashicons-lock"></span>
    </a>
    <button class="gp-top-bar--advanced-settings-btn">
        Advanced Settings
        <span class="dashicons dashicons-admin-settings"></span>
    </button>
</div>