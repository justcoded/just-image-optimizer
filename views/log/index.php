<?php
/* @var $model object */
?>
<div class="wrap">
	<?php include( JUSTIMAGEOPTIMIZER_ROOT . '/views/_tabs.php' ); ?>
	<h2></h2>
	<table class="wp-list-table widefat fixed striped pages">
		<thead>
		<tr>
			<th>Log ID</th>
			<th>Service</th>
			<th>Image Limit</th>
			<th>Size Limit(MB)</th>
			<th>Attachment Found</th>
			<th>Attachment Removed</th>
			<th>Total Count Attachment</th>
			<th>Total Save Size</th>
			<th>Date</th>
		</tr>
		</thead>
		<tbody id="the-list">
		<?php $log_store_data = $model->get_log_store(); ?>
		<?php if ( ! empty( $log_store_data['log_store'] ) ) : ?>
			<?php foreach ( $log_store_data['log_store'] as $log_store ) : ?>
				<tr>
					<td>
						<a href="<?php echo admin_url() ?>upload.php?page=just-img-opt-log&store_id=
						<?php echo $log_store[ $model::COL_STORE_ID ]; ?>">
							<?php echo $log_store[ $model::COL_STORE_ID ]; ?>
						</a>
					</td>
					<td><?php echo $log_store[ $model::COL_SERVICE ]; ?></td>
					<td><?php echo $log_store[ $model::COL_IMAGE_LIMIT ]; ?></td>
					<td><?php echo $log_store[ $model::COL_SIZE_LIMIT ]; ?></td>
					<td><?php echo $model->attach_count_stat( $log_store[ $model::COL_STORE_ID ], 'optimized' )[0]['stat'] +
					               $model->attach_count_stat( $log_store[ $model::COL_STORE_ID ], 'aborted' )[0]['stat']; ?></td>
					<td><?php echo $model->attach_count_stat( $log_store[ $model::COL_STORE_ID ], 'removed' )[0]['stat']; ?></td>
					<td><?php echo( ! empty( $log_store['total_count'] ) ? $log_store['total_count'] : '0' ); ?></td>
					<td><?php echo( ! empty( $log_store['total_save'] ) ? size_format( $log_store['total_save'] ) : '0 B' ); ?></td>
					<td><?php echo $log_store[ $model::COL_TIME ]; ?></td>
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
			<th>Log ID</th>
			<th>Service</th>
			<th>Image Limit</th>
			<th>Size Limit(MB)</th>
			<th>Attachment Found</th>
			<th>Attachment Removed</th>
			<th>Total Count Attachment</th>
			<th>Total Save Size</th>
			<th>Date</th>
		</tr>
		</tfoot>
	</table>
	<div class="tablenav bottom">
		<div class="tablenav-pages">
			<?php echo $log_store_data['pagination']; ?>
		</div>
	</div>
</div>