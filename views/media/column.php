<?php
if ( $column_name !== 'optimize' ) {
	return;
}
$image_status = get_post_meta( $id, '_just_img_opt_queue', true );
?>
<?php if ( $image_status === '3' ) : ?>
	<p>Optimized</p>
<?php elseif ( $image_status === '1' ) : ?>
	<p>Queued (#<?php echo $id; ?>)</p>
	<a class="optimize-now" href="#<?php echo $id; ?>" data-attach-id="<?php echo $id; ?>">optimize now</a>
<?php else: ?>
	<a class="optimize-now" href="#<?php echo $id; ?>" data-attach-id="<?php echo $id; ?>">optimize now</a>
<?php endif; ?>
