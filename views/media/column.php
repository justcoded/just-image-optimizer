<?php

use JustCoded\WP\ImageOptimizer\models\Media;

if ( 'optimize' !== $column_name ) {
	return;
}
if ( ! in_array( get_post_mime_type( $id ), $allowed_images, true ) ) {
	return;
}
$image_status = (int) get_post_meta( $id, '_just_img_opt_status', true );
$total_stats  = $model->get_total_attachment_stats( $id );
?>
<?php if ( Media::STATUS_PROCESSED === $image_status || Media::STATUS_PARTIALY_PROCESSED === $image_status ) : ?>
	<?php if ( ! empty( $total_stats[0]->percent ) ) : ?>
		<p><?php echo esc_html( $total_stats[0]->percent ); ?>% saved
			(<?php echo esc_html( ! empty( $total_stats[0]->saving_size ) ? jio_size_format( $total_stats[0]->saving_size ) : 0 ); ?>)
		</p>
		<p>disk usage: <?php echo jio_size_format( $total_stats[0]->disk_usage ); ?>
			(<?php echo esc_html( $model->get_count_images( $id ) ); ?> images) </p>
		<?php if ( Media::STATUS_PARTIALY_PROCESSED === $image_status ) : ?>
			<em>&nbsp; * can be better,
			<a class="optimize-now" href="#<?php echo esc_attr( $id ); ?>" data-attach-id="<?php echo esc_attr( $id ); ?>">
				try again
			</a>
			</em>
		<?php endif; ?>
	<?php endif; ?>
<?php elseif ( Media::STATUS_IN_QUEUE === $image_status ) : ?>
	<p>Queued (#<?php echo esc_html( $id ); ?>)</p>
	<a class="optimize-now" href="#<?php echo esc_attr( $id ); ?>" data-attach-id="<?php echo esc_attr( $id ); ?>">optimize
		now</a>
<?php else: ?>
	<a class="optimize-now" href="#<?php echo esc_attr( $id ); ?>" data-attach-id="<?php echo esc_attr( $id ); ?>">optimize
		now</a>
<?php endif; ?>