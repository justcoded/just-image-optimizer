<?php
/* @var $model \JustCoded\WP\ImageOptimizer\models\Media */
$dash_stats          = $model->get_dashboard_attachment_stats();
$dash_saving_size    = ( ! empty( $dash_stats[0]->saving_size ) ? $dash_stats[0]->saving_size : 0 );
$dash_saving_percent = ( ! empty( $dash_stats[0]->percent ) ? $dash_stats[0]->percent : 0 );
$chart_saving        = $model->size_format_explode( $dash_saving_size );
$chart_disk_space    = $model->size_format_explode( $model->get_disk_space_size() );
?>
<div class="wrap jio-admin-page">
	<?php include( JUSTIMAGEOPTIMIZER_ROOT . '/views/_tabs.php' ); ?>
	<?php include( JUSTIMAGEOPTIMIZER_ROOT . '/views/dashboard/_requirements.php' ); ?>

	<?php if ( empty( \JustImageOptimizer::$settings->auto_optimize ) ) : ?>
		<div class="update-nag">
			<strong>Automatic image optimization is disabled. Please check
				<a href="<?php echo admin_url(); ?>upload.php?page=just-img-opt-settings">Settings</a>
				tab to enable it.</strong>
		</div><br>
	<?php endif; ?>
	<?php if ( empty( \JustImageOptimizer::$settings->image_sizes ) ) : ?>
		<div class="update-nag">
			<strong>Image sizes for optimization are not selected. Please check
				<a href="<?php echo admin_url(); ?>upload.php?page=just-img-opt-settings">Settings</a>
				tab to select sizes.</strong>
		</div>
	<?php endif; ?>
	<div class="row">
		<div class="column middle">
			<h2>Progress</h2>
			<p><strong><?php echo esc_html( $model->get_images_stat( false ) === '0' ?
					$model->get_images_stat( false ) :
						$model->get_in_queue_image_count() ); ?> images</strong>
				of <?php echo esc_html( $model->get_images_stat( true ) ); ?>
				are in queue.</p>
			<div id="progress" style="height: 300px; width: 95%;"></div>
		</div>
		<div class="column middle">
			<h2>Disk Space Saving</h2>
			<p><strong><?php echo esc_html($dash_saving_percent); ?>% saved</strong> (<?php echo jio_size_format( $dash_saving_size ); ?>),
				disk usage: <?php echo jio_size_format( $model->get_images_disk_usage() ); ?></p>
			<div id="saving" style="height: 300px; width: 95%;"></div>
		</div>

		<div class="column-l">
			<h2 class="head-title">We recommend</h2>
			<p class="description">To optimize your site and get high score
				on Google PageSpeed Insight we
				recommend such plugins:</p>
			<ul>
				<li><a target="_blank" href="https://wordpress.org/plugins/autoptimize/">Autoptimize</a></li>
				<li><a target="_blank" href="https://wordpress.org/plugins/wp-super-cache/">WP Super Cache</a></li>
				<li><a target="_blank" href="https://wordpress.org/plugins/wordpress-seo/">Yoast SEO</a></li>
				<li><a target="_blank" href="https://wordpress.org/plugins/just-responsive-images/">Just Responsive
						Images</a></li>
			</ul>
		</div>
	</div>
	<script type="text/javascript">
		// Load google charts
		google.charts.load('current', {'packages': ['corechart']});
		google.charts.setOnLoadCallback(drawChart);

		// Draw the chart and set the chart values
		function drawChart() {
			var data = google.visualization.arrayToDataTable([
				['Optimizer', 'Progress'],
				['In queue', <?php echo (int) $model->get_in_queue_image_count(); ?>],
				['Optimized images', <?php echo (int) $model->get_count_images_processed(); ?>]

			]);
			var data2 = google.visualization.arrayToDataTable([
				['Optimizer', 'Saving'],
				['Saved', {
					v:<?php echo intval( $chart_saving['bytes'] ); ?>,
					f: "<?php echo esc_attr($chart_saving['unit']); ?>"
				}],
				['Disk space', {
					v:<?php echo intval( $chart_disk_space['bytes'] ); ?>,
					f: "<?php echo esc_attr($chart_disk_space['unit']); ?>"
				}]
			]);

			// Optional; add a title and set the width and height of the chart
			var options = {
				sliceVisibilityThreshold: 0,
				'title': '',
				is3D: true,
				backgroundColor: 'transparent',
				colors: ['#FE2E2E', '#04B431'],
				chartArea: {left:10,top:'20%',width:'95%',height:'80%'},
			    fontSize: 12
			};
			var options2 = {
				sliceVisibilityThreshold: 0,
				'title': '',
				is3D: true,
				backgroundColor: 'transparent',
				colors: ['#04B431', '#0174DF'],
                chartArea: {left:10,top:'20%',width:'95%',height:'80%'},
                fontSize: 12
			};

			// Display the chart inside the <div> element with id="piechart"
			var progress = new google.visualization.PieChart(document.getElementById('progress'));
			var saving = new google.visualization.PieChart(document.getElementById('saving'));
			progress.draw(data, options);
			saving.draw(data2, options2);
		}
	</script>
</div>
