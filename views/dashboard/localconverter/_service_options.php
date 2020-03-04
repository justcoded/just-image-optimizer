<?php

use JustCoded\WP\ImageOptimizer\services\LocalConverter;

$service_default_options = LocalConverter::$options;

$webp_quality = isset( $model->service_options['webp_image_quality'] ) ?
	$model->service_options['webp_image_quality'] : $service_default_options['webp_image_quality'];
$jp2_quality  = isset( $model->service_options['jp2_image_quality'] ) ?
	$model->service_options['jp2_image_quality'] : $service_default_options['jp2_image_quality'];
$lossless     = isset( $model->service_options['lossless'] ) ?
	$model->service_options['lossless'] : $service_default_options['lossless'];
?>
<tr>
	<th scope="row"><?php esc_html_e( 'Webp image conversion quality', \JustImageOptimizer::TEXTDOMAIN ); ?></th>
	<td class="inline-content">
		<input id="webp-quality-range"
				type="number"
				min="50"
				max="100"
				name="service_options[webp_image_quality]"
				value="<?php esc_attr_e( $webp_quality ); ?>">
	</td>
</tr>
<tr>
	<th scope="row"><?php esc_html_e( 'Jp2 image conversion quality', \JustImageOptimizer::TEXTDOMAIN ); ?></th>
	<td class="inline-content">
		<input id="jp2-quality-range"
				type="number"
				min="50"
				max="100"
				name="service_options[jp2_image_quality]"
				value="<?php esc_attr_e( $jp2_quality ); ?>">
	</td>
</tr>
<tr>
	<th scope="row"><?php esc_html_e( 'Lossless', JustImageOptimizer::TEXTDOMAIN ); ?></th>
	<td>
		<input type="hidden" name="service_options[lossless]" value="0">
		<input <?php checked( $lossless ); ?>
				type="checkbox"
				name="service_options[lossless]"
				value="1" >
	</td>
</tr>

<style>
	.inline-content {
		display: flex;
		flex-direction: row;
		align-content: center;
	}

	.inline-content .range-label {
		align-self: center;
		font-weight: bold;
		margin: 0 10px;
	}
</style>
