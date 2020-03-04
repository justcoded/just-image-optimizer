<?php
/**
 * Variables
 *
 * @var $model JustCoded\WP\ImageOptimizer\models\Debug
 */

use JustCoded\WP\ImageOptimizer\models\Log;
use JustCoded\WP\ImageOptimizer\models\Media;
use JustCoded\WP\ImageOptimizer\services\LocalConverter;
use JustCoded\WP\ImageOptimizer\components\Filesystem;

$attach_ids = [ 5, 9, 17, 54, 76, 89, 90 ];

$media     = new Media();
$log       = new Log();
$converter = new LocalConverter();
$fs        = Filesystem::instance();

global $wpdb, $is_safari, $is_chrome, $cache_path;
$table_stat    = $wpdb->prefix . Media::TABLE_IMAGE_STATS;
$table_conv    = $wpdb->prefix . Log::TABLE_IMAGE_CONVERSION;
$table_log     = $wpdb->prefix . Log::TABLE_IMAGE_LOG;
$table_details = $wpdb->prefix . Log::TABLE_IMAGE_LOG_DETAILS;

?>

<div class="wrap">
	<?php include( JUSTIMAGEOPTIMIZER_ROOT . '/views/_tabs.php' ); ?>
	<h1>Debug Information</h1>
	<hr>
	<div id="debug">
		<?php
		foreach ( $attach_ids as $attach_id ) {
			$meta = wp_get_attachment_metadata( $attach_id );

			var_dump( $converter->attachment_conversion_path( $meta['file'], 'webp' ) );
		}
		?>
	</div>
</div>
