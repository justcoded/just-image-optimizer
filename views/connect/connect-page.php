<div class="wrap">
	<?php include(JUSTIMAGEOPTIMIZER_ROOT . '/views/_tabs.php'); ?>
	<form method="post" action="<?php get_permalink(); ?>" enctype="multipart/form-data">
		<table class="form-table">
			<tr>
				<th scope="row"><?php _e( 'Select an Image Optimization Service', \justImageOptimizer::TEXTDOMAIN ); ?></th>
				<td><label for="<?php echo esc_attr( $service_opt ); ?>">
						<input
							<?php echo( $service === 'google_insights' ? 'checked' : '' ); ?>
							type="checkbox"
							id="<?php echo esc_attr( $service_opt ); ?>"
							name="<?php echo esc_attr( $service_opt ); ?>"
							value="google_insights">
						<?php _e( 'Google PageSpeed Insights', \justImageOptimizer::TEXTDOMAIN ); ?></label>
				</td>
			</tr>
		</table>
		<div id="google_insights">
			<h3><?php _e( 'Google PageSpeed Insights Settings', \justImageOptimizer::TEXTDOMAIN ); ?></h3>
			<table class="form-table">
				<tr>
					<th scope="row"><?php _e( 'API Key', \justImageOptimizer::TEXTDOMAIN ); ?></th>
					<td>

						<input type="text" name="<?php echo esc_attr( $api_key_opt ); ?>"
						       id="<?php echo esc_attr( $api_key_opt ); ?>" class="regular-text"
						       value="<?php echo esc_attr( $api_key ); ?>"/>
						<p><a href="https://console.developers.google.com"
						      target="_blank">where i can find my API key?</a></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php _e( 'API Status', \justImageOptimizer::TEXTDOMAIN ); ?></th>
					<td class="api_status">
						<?php if ( $connection_status === '1' ) : ?>
							<p>OK</p>
						<?php else: ?>
							<button id="api_connect">Connect</button>
							<p class="notice-error"></p>
						<?php endif; ?>
					</td>
				</tr>
			</table>
		</div>
		<input <?php echo( $connection_status === '1' ? '' : 'disabled' ); ?>
			type="submit" name="submit-connect" id="submit-connect"
			class="button button-primary" value="Save">
	</form>
</div>
