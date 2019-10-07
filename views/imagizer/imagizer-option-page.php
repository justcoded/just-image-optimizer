<?php

use JustCoded\WP\ImageOptimizer\models;

$model = models\ImagizerSettings::instance();
$uo    = $model->uo();
?>

<div class="wrap">
	<h1>Just Imagizer - Image Converter Options Page</h1>
	<em>Let's convert your images!</em>

	<?php
	$model::page_tabs();

	switch ( $model->current_tab() ) {
		case 'processing':
			include JUSTIMAGEOPTIMIZER_ROOT . '/views/imagizer/_processing.php';
			break;
		case 'options':
			include JUSTIMAGEOPTIMIZER_ROOT . '/views/imagizer/_options.php';
			break;
		default:
			echo 'Tab does\'t exists';
			break;
	}
	?>

</div>

<script type="text/javascript">
	let total = <?php esc_attr_e( $model->options->images_total ); ?>;
	let $title = '<?php esc_attr_e( get_admin_page_title() ); ?>';
	let $old_title = $title;
</script>
