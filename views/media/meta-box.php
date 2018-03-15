<?php
/**
 * @var Media $model
 * @var array $meta
 */

use JustCoded\WP\ImageOptimizer\models\Media;

?>
<table class="form-table">
	<?php if ( ! empty( $meta['width'] ) ) : ?>
		<tr>
			<td><strong>Full size</strong>
			</td>
			<td><strong><?php echo esc_html( "{$meta['width']} x {$meta['height']}" ); ?> px</strong>
				<?php if ( ! empty( $meta['gcd'] ) ) : ?>
					(
					<?php
					if ( ! empty( $meta['avr_ratio'] ) ) :
						echo '~';
					endif;
					?>
					<?php echo esc_html( "{$meta['x_ratio']}:{$meta['y_ratio']}" ); ?> )
				<?php endif; ?>
			</td>
		</tr>
	<?php endif; ?>
	<tr class="optimize-stats">
		<td><strong>Image Optimization</strong>
			<?php if ( in_array( get_post_mime_type( $id ), $allowed_images, true ) ) :
				$image_status = (int) get_post_meta( $id, '_just_img_opt_status', true );
				?>
				<?php if ( Media::STATUS_IN_PROCESS === $image_status ) : ?>
					<br>Optimizing is in progress...
				<?php elseif( Media::STATUS_PROCESSED !== $image_status ) :
					$button_text = ( Media::STATUS_PARTIALY_PROCESSED === $image_status ) ? 'try again' : 'optimize now';
					?>
					<br><a class="optimize-now-meta" href="#<?php echo esc_attr( $id ); ?>" data-attach-id="<?php echo esc_attr( $id ); ?>">
					<?php echo esc_html( $button_text ); ?></a>
				<?php endif; ?>
			<?php endif; ?>
		</td>
		<?php $total_stats = $model->get_total_attachment_stats( $id ); ?>
		<?php if ( ! empty( $total_stats[0]->percent ) ) : ?>
			<td>
				<p><?php echo esc_html( $total_stats[0]->percent ); ?>% saved
					(<?php echo esc_html( ! empty( $total_stats[0]->saving_size ) ? jio_size_format( $total_stats[0]->saving_size ) : 0 ); ?>)</p>
				<p>disk usage: <?php echo jio_size_format( $total_stats[0]->disk_usage ); ?>
					(<?php echo esc_html( $model->get_count_images( $id ) ); ?> images) </p>
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
					<td><strong><?php echo jio_size_format( $stats[0]->saving_size ); ?>
							, <?php echo esc_html( $stats[0]->percent ); ?>% saved</strong></td>
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
