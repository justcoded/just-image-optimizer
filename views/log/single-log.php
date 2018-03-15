<?php
/* @var $model Log */
/* @var $log object */
/* @var $request_id int */

use JustCoded\WP\ImageOptimizer\models\Log;
?>
<div class="wrap">
	<?php include( JUSTIMAGEOPTIMIZER_ROOT . '/views/_tabs.php' ); ?>
	<h2><small><a href="<?php echo admin_url() ?>upload.php?page=just-img-opt-log">All Requests</a> &raquo;</small>
		Request #<?php echo esc_html( $request_id ); ?> details</h2>

	<h3>Details</h3>
	<p>
		<?php echo nl2br( esc_html($log->info) ); ?>
	</p>

	<h3>Attachments</h3>
	<table class="wp-list-table widefat fixed striped pages">
		<thead>
		<tr>
			<th class="num">Attachment ID</th>
			<th>Size</th>
			<th>File Name</th>
			<th class="num">Size Before</th>
			<th class="num">Size After</th>
			<th>Status</th>
		</tr>
		</thead>
		<tbody id="the-list">
		<?php if ( $log_data = $model->get_request_details( $request_id ) ) : ?>
			<?php foreach ( $log_data as $row ) : ?>
				<tr>
					<td class="num">
						<a href="<?php echo get_edit_post_link( $row[ Log::COL_ATTACH_ID ] ); ?>">
							<?php echo esc_html( $row[ Log::COL_ATTACH_ID ] ); ?>
						</a>
					</td>
					<td><?php echo esc_html( $row[ Log::COL_IMAGE_SIZE ] ); ?></td>
					<td><?php echo esc_html( $row[ Log::COL_ATTACH_NAME ] ); ?></td>
					<td class="num"><?php echo jio_size_format( $row[ Log::COL_BYTES_BEFORE ] ); ?></td>
					<td class="num"><?php echo jio_size_format( $row[ Log::COL_BYTES_AFTER ] ); ?></td>
					<td><?php echo esc_html( $model->get_status_message( $row[ Log::COL_STATUS ] ) ); ?></td>
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
			<th class="num">Attachment ID</th>
			<th>Size</th>
			<th>File Name</th>
			<th class="num">Size Before</th>
			<th class="num">Size After</th>
			<th>Status</th>
		</tr>
		</tfoot>
	</table>
</div>