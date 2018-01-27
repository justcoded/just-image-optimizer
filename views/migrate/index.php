<?php
/* @var $migrations \jcf\core\Migration[] */
/* @var $warnings  array */
/* @var $errors    array */
?>
<div class="wrap">
	<?php include( JUSTIMAGEOPTIMIZER_ROOT . '/views/_tabs.php' ); ?>

	<?php do_action('jcf_print_admin_notice'); ?>

	<p>We found out that you upgraded the plugin to the newer version. Your field settings needs to be upgraded to continue using the plugin.</p>
	<p>Please make sure <strong>you have a backup of your current settings</strong> (database dump if you store them in database or json config file).</p>

	<?php if (empty($migrations) && empty($errors)) : ?>
		<div class="jcf_well notice-success">
			<p>Just click the button below to upgrade.</p>
		</div>
	<?php endif; ?>

	<?php if (!empty($warnings)) : ?>
		<div class="jcf_well notice-warning">
			<h3>Warning! There are some problems with the upgrade.</h3>

			<?php foreach ($warnings as $ver => $warning) : ?>
				<h4><strong>v<?php echo $ver; ?> upgrade</strong></h4>
				<?php echo $warning; ?>
			<?php endforeach; ?>

			<h4><u>If you're unable to update your theme templates - please downgrade to the previous version.</u></h4>
			<p>You can find previous versions on <a href="https://wordpress.org/plugins/just-custom-fields/developers/" target="_blank">wordpress repository</a>.</p>
			<script>var jcf_migrate_errors = true;</script>
		</div>
	<?php endif; ?>

	<?php if (!empty($migrations)) : ?>
		<div class="jcf_well <?php echo empty($warnings)? 'notice-success' : 'notice-warning'; ?>">
			<p>We will launch several upgrade scripts:</p>
			<ul class="jcf_list">
			<?php foreach ($migrations as $ver => $m) : ?>
				<li>v<?php echo $ver; ?> upgrade</li>
			<?php endforeach; ?>
			</ul>
		</div>
	<?php endif; ?>

	<form method="POST" onsubmit="if(jcf_migrate_errors && !confirm('Are you sure you will be able to update your code?')) return false;">
		<input type="submit" value="Upgrade" name="upgrade_storage" class="button button-primary"
			   <?php if(!empty($errors)) echo 'disabled'; ?>
			/>
	</form>

