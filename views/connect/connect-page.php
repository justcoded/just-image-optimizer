<?php
use justimageoptimizer\services\GooglePagespeed;

$test = new GooglePagespeed();
$api = $test->check_api_key('AIzaSyCuRS1tFKnRRNQbjt6gmk8ch9Ccmw_BLNk');
?>
<div class="wrap">
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
				<td>

					<button>Connect</button>
				</td>
			</tr>
		</table>
		</div>
		<?php submit_button(); ?>
	</form>
</div>
