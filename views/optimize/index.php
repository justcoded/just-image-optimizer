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
	$bulk_size  = 0;
	$bulk_array = array();
	if ( is_array( $sizes_attachment ) ) {
		foreach ( $attach_ids as $attach_id ) {
			$bulk_array[ $attach_id ] = $media->get_total_filesizes( $attach_id, true );
		}
		foreach ( $sizes_attachment as $value_size ) {
			foreach ( $attach_ids as $attach_id ) {
				if ( get_option( $settings::DB_OPT_SIZE_LIMIT ) === '0' ) {
					echo '<img src="' . esc_url( wp_get_attachment_image_url( $attach_id, $value_size ) ) . '" />';
				} else {
					if ( ! empty( $bulk_array[ $attach_id ][ $value_size ] ) ) {
						$bulk_size = $bulk_size + $bulk_array[ $attach_id ][ $value_size ];
					}
					if ( number_format_i18n( $bulk_size / 1048576 ) >= get_option( $settings::DB_OPT_SIZE_LIMIT ) ) {
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