<div class="wrap">
	<?php include( JUSTIMAGEOPTIMIZER_ROOT . '/views/_tabs.php' ); ?>
	<form method="post" action="<?php get_permalink(); ?>" enctype="multipart/form-data">
		<table class="form-table">
			<tr>
				<th scope="row"><?php _e( 'Automatically optimize uploads', \justImageOptimizer::TEXTDOMAIN ); ?></th>
				<td>
					<input <?php echo( $auto_optimize === '1' ? 'checked' : '' ); ?>
						type="checkbox"
						name="<?php echo $auto_optimize_opt; ?>"
						value="1">
				</td>
			</tr>
			<tr>
				<th scope="row"><?php _e( 'Image sizes to optimize', \justImageOptimizer::TEXTDOMAIN ); ?></th>
				<td class="additional_sizes">
					<?php foreach ( $sizes as $size => $dimensions ) : ?>
						<?php if ( is_array( $image_sizes ) ) : ?>
							<input <?php echo( in_array( $size, $image_sizes ) ? 'checked' : '' ); ?>
							type="checkbox"
							name="<?php echo $image_sizes_opt; ?>"
							value="<?php echo $size; ?>"><?php echo $size; ?><br>
						<?php else : ?>
							<input type="checkbox" name="<?php echo $image_sizes_opt; ?>"
							       value="<?php echo $size; ?>"><?php echo $size; ?><br>
						<?php endif; ?>
					<?php endforeach; ?>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php _e( 'Keep origin', \justImageOptimizer::TEXTDOMAIN ); ?></th>
				<td>
					<input disabled checked type="checkbox" name="keep_origin" value="keep_origin">
				</td>
			</tr>
			<tr>
				<th scope="row"><?php _e( 'Bulk images limit', \justImageOptimizer::TEXTDOMAIN ); ?></th>
				<td>
					<input type="text" name="<?php echo $image_limit_opt; ?>" value="<?php echo $image_limit; ?>">
					<p>how many images can be optimized at a time</p>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php _e( 'Bulk size limit', \justImageOptimizer::TEXTDOMAIN ); ?></th>
				<td>
					<input type="text" name="<?php echo $size_limit_opt; ?>" value="<?php echo $size_limit; ?>">
					<p>optimize images, which file size in total is not greater than limit</p>
					<p>* can decrease Bulk images limit, if original images are very big</p>
					<p>** use 0 to ignore this limit</p>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php _e( 'Regenerate image thumbnails before optimize', \justImageOptimizer::TEXTDOMAIN ); ?></th>
				<td>
					<input <?php echo( $before_regen === '1' ? 'checked' : '' ); ?>
						type="checkbox"
						name="<?php echo $before_regen_opt; ?>"
						value="1">
					<p>can affect server performance if you upload images very often</p>
				</td>
			</tr>
		</table>
		<input
			type="submit" name="submit-settings" class="button button-primary" value="Save">
	</form>
</div>
