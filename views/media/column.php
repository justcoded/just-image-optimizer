<?php
if ( $column_name !== 'optimize' ) {
	return;
}
if ( ! in_array( get_post_mime_type( $id ), $allowed_images ) ) {
	return;
}
$image_status = get_post_meta( $id, '_just_img_opt_queue', true );
?>
<?php if ( $image_status === '3' ) : ?>
	<p><?php echo get_post_meta( $id, $model::DB_OPT_IMAGE_SAVING_PERCENT, true ); ?> saved
		(<?php echo get_post_meta( $id, $model::DB_OPT_IMAGE_SAVING, true ); ?>)</p>
	<p>disk usage: <?php echo get_post_meta( $id, $model::DB_OPT_IMAGE_DU, true ); ?>
		(<?php echo $model->get_count_images( $id ) ?> images) </p>
<?php elseif ( $image_status === '1' ) : ?>
	<p>Queued (#<?php echo $id; ?>)</p>
	<a class="optimize-now" href="#<?php echo $id; ?>" data-attach-id="<?php echo $id; ?>">optimize now</a>
<?php else: ?>
	<a class="optimize-now" href="#<?php echo $id; ?>" data-attach-id="<?php echo $id; ?>">optimize now</a>
<?php endif; ?>
