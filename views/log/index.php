<?php
/**
 * Variables
 *
 * @var $model Log
 */

use JustCoded\WP\ImageOptimizer\models\Log;
use JustCoded\WP\ImageOptimizer\models\CommonLogTable;

// TODO: (after launch) replace this table with custom class, extended from WP_List_Table (it can handle nice admin UI with search, pagination controls etc.)
$log_table = new CommonLogTable();
$log_table->setup( $model );
?>
<div class="wrap">
	<?php include( JUSTIMAGEOPTIMIZER_ROOT . '/views/_tabs.php' ); ?>
	<p>List of optimization request to the 3rd-party services.</p>
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
