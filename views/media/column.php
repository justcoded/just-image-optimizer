<?php
if ( $column_name !== 'optimize' ) {
	return;
}
if ( ! in_array( get_post_mime_type( $id ), $allowed_images ) ) {
	return;
}
$image_status = get_post_meta( $id, '_just_img_opt_status', true );
$total_stats = $model->get_total_attachment_stats( $id );
?>
<?php if ( $image_status === '3' ) : ?>
	<?php if ( ! empty( $total_stats[0]->percent ) ) : ?>
	<p><?php echo $total_stats[0]->percent; ?>% saved
		(<?php echo ( ! empty( $total_stats[0]->saving_size ) ? size_format( $total_stats[0]->saving_size ) : 0 ); ?>)</p>
	<p>disk usage: <?php echo size_format( $total_stats[0]->disk_usage ); ?>
		(<?php echo $model->get_count_images( $id ); ?> images) </p>
	<?php endif; ?>
<?php elseif ( $image_status === '1' ) : ?>
	<p>Queued (#<?php echo $id; ?>)</p>
	<a class="optimize-now" href="#<?php echo $id; ?>" data-attach-id="<?php echo $id; ?>">optimize now</a>
<?php else: ?>
	<a class="optimize-now" href="#<?php echo $id; ?>" data-attach-id="<?php echo $id; ?>">optimize now</a>
<?php endif; ?>