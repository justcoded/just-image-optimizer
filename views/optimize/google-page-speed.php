<?php
/**
 * Template vars:
 *
 * @var $service \JustCoded\WP\ImageOptimizer\services\GooglePagespeed
 * @var $media \JustCoded\WP\ImageOptimizer\models\Media
 * @var $settings Settings
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
	$a_ids = $media->size_limit( $attach_ids );
	if ( is_array( $settings->image_sizes ) ) {
		foreach ( $settings->image_sizes as $image_size ) {
			foreach ( $a_ids as $attach_id ) {
				if ( $img_src = $service->get_image_proxy_url( $attach_id, $image_size ) ) {
					echo '<img src="' . esc_url( $img_src ) . '" />';
				}
			}
		}
	}
	?>
</div>
</body>
</html>