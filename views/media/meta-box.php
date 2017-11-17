<table class="form-table">
	<?php if ( ! empty( $meta['width'] ) ) : ?>
		<tr>
			<td><strong>Full size</strong>
				<?php if ( in_array( get_post_mime_type( $id ), $allowed_images ) ) :
					$image_status = get_post_meta( $id, '_just_img_opt_queue', true );
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
		</tr>
	<?php endif; ?>
	<tr class="optimize-stats">
		<?php if ( get_post_meta( $id, $model::DB_META_IMAGE_SAVING, true ) ) : ?>
			<td><strong>Image Optimization</strong></td>
			<td>
				<p><?php echo get_post_meta( $id, $model::DB_META_IMAGE_SAVING_PERCENT, true ); ?> saved
					(<?php echo get_post_meta( $id, $model::DB_META_IMAGE_SAVING, true ); ?>)</p>
				<p>disk usage: <?php echo get_post_meta( $id, $model::DB_META_IMAGE_DU, true ); ?>
					(<?php echo $model->get_count_images( $id ) ?> images) </p>
			</td>
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
				<?php $stats = $model->get_stats( $id, $key ); ?>
				<?php if ( isset( $stats[ $key ] ) ) : ?>
					<?php if ( $stats[ $key ]['percent_stats'] && $stats[ $key ]['size_stats'] ) : ?>
						<td><strong><?php echo size_format( $stats[ $key ]['percent_stats'] ); ?>
								, <?php echo $stats[ $key ]['size_stats']; ?>% saved</strong></td>
					<?php else : ?>
						<td><strong>0% saved</strong></td>
					<?php endif; ?>
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
