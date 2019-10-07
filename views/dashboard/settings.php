<div class="wrap jio-admin-page">
	<?php include( JUSTIMAGEOPTIMIZER_ROOT . '/views/_tabs.php' ); ?>
	<?php $force_requirements_check = true; ?>
	<?php include( JUSTIMAGEOPTIMIZER_ROOT . '/views/dashboard/_requirements.php' ); ?>
	<?php if ( !$model->saved() && $model->check_requirements() ) : ?>
		<div class="update-nag">
			<strong>Please confirm the settings below and Save them.</strong>
		</div><br>
	<?php endif; ?>
	<?php if ( $saved ) : ?>
		<div class="update-nag success-nag">
			<strong>Settings options updated!</strong>
			<strong>Go to <a href="<?php echo admin_url(); ?>upload.php?page=just-img-opt-dashboard">Dashboard page</a>
				to view the general statistics.</strong>
		</div>
	<?php endif; ?>
	<form method="post" action="<?php get_permalink(); ?>" enctype="multipart/form-data">
		<table class="form-table">
			<tr>
				<th scope="row">
					<?php _e( 'Automatically optimize uploads', \JustImageOptimizer::TEXTDOMAIN ); ?>
				</th>
				<td>
					<input type="hidden" name="auto_optimize" value="0">
					<input <?php checked( $model->auto_optimize ); ?>
							type="checkbox"
							name="auto_optimize"
							value="1">
				</td>
			</tr>
			<tr class="image_sizes_set">
				<th scope="row">
					<?php _e( 'Image sizes to optimize', \JustImageOptimizer::TEXTDOMAIN ); ?>
				</th>
				<td class="additional_sizes">
					<input type="hidden" name="image_sizes_all" value="0">
					<label for="check_all_size">
						<input <?php checked( $model->image_sizes_all ); ?>
								id="check_all_size" type="checkbox" name="image_sizes_all"
								value="1">All
					</label>
					<div class="size_checked">
						<input type="hidden" name="image_sizes" value="">
						<?php $i = 0; ?>
						<?php foreach ( $sizes as $size => $dimensions ) : ?>
							<label for="image_size_<?php echo ++$i; ?>" class="label-checkbox">
								<input <?php checked( in_array( $size, $model->image_sizes ) ); ?>
										id="image_size_<?php echo $i; ?>"
										type="checkbox"
										name="image_sizes[]"
										value="<?php echo $size; ?>"><?php echo $size; ?>
							</label>
						<?php endforeach; ?>
					</div>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php _e( 'Keep origin', \JustImageOptimizer::TEXTDOMAIN ); ?></th>
				<td>
					<input onclick="return false;" checked type="checkbox" name="keep_origin" value="1">
				</td>
			</tr>
			<tr>
				<th scope="row"><?php _e( 'Bulk media limit', \JustImageOptimizer::TEXTDOMAIN ); ?></th>
				<td>
					<input type="text" name="image_limit"
						   value="<?php echo $model->image_limit; ?>">
					<p class="description">How many Media can be optimized at a time</p>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php _e( 'Bulk filesize limit', \JustImageOptimizer::TEXTDOMAIN ); ?></th>
				<td>
					<input type="text" name="size_limit" value="<?php echo $model->size_limit; ?>">
					<p class="description">Filesize limit for one optimization request, in MB.</p>
					<p class="description">* set 0 to turn off this limit.</p>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php _e( 'Tries count', \JustImageOptimizer::TEXTDOMAIN ); ?></th>
				<td>
					<input type="text" name="tries_count" value="<?php echo $model->tries_count; ?>">
					<p class="description">Tries count for optimization request</p>
				</td>
			</tr>
			<?php /*
			// TODO: add support in future releases.
			<tr>
				<th scope="row"><?php _e( 'Regenerate image thumbnails before optimize', \JustImageOptimizer::TEXTDOMAIN ); ?></th>
				<td>
					<input type="hidden" name="before_regen" value="0">
					<input <?php checked( $model->before_regen ); ?>
						type="checkbox"
						name="before_regen"
						value="1">
					<p class="description">Can affect server performance if you upload images very often</p>
				</td>
			</tr>
			*/ ?>
		</table>
		<input <?php echo( $model->check_requirements() ? '' : 'disabled' ); ?>
				type="submit" name="submit-settings" class="button button-primary" value="Save">
	</form>
</div>
<style>
	.size_checked {
		padding-top: 10px;
	}

	.label-checkbox {
		width: 200px;
		display: inline-block;
	}
</style>