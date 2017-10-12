<h1><?php _e('Just Image Optimizer', \justImageOptimizer::TEXTDOMAIN); ?></h1>
<h2 class="nav-tab-wrapper">
	<a class="nav-tab <?php echo ($tab == 'dashboard' ? 'nav-tab-active' : ''); ?>" href="<?php echo admin_url() ?>upload.php?page=just-img-opt-dashboard"><?php _e('Dashboard', \justImageOptimizer::TEXTDOMAIN); ?></a>
	<a class="nav-tab <?php echo ($tab == 'settings' ? 'nav-tab-active' : ''); ?>" href="<?php echo admin_url() ?>upload.php?page=just-img-opt-settings"><?php _e('Settings', \justImageOptimizer::TEXTDOMAIN); ?></a>
	<a class="nav-tab <?php echo ($tab == 'connect' ? 'nav-tab-active' : ''); ?>" href="<?php echo admin_url() ?>upload.php?page=just-img-opt-connection"><?php _e('Connect', \justImageOptimizer::TEXTDOMAIN); ?></a>
</h2>