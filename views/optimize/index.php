<?php
use justimageoptimizer\models\Settings;
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
	if ( is_array( $sizes_attachment ) ) {
		foreach ( $sizes_attachment as $value_size ) {
			foreach ( $attach_ids as $attach_id ) {
				echo '<img src="' . esc_url( wp_get_attachment_image_url( $attach_id, $value_size ) ) . '" />';
			}
		}
	}
	?>
</div>
</body>
</html>