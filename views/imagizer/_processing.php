<div id="processing" class="proccessing im-tabs">

	<div id="test-div"></div>

	<div class="attachments-list">
		<span>System: <?php esc_attr_e( PHP_OS ); ?></span>
		<span>Images total: <?php esc_attr_e( $model->options->images_total ); ?></span>
		<span class="imgzr-webp">Webp images: <p class="inn-res"><?php esc_attr_e( $model->options->converted['webp'] ); ?></p>

			<?php if ( 0 !== $model->options->converted['webp'] ) : ?>
				<button id="rm-webp" class="button button-link-delete" onclick="imagizer_remove_images(this)">Delete webp</button>
			<?php endif; ?>

		</span>
		<span class="imgzr-jp2">JP2 images: <p class="inn-res"><?php esc_attr_e( $model->options->converted['jp2'] ); ?></p>

			<?php if ( 0 !== $model->options->converted['jp2'] ) : ?>
				<button id="rm-jp2" class="button button-link-delete" onclick="imagizer_remove_images(this)">Delete jp2</button>
			<?php endif; ?>

		</span>
		<span class="imgzr-converters">System converters:
					<?php if ( $model->converters ) : ?>
						<div class="lib-list">Available converters:
							<?php foreach ( $model->converters as $converter ) : ?>
								<div><?php esc_attr_e( $converter ); ?></div>
							<?php endforeach; ?>
						</div>
					<?php else : ?>
						<div class="lib-list red">Unfortunately: no converters installed. Will try to use built-in converters.</div>
					<?php endif; ?>
				</span>
	</div>

	<div class="controls">
		<div id="image-proccessing">
			<label>50</label>
			<input id="imgzr-quality" type="range" max="100" min="50" step="5"
					value="<?php esc_attr_e( $model->options->quality ); ?>"/>
			<label for="imgzr-quality" id="imgzr-show-quality"><?php esc_attr_e( $model->options->quality ); ?></label>
			<button class="button button-primary action-btn">Generate images</button>
		</div>
	</div>

	<div
			id="progress"
			class="imgzr-progress"
			data-nonce="<?php esc_attr_e( wp_create_nonce( 'wp_rest' ) ); ?>"
			data-url="<?php esc_attr_e( get_rest_url( 0, 'imagizer/v1/progress', 'http' ) ); ?>">
		<progress class="imgzr-progressbar" value="0" max="<?php esc_attr_e( $model->options->images_total ); ?>"></progress>
		<span class="imgzr-progressbar-count"><?php esc_attr_e( $model->options->images_total ); ?></span>
	</div>
	<div id="results"></div>
</div>
