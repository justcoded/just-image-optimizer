<div class="wrap">
	<?php include( JUSTIMAGEOPTIMIZER_ROOT . '/views/_tabs.php' ); ?>
	<?php if ( ! empty( $errors ) ) : ?>
		<div class="update-nag">
			<strong><?php echo $errors; ?></strong>
		</div><br>
	<?php endif; ?>

	<?php if ( ! empty( $migrations ) ) : ?>
		<div class="update-nag">
			<strong>You need to Upgrade DataBase to continue using the plugin</strong>
		</div><br>
	<?php endif; ?>

	<p>We found out that you upgraded the plugin to the newer version. Your DataBase needs to be upgraded to continue
		using the plugin.</p>

	<?php if ( empty( $migrations ) && empty( $errors ) ) : ?>
		<div class="notice-success">
			<p>Just click the button below to upgrade.</p>
		</div>
	<?php endif; ?>

	<?php if ( ! empty( $warnings ) ) : ?>
		<div class="notice-warning">
			<h3>Warning! There are some problems with the upgrade.</h3>

			<?php foreach ( $warnings as $ver => $warning ) : ?>
				<h4><strong>v<?php echo $ver; ?> upgrade</strong></h4>
				<?php echo $warning; ?>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>

	<?php if ( ! empty( $migrations ) ) : ?>
		<div class="<?php echo empty( $warnings ) ? 'notice-success' : 'notice-warning'; ?>">
			<p>We will launch several upgrade scripts:</p>
			<ul>
				<?php foreach ( $migrations as $ver => $m ) : ?>
					<li>v<?php echo $ver; ?> upgrade</li>
				<?php endforeach; ?>
			</ul>
		</div>
	<?php endif; ?>

	<form method="POST">
		<input type="submit" value="Upgrade" name="upgrade_storage" class="button button-primary"
			<?php if ( ! empty( $errors ) ) {
				echo 'disabled';
			} ?>
		/>
	</form>

