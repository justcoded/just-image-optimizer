<?php echo $redirect; ?>
<div class="wrap">
	<?php include( JUSTIMAGEOPTIMIZER_ROOT . '/views/_tabs.php' ); ?>
	<form method="post" action="<?php get_permalink(); ?>" enctype="multipart/form-data">
		<table class="form-table">
			<tr>
				<th scope="row"><?php _e( 'Select an Image Optimization Service', \JustImageOptimizer::TEXTDOMAIN ); ?></th>
				<td><label for="service">
						<input
							<?php echo( get_option( $model::DB_OPT_SERVICE ) === 'google_insights' ? 'checked' : '' ); ?>
							type="checkbox"
							id="service"
							name="service"
							value="google_insights">
						<?php _e( 'Google PageSpeed Insights', \JustImageOptimizer::TEXTDOMAIN ); ?></label>
				</td>
			</tr>
		</table>
		<div id="google_insights">
			<h3><?php _e( 'Google PageSpeed Insights Settings', \JustImageOptimizer::TEXTDOMAIN ); ?></h3>
			<table class="form-table">
				<tr>
					<th scope="row"><?php _e( 'API Key', \JustImageOptimizer::TEXTDOMAIN ); ?></th>
					<td>

						<input type="text" name="api_key"
						       id="api_key" class="regular-text"
						       value="<?php echo esc_attr( get_option( $model::DB_OPT_API_KEY ) ); ?>"/>
						<p><a id="find_api" href="#connect">where i can find my API key?</a></p>
						<div>
						<ol id="instructions-api" style="display: none">
							<li>
								Navigate to <a target="_blank" href="https://code.google.com/apis/console" rel="nofollow">
									https://code.google.com/apis/console
								</a>
							</li>
							<li>
								Login with your Google Account (Create a Google account if you do not have one)
							</li>
							<li>
								Click the “Create Project…” button
							</li>
							<li>
								You should now be looking at the “Services” page, if you are not, click “Services” from the menu on the left.
							</li>
							<li>
								Scroll down the Services page until you find “PageSpeed Insights API”. Click the Switch to turn it on. You must agree to Google’s Terms and Conditions to continue.
							</li>
							<li>
								After enabling the API, navigate to the “API Access” page from the left menu. Your API Key can be found under “Simple API Access.” Copy this key to your clipboard.
							</li>
							<li>Paste this API Key</li>
						</ol>
						</div>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php _e( 'API Status', \JustImageOptimizer::TEXTDOMAIN ); ?></th>
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
