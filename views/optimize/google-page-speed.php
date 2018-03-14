<?php
/**
 * Template vars:
 *
 * @var $service \JustCoded\WP\ImageOptimizer\services\GooglePagespeed
 * @var $media \JustCoded\WP\ImageOptimizer\models\Media
 * @var $settings \JustCoded\WP\ImageOptimizer\models\Settings
 * @var $attach_ids array
 */

?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="profile" href="http://gmpg.org/xfn/11">
	<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>">
</head>
<body <?php body_class(); ?>>
<div id="wrapper">
	<?php
	foreach ( $attach_ids as $attach_id ) {
		if ( $image_sizes = $media->get_queued_image_sizes( $attach_id ) ) {
			foreach ( $image_sizes as $image_size ) {
				if ( $settings->image_sizes_all || in_array( $image_size, $settings->image_sizes, true ) ) {
					if ( $img_src = $service->get_image_proxy_url( $attach_id, $image_size ) ) {
						echo '<img src="' . esc_url( $img_src ) . '" />';
					}
				}
			}
		}
	}
	?>
</div>
</body>
</html>