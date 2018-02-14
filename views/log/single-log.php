<?php
/* @var $model object */
?>
<div class="wrap">
	<?php include( JUSTIMAGEOPTIMIZER_ROOT . '/views/_tabs.php' ); ?>
	<h2></h2>
	<a href="<?php echo admin_url() ?>upload.php?page=just-img-opt-log"><<< Back</a>
	<table class="wp-list-table widefat fixed striped pages">
		<thead>
		<tr>
			<th>Attach ID</th>
			<th>File Name</th>
			<th>Size</th>
			<th>Size Before</th>
			<th>Size After</th>
			<th>Status</th>
		</tr>
		</thead>
		<tbody id="the-list">
		<?php $log_data = $model->get_log( $store_id ); ?>
		<?php if ( ! empty( $log_data['log'] ) ) : ?>
			<?php foreach ( $log_data['log'] as $log ) : ?>
				<tr>
					<td>
						<a href="<?php echo get_edit_post_link( $log[ $model::COL_ATTACH_ID ] ); ?>">
							<?php echo $log[ $model::COL_ATTACH_ID ]; ?>
						</a>
					</td>
					<td><?php echo $log[ $model::COL_IMAGE_SIZE ]; ?></td>
					<td><?php echo $log[ $model::COL_ATTACH_NAME ]; ?></td>
					<td><?php echo size_format( $log[ $model::COL_BYTES_BEFORE ] ); ?></td>
					<td><?php echo size_format( $log[ $model::COL_BYTES_AFTER ] ); ?></td>
					<td><?php echo $log[ $model::COL_STATUS ]; ?></td>
				</tr>
			<?php endforeach; ?>
		<?php else : ?>
			<tr class="no-items">
				<td class="colspanchange" colspan="6">Log is empty.</td>
			</tr>
		<?php endif; ?>
		</tbody>
		<tfoot>
		<tr>
			<th>Attach ID</th>
			<th>File Name</th>
			<th>Size</th>
			<th>Size Before</th>
			<th>Size After</th>
			<th>Status</th>
		</tr>
		</tfoot>
	</table>
	<div class="tablenav bottom">
		<div class="tablenav-pages">
			<?php echo $log_data['pagination']; ?>
		</div>
	</div>
</div>