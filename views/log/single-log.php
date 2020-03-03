<?php
/**
 * Variables
 *
 * @var $model Log
 * @var $log object
 * @var $request_id int
 */

use JustCoded\WP\ImageOptimizer\models\Log;
use JustCoded\WP\ImageOptimizer\models\DetailedLogTable;

$log_table = new DetailedLogTable();
$log_table->setup( $model, true, $request_id );
?>
<div class="wrap">
	<?php include( JUSTIMAGEOPTIMIZER_ROOT . '/views/_tabs.php' ); ?>
	<h2><small><a href="<?php echo admin_url() ?>upload.php?page=just-img-opt-log">All Requests</a> &raquo;</small>
		Request #<?php echo esc_html( $request_id ); ?> details</h2>

	<h3>Details</h3>
	<p>
		<?php echo nl2br( esc_html( $log->info ) ); ?>
	</p>

	<h3>Attachments</h3>
	<?php $log_table->display(); ?>
</div>

<style type="text/css">
	.media_page_just-img-opt-log th,
	#the-list td {
		text-align: center;
	}

	.text-danger {
		color: #a00;
	}
</style>
