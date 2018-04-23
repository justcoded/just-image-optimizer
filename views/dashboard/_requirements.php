<?php
if ( ! isset( $force_requirements_check ) ) {
	$force_requirements_check = false;
}
?>
<?php if ( ! \JustImageOptimizer::$settings->check_requirements( $force_requirements_check ) ) : ?>
	<div class="update-nag error-nag">
		<strong>Please check that all requirements are met:</strong>
		<ul>
			<li>PHP version should be at least 7.0 (you have <?php echo esc_html( phpversion() ); ?>).</li>
			<li>
				<?php
				echo esc_html(
					sprintf(
						__( 'Make sure directory "%1$s" is writable.',
						\JustImageOptimizer::TEXTDOMAIN ), WP_CONTENT_DIR
					)
				);
				?>
			</li>
			<li>Your site is online and can be accessible by <?php echo esc_html( \JustImageOptimizer::$service->name() ); ?></li>
		</ul>
	</div>
<?php endif; ?>