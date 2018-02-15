<?php
use JustCoded\WP\ImageOptimizer\models;
?>
<h1><?php _e('Just Image Optimizer', \JustImageOptimizer::TEXTDOMAIN); ?></h1>
<h2 class="nav-tab-wrapper">
<?php if ( models\Connect::connected() ) : ?>
	<a class="nav-tab <?php echo ($tab == 'dashboard' ? 'nav-tab-active' : ''); ?>" href="<?php echo admin_url() ?>upload.php?page=just-img-opt-dashboard"><?php _e('Dashboard', \JustImageOptimizer::TEXTDOMAIN); ?></a>
	<a class="nav-tab <?php echo ($tab == 'settings' ? 'nav-tab-active' : ''); ?>" href="<?php echo admin_url() ?>upload.php?page=just-img-opt-settings"><?php _e('Settings', \JustImageOptimizer::TEXTDOMAIN); ?></a>
<?php endif; ?>
	<a class="nav-tab <?php echo ($tab == 'connect' ? 'nav-tab-active' : ''); ?>" href="<?php echo admin_url() ?>upload.php?page=just-img-opt-connection"><?php _e('Connect', \JustImageOptimizer::TEXTDOMAIN); ?></a>
	<?php if ( models\Connect::connected() ) : ?>
	<a class="nav-tab <?php echo ($tab == 'log' ? 'nav-tab-active' : ''); ?>" href="<?php echo admin_url() ?>upload.php?page=just-img-opt-log"><?php _e('Optimization Log', \JustImageOptimizer::TEXTDOMAIN); ?></a>
	<?php endif; ?>
	<?php if( version_compare( \JustImageOptimizer::$opt_version, \JustImageOptimizer::$version, '<' ) ) : ?>
	<a class="nav-tab <?php echo ($tab == 'migrate' ? 'nav-tab-active' : ''); ?>" href="<?php echo admin_url() ?>upload.php?page=just-img-opt-migrate"><?php _e('Migration', \JustImageOptimizer::TEXTDOMAIN); ?></a>
	<?php endif; ?>
</h2>