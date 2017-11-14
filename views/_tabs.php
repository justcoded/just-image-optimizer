<?php
use justimageoptimizer\models;
?>
<h1><?php _e('Just Image Optimizer', \JustImageOptimizer::TEXTDOMAIN); ?></h1>
<h2 class="nav-tab-wrapper">
	<?php if( get_option( models\Settings::DB_OPT_IS_SECOND ) ) : ?>
	<a class="nav-tab <?php echo ($tab == 'dashboard' ? 'nav-tab-active' : ''); ?>" href="<?php echo admin_url() ?>upload.php?page=just-img-opt-dashboard"><?php _e('Dashboard', \JustImageOptimizer::TEXTDOMAIN); ?></a>
	<?php endif; ?>
	<?php if( get_option( models\Connect::DB_OPT_IS_FIRST ) ) : ?>
	<a class="nav-tab <?php echo ($tab == 'settings' ? 'nav-tab-active' : ''); ?>" href="<?php echo admin_url() ?>upload.php?page=just-img-opt-settings"><?php _e('Settings', \JustImageOptimizer::TEXTDOMAIN); ?></a>
	<?php endif; ?>
	<a class="nav-tab <?php echo ($tab == 'connect' ? 'nav-tab-active' : ''); ?>" href="<?php echo admin_url() ?>upload.php?page=just-img-opt-connection"><?php _e('Connect', \JustImageOptimizer::TEXTDOMAIN); ?></a>
</h2>