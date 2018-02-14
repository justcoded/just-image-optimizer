<?php
/**
 * @var \JustCoded\WP\ImageOptimizer\models\Media $model
 * @var array $meta
 */
?>
<table class="form-table">
	<?php if ( ! empty( $meta['width'] ) ) : ?>
		<tr>
			<td><strong>Full size</strong>
				<?php if ( in_array( get_post_mime_type( $id ), $allowed_images ) ) :
					$image_status = get_post_meta( $id, '_just_img_opt_status', true );
					?>
					<?php if ( $image_status === '1' ) : ?>
					<br><a class="optimize-now-meta" href="#<?php echo $id; ?>" data-attach-id="<?php echo $id; ?>">optimize
						now</a>
				<?php elseif ( empty( $image_status ) ) : ?>
					<br><a class="optimize-now-meta" href="#<?php echo $id; ?>" data-attach-id="<?php echo $id; ?>">optimize
						now</a>
				<?php endif; ?>
				<?php endif; ?>
			</td>
			<td><strong><?php echo esc_html( "{$meta['width']} x {$meta['height']}" ); ?> px</strong>
				<?php if ( ! empty( $meta['gcd'] ) ) : ?>
					( <?php if ( ! empty( $meta['avr_ratio'] ) ) {
						echo '~';
					} ?>
					<?php echo esc_html( "{$meta['x_ratio']}:{$meta['y_ratio']}" ); ?> )
				<?php endif; ?>
			</td>
			<td>
				<?php $stats = $model->get_attachment_stats( $id, 'full' ); ?>
				<?php if ( ! empty( $stats[0]->saving_size ) ) : ?>
					<strong><?php echo size_format( $stats[0]->saving_size ); ?>
						, <?php echo $stats[0]->percent; ?>% saved</strong>
				<?php else: ?>
					<strong>0% saved</strong>
				<?php endif; ?>

			</td>
		</tr>
	<?php endif; ?>
	<tr class="optimize-stats">
		<td><strong>Image Optimization</strong></td>
		<?php $total_stats = $model->get_total_attachment_stats( $id ); ?>
		<?php if ( ! empty( $total_stats[0]->percent ) ) : ?>
			<td>
				<p><?php echo $total_stats[0]->percent; ?>% saved
					(<?php echo ( ! empty( $total_stats[0]->saving_size ) ? size_format( $total_stats[0]->saving_size ) : 0 ); ?>)</p>
				<p>disk usage: <?php echo size_format( $total_stats[0]->disk_usage ); ?>
					(<?php echo $model->get_count_images( $id ); ?> images) </p>
			</td>
		<?php else : ?>
			<td><strong>0% saved</strong></td>
		<?php endif; ?>
	</tr>
	<?php if ( ! empty( $meta['sizes'] ) ) : ?>
		<tr>
			<td colspan="2"><em>Additional sizes</em></td>
		</tr>
		<?php foreach ( $meta['sizes'] as $key => $params ) : ?>
			<tr style="border-top: 1px solid #eee;">
				<td><?php echo esc_html( $key ); ?></td>
				<td><?php echo esc_html( "{$params['width']} x {$params['height']}" ); ?> px</td>
				<?php $stats = $model->get_attachment_stats( $id, $key ); ?>
				<?php if ( ! empty( $stats[0]->saving_size ) ) : ?>
					<td><strong><?php echo size_format( $stats[0]->saving_size ); ?>
							, <?php echo $stats[0]->percent; ?>% saved</strong></td>
				<?php else: ?>
					<td><strong>0% saved</strong></td>
				<?php endif; ?>
			</tr>
		<?php endforeach; ?>
	<?php else : ?>
		<tr>
			<td colspan="2"><em>No additional sizes.</em><br>
				<small>It seems you didn't configure your image sizes correctly.
					You should configure responsive image sizes and regenerate thumnbails after that.
				</small>
			</td>
		</tr>
	<?php endif; ?>
</table>
