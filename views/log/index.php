<?php
/* @var $model object */
?>
<div class="wrap">
	<table class="wp-list-table widefat fixed striped pages">
		<thead>
		<tr>
			<th>Attach ID</th>
			<th>File Name</th>
			<th>Size</th>
			<th>Size Before</th>
			<th>Size After</th>
			<th>Time</th>
			<th>Fail</th>
		</tr>
		</thead>
		<tbody id="the-list">
		<?php $log_data = $model->get_log(); ?>
		<?php if ( ! empty( $log_data['log'] ) ) : ?>
			<?php foreach ( $log_data['log'] as $log ) : ?>
				<tr>
					<td>
						<a href="<?php echo get_edit_post_link( $log[ $model::DB_ATTACH_ID ] ); ?>">
							<?php echo $log[ $model::DB_ATTACH_ID ]; ?>
						</a>
					</td>
					<td><?php echo $log[ $model::DB_SIZE ]; ?></td>
					<td><?php echo $log[ $model::DB_ATTACH_NAME ]; ?></td>
					<td><?php echo size_format( $log[ $model::DB_B_FILE_SIZE ] ); ?></td>
					<td><?php echo size_format( $log[ $model::DB_A_FILE_SIZE ] ); ?></td>
					<td><?php echo $log[ $model::DB_TIME ]; ?></td>
					<td><?php echo $log[ $model::DB_FAIL ]; ?></td>
				</tr>
			<?php endforeach; ?>
		<?php else : ?>
			<tr class="no-items">
				<td class="colspanchange" colspan="7">Log is empty.</td>
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
			<th>Time</th>
			<th>Fail</th>
		</tr>
		</tfoot>
	</table>
	<div class="tablenav bottom">
		<div class="tablenav-pages">
			<?php echo $log_data['pagination']; ?>
		</div>
	</div>
</div>