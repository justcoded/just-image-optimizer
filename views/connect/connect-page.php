<div class="wrap">
	<?php include( JUSTIMAGEOPTIMIZER_ROOT . '/views/_tabs.php' ); ?>
	<form method="post" action="<?php get_permalink(); ?>" enctype="multipart/form-data">
		<table class="form-table">
			<tr>
				<th scope="row"><?php _e( 'Select an Image Optimization Service', \justImageOptimizer::TEXTDOMAIN ); ?></th>
				<td><label for="service">
						<input
							<?php echo( get_option( $model::DB_OPT_SERVICE ) === 'google_insights' ? 'checked' : '' ); ?>
							type="checkbox"
							id="service"
							name="service"
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

						<input type="text" name="api_key"
						       id="api_key" class="regular-text"
						       value="<?php echo esc_attr( get_option( $model::DB_OPT_API_KEY ) ); ?>"/>
						<p><a href="https://console.developers.google.com"
						      target="_blank">where i can find my API key?</a></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php _e( 'API Status', \justImageOptimizer::TEXTDOMAIN ); ?></th>
					<td class="api_status">
						<?php if ( get_option( $model::DB_OPT_STATUS ) === '1' ) : ?>
							<p>OK</p>
						<?php else: ?>
							<button id="api_connect">Connect</button>
							<p class="notice-error"></p>
						<?php endif; ?>
					</td>
				</tr>
			</table>
		</div>
		<input <?php echo( get_option( $model::DB_OPT_STATUS ) === '1' ? '' : 'disabled' ); ?>
			type="submit" name="submit-connect" id="submit-connect"
			class="button button-primary" value="Save">
	</form>
</div>
