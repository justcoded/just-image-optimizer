<?php

use JustCoded\WP\ImageOptimizer\controllers;

?>

<form id="options" class="im-tabs"
		action="<?php esc_attr_e( admin_url() ); ?>upload.php?page=<?php esc_attr_e( controllers\ServiceController::$page_path . '/' ); ?>imagizer-option-page.php&tab=options"
		method="post">
	<input type="hidden" name="options_nonce" value="<?php esc_attr_e( wp_create_nonce( '_options' ) ); ?>"/>
	<div class="imgzr-option">
		<div class="switchers">
			<label  class="label" for="imgzr-replace">Replace images URL</label>
			<label class="switch">
				<input type="checkbox" id="imgzr-replace" value="1"
						name="imgzr-replace" <?php echo $model->options->replacement ? 'checked' : ''; ?> />
				<span class="slider round"></span>
			</label>
		</div>

		<div class="switchers">
			<label class="label" for="imgzr-lazy">Lazyload images<br>
				<em class="label-small">Can not be enabled without replacment</em>
			</label>
			<label class="switch">
				<input type="checkbox" id="imgzr-lazy" value="1"
						name="imgzr-lazy" <?php echo $model->options->lazy ? 'checked' : ''; ?> />
				<span class="slider round"></span>
			</label>
		</div>
	</div>

	<div class="imgzr-option">
		<label for="imgzr-amount">Amount per pass: </label>
		<input type="number" id="imgzr-amount" min="0" max="999" name="imgzr-amount"
				value="<?php esc_attr_e( $model->options->amount ); ?>"/>
	</div>

	<div class="imgzr-option">

	</div>

	<div class="tips">
		<button class="button button-primary">Save</button>

		<?php if ( true === $uo ) : ?>
			<span class="options-tip saved">Options saved.</span>
		<?php elseif ( false === $uo ) : ?>
			<span class="options-tip not-saved">Options not saved!</span>
		<?php endif; ?>
	</div>

</form>
