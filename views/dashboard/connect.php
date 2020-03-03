<?php
/**
 * Variables
 *
 * @var bool $saved .
 */

$scope = \JustCoded\WP\ImageOptimizer\services\ImageOptimizerFactory::$scope;
?>
<div class="wrap jio-admin-page">
	<?php include( JUSTIMAGEOPTIMIZER_ROOT . '/views/_tabs.php' ); ?>
	<?php if ( $saved ) : ?>
		<div class="update-nag success-nag">
			<strong>Connection options updated!</strong>
		</div>
	<?php endif; ?>
	<?php if ( false === $saved ) : ?>
		<div class="update-nag error-nag">
			<strong>API key is invalid!</strong>
		</div>
	<?php endif; ?>
	<form method="post" action="<?php get_permalink(); ?>" enctype="multipart/form-data">
		<table class="form-table">
			<?php
			if ( $scope ) :
				foreach ( $scope as $service ) :
					?>
					<tr>
						<th scope="row"><?php esc_html_e( 'Select an Image Optimization Service', \JustImageOptimizer::TEXTDOMAIN ); ?></th>
						<td><label for="<?php esc_attr_e( $service->service_id() ); ?>">
								<input
									<?php checked( $model->service, $service->service_id() ); ?>
										type="radio"
										id="<?php esc_attr_e( $service->service_id() ); ?>"
										name="service"
										value="<?php esc_attr_e( $service->service_id() ); ?>">
								<?php esc_html_e( $service->name(), \JustImageOptimizer::TEXTDOMAIN ); ?></label>
						</td>
					</tr>
					<?php
				endforeach;
			endif;
			?>
		</table>
		<div id="google_insights-info" class="frames">
			<h3><?php esc_html_e( 'Google PageSpeed Insights Settings', \JustImageOptimizer::TEXTDOMAIN ); ?></h3>
			<table class="form-table">
				<tr>
					<th scope="row"><?php esc_html_e( 'API Key', \JustImageOptimizer::TEXTDOMAIN ); ?></th>
					<td>

						<input type="text" name="api_key"
								id="api_key" class="regular-text"
								value="<?php echo esc_attr( $model->api_key ); ?>"/>
						<p><a id="find_api" href="#connect">where i can find my API key?</a></p>
						<div>
							<ol id="instructions-api" style="display: none">
								<li>
									Navigate to <a target="_blank" href="https://console.cloud.google.com/apis/dashboard"
											rel="nofollow">
										Google Cloud Platform Console</a>.
								</li>
								<li>
									<strong>Login</strong> with your Google Account (Create a Google account if you do not have one).
								</li>
								<li>
									From the projects list ( is on top left ), <strong>select a project</strong> or create a new one.
									<ul>
										<li>
											- To create a project set “Project Name” and click “Create”. <br>
											- Wait until the creation is complete and select newly created project.
										</li>
									</ul>
								</li>
								<li>
									After selecting a project. Click on
									<a href="https://console.developers.google.com/apis/credentials">Dashboard page</a>.
								</li>
								<li>Click on <strong>"Enable APIs and Services"</strong> button.</li>
								<li>Find and enable <strong>PageSpeed Insights</strong> API.</li>
								<li>
									After enabling PageSpeed Insights API we need to <strong>create an API key</strong>. Click on
									<a href="https://console.developers.google.com/apis/credentials">Credentials page</a>.
								</li>
								<li>
									Click "Create credentials" and then select "API key".
								</li>
								<li>Copy/Paste this API Key in the input field below.</li>
							</ol>
						</div>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'API Status', \JustImageOptimizer::TEXTDOMAIN ); ?></th>
					<td class="api_status">
						<input type="hidden" name="status" value="<?php esc_attr( $model->status ); ?>">
						<?php if ( '1' === $model->status ) : ?>
							<button id="api_connect" class="hidden">Connect</button>
							<p class="notice-error">Connected</p>
						<?php else : ?>
							<button id="api_connect">Connect</button>
							<p class="notice-error"></p>
						<?php endif; ?>
					</td>
				</tr>
			</table>
		</div>
		<div id="localconverter-info" class="frames">
			<h3><?php esc_html_e( 'Local Image Converter Settings', JustImageOptimizer::TEXTDOMAIN ); ?></h3>
			<div class="form-table">
				<div class="service-information">
					<strong>Requirements:</strong>
					<ol>
						<li>ImageMagick, Webp and Openjpeg libraries should be installed.</li>
						<li>ImageMagick should has 'webp' and 'jp2' delegates enabled.</li>
						<li>Exec() function must be allowed on the server.</li>
						<li>Uploads directory must be writable.</li>
					</ol>
				</div>
				<input type="button" id="check_converters" value="Check">
				<div id="connect-status"><ul class="notice-error"></ul></div>
			</div>
		</div>
		<input <?php esc_attr_e( '1' === $model->status ? '' : 'disabled' ); ?>
				type="submit" name="submit-connect" id="submit-connect"
				class="button button-primary" value="Save">
	</form>
</div>

<style>
	.notice-success {
		color: green;
	}
</style>
