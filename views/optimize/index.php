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
	$link       = $_SERVER['REQUEST_URI'];
	$link_array = explode( '/', $link );
	$attach_id  = base64_decode( end( $link_array ) );
	if ( is_int( $attach_id ) ) {
		$sizes_attachment = wp_get_attachment_metadata( $attach_id );
		foreach ( $sizes_attachment['sizes'] as $key_size => $value_size ) {
			echo '<img src="' . esc_url( wp_get_attachment_image_url( $attach_id, $key_size ) ) . '" />';
		}
		echo '<img src="' . esc_url( wp_get_attachment_image_url( $attach_id, 'full' ) ) . '" />';
	}
	?>
</div>
</body>
</html>