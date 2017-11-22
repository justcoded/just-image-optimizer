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
	$limit_size = 0;
	$size_array = array();
	if ( is_array( $settings->image_sizes ) ) {
		foreach ( $attach_ids as $attach_id ) {
			$size_array[ $attach_id ] = $media->get_total_filesizes( $attach_id, true );
		}
		foreach ( $settings->image_sizes as $value_size ) {
			foreach ( $attach_ids as $attach_id ) {
				if ( $settings->size_limit === '0' ) {
					echo '<img src="' . esc_url( wp_get_attachment_image_url( $attach_id, $value_size ) ) . '" />';
				} else {
					if ( ! empty( $size_array[ $attach_id ][ $value_size ] ) ) {
						$limit_size = $limit_size + $size_array[ $attach_id ][ $value_size ];
					}
					if ( number_format_i18n( $limit_size / 1048576 ) >= $settings->size_limit ) {
						break;
					}
					echo '<img src="' . esc_url( wp_get_attachment_image_url( $attach_id, $value_size ) ) . '" />';
				}
			}
		}
	}
	?>
</div>
</body>
</html>