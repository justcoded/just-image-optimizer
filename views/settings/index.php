<div class="wrap">
	<?php include( JUSTIMAGEOPTIMIZER_ROOT . '/views/_tabs.php' ); ?>
	<form method="post" action="<?php get_permalink(); ?>" enctype="multipart/form-data">
		<table class="form-table">
			<tr>
				<th scope="row"><?php _e( 'Automatically optimize uploads', \justImageOptimizer::TEXTDOMAIN ); ?></th>
				<td>
					<input <?php echo( get_option( $model::DB_OPT_AUTO_OPTIMIZE ) === '1' ? 'checked' : '' ); ?>
						type="checkbox"
						name="auto_optimize"
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
							name="image_sizes[]"
							value="<?php echo $size; ?>"><?php echo $size; ?><br>
						<?php else : ?>
							<input type="checkbox" name="image_sizes[]"
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
					<input type="text" name="image_limit" value="<?php echo get_option( $model::DB_OPT_IMAGE_LIMIT ); ?>">
					<p>how many images can be optimized at a time</p>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php _e( 'Bulk size limit', \justImageOptimizer::TEXTDOMAIN ); ?></th>
				<td>
					<input type="text" name="size_limit" value="<?php echo get_option( $model::DB_OPT_SIZE_LIMIT ); ?>">
					<p>optimize images, which file size in total is not greater than limit</p>
					<p>* can decrease Bulk images limit, if original images are very big</p>
					<p>** use 0 to ignore this limit</p>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php _e( 'Regenerate image thumbnails before optimize', \justImageOptimizer::TEXTDOMAIN ); ?></th>
				<td>
					<input <?php echo( get_option( $model::DB_OPT_BEFORE_REGEN ) === '1' ? 'checked' : '' ); ?>
						type="checkbox"
						name="before_regen"
						value="1">
					<p>can affect server performance if you upload images very often</p>
				</td>
			</tr>
		</table>
		<input
			type="submit" name="submit-settings" class="button button-primary" value="Save">
	</form>
</div>
