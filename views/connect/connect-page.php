<?php
use JustCoded\WP\ImageOptimizer\models\Media;

$test = new Media();

var_dump($test->get_dashboard_attachment_stats());
?>
<div class="wrap">
	<?php include( JUSTIMAGEOPTIMIZER_ROOT . '/views/_tabs.php' ); ?>
	<?php if ( $saved ) : ?>
		<div class="update-nag" style="border-left-color: green !important">
			<strong>Connection options updated!</strong>
		</div>
	<?php endif; ?>
	<?php if ( $saved === false ) : ?>
		<div class="update-nag" style="border-left-color: red !important">
			<strong>API key is invalid!</strong>
		</div>
	<?php endif; ?>
	<form method="post" action="<?php get_permalink(); ?>" enctype="multipart/form-data">
		<table class="form-table">
			<tr>
				<th scope="row"><?php _e( 'Select an Image Optimization Service', \JustImageOptimizer::TEXTDOMAIN ); ?></th>
				<td><label for="service">
						<input type="hidden" name="service" value="0">
						<input
							<?php echo( $model->service === 'google_insights' ? 'checked' : '' ); ?>
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
						       value="<?php echo esc_attr( $model->api_key ); ?>"/>
						<p><a id="find_api" href="#connect">where i can find my API key?</a></p>
						<div>
							<ol id="instructions-api" style="display: none">
								<li>
									Navigate to <a target="_blank" href="https://console.cloud.google.com/"
									               rel="nofollow">
										Cloud Platform Console</a>
								</li>
								<li>
									Login with your Google Account (Create a Google account if you do not have one)
								</li>
								<li>
									From the projects list ( is on top left ), select a project or create a new one.
								</li>
								<li>
									For creation project set “Project Name” and click “Create”. Waiting for creation
									project and select him.
								</li>
								<li>
									After creation project. Click on <a
										href="https://console.developers.google.com/apis/credentials">Credentials
										page</a>
								</li>
								<li>
									On Credentials page the left, choose "Credentials".
								</li>
								<li>
									Click "Create credentials" and then select API key.
								</li>
								<li>Paste this API Key</li>
							</ol>
						</div>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php _e( 'API Status', \JustImageOptimizer::TEXTDOMAIN ); ?></th>
					<td class="api_status">
						<input type="hidden" name="status" value="<?php $model->status; ?>">
						<?php if ( $model->status === '1' ) : ?>
							<p>OK</p>
						<?php else: ?>
							<button id="api_connect">Connect</button>
							<p class="notice-error"></p>
						<?php endif; ?>
					</td>
				</tr>
			</table>
		</div>
		<input <?php echo( $model->status === '1' ? '' : 'disabled' ); ?>
			type="submit" name="submit-connect" id="submit-connect"
			class="button button-primary" value="Save">
	</form>
</div>
