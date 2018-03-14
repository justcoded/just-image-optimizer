<?php
/* @var $model Log */

use JustCoded\WP\ImageOptimizer\models\Log;

// TODO: (after launch) replace this table with custom class, extended from WP_List_Table (it can handle nice admin UI with search, pagination controls etc.)

?>
<div class="wrap">
	<?php include( JUSTIMAGEOPTIMIZER_ROOT . '/views/_tabs.php' ); ?>
	<p>List of optimization request to the 3rd-party services.</p>
	<table class="wp-list-table widefat fixed striped pages">
		<thead>
		<tr>
			<th class="num">Log ID</th>
			<th>Date</th>
			<th>Service</th>
			<th class="num">Image/Size Limits</th>
			<th class="num">Attachments #</th>
			<th class="num">Img Sizes #</th>
			<th class="num">Optimized / Failed</th>
			<th class="num">Saved Size</th>
		</tr>
		</thead>
		<tbody id="the-list">
		<?php $log_store_data = $model->get_requests(); ?>
		<?php if ( ! empty( $log_store_data['rows'] ) ) : ?>
			<?php foreach ( $log_store_data['rows'] as $row ) :
				$request_id = $row[ Log::COL_REQUEST_ID ];
				?>
				<tr>
					<td class="num">
						<a href="<?php echo admin_url( 'upload.php?page=just-img-opt-log&request_id=' . $request_id ); ?>">
							<?php echo esc_html( $request_id ); ?>
						</a>
					</td>
					<td><?php echo esc_html( $row[ Log::COL_TIME ] ); ?></td>
					<td><?php echo esc_html( $row[ Log::COL_SERVICE ] ); ?></td>
					<td class="num"><?php echo esc_html( $row[ Log::COL_IMAGE_LIMIT ] ); ?> attachm. / <?php echo esc_html( $row[ Log::COL_SIZE_LIMIT ] ); ?>MB</td>
					<td class="num"><?php echo esc_html( $model->attach_count( $request_id ) ); ?></td>
					<td class="num"><?php echo esc_html( $total_files = ! empty( $row['total_count'] ) ? $row['total_count'] : '0' ); ?></td>
					<td class="num">
						<b><?php echo esc_html( $optimized = $model->files_count_stat( $request_id, Log::STATUS_OPTIMIZED ) ); ?></b>
						/ <span class="text-danger"><?php echo esc_html( max( $total_files - $optimized, 0 ) ); ?></span>
					</td>
					<td class="num"><strong><?php echo esc_html( ! empty( $row['total_save'] ) ? jio_size_format( $row['total_save'] ) : '0 B' ); ?></strong></td>
				</tr>
			<?php endforeach; ?>
		<?php else : ?>
			<tr class="no-items">
				<td class="colspanchange" colspan="8">Log is empty.</td>
			</tr>
		<?php endif; ?>
		</tbody>
		<tfoot>
		<tr>
			<th class="num">Log ID</th>
			<th>Date</th>
			<th>Service</th>
			<th class="num">Image/Size Limits</th>
			<th class="num">Attachments #</th>
			<th class="num">Img Sizes #</th>
			<th class="num">Optimized / Failed</th>
			<th class="num">Saved Size</th>
		</tr>
		</tfoot>
	</table>
	<div class="tablenav bottom">
		<div class="tablenav-pages">
			<?php echo paginate_links( $log_store_data['pagination'] ); ?>
		</div>
	</div>
</div>

<style type="text/css">
	.text-danger { color:#a00; }
</style>