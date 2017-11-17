<div class="wrap">
	<?php include( JUSTIMAGEOPTIMIZER_ROOT . '/views/_tabs.php' ); ?>
	<?php do_action( 'joi_dashboard_admin_notice' ); ?>
	<div class="row">
		<h1>Stats</h1>
		<div class="column middle">
			<h2 class="head-title">Progress</h2>
			<div id="progress" style="height: 500px; width: 95%;"></div>
			<p><?php echo $model->get_images_stat( false ); ?> images in queue
				of <?php echo $model->get_images_stat( true ); ?> images</p>
		</div>
		<div class="column middle">
			<h2 class="head-title">Saving</h2>
			<div id="saving" style="height: 500px; width: 95%;"></div>
			<p> <?php echo $model->saving_size; ?>MB
				/ <?php echo $model->get_saving_percent_dashboard(); ?>% saving,
				disk usage: <?php echo $model->get_images_disk_usage(); ?>MB</p>
		</div>
		<div class="column-l">
			<h2 class="head-title">We recommends</h2>
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
				['In queue', <?php echo $model->get_in_queue_image_count(); ?>],
				['Optimized images', <?php echo $model->get_count_images_processed(); ?>]

			]);
			var data2 = google.visualization.arrayToDataTable([
				['Optimizer', 'Saving'],
				['Saved, MB', <?php echo $model->saving_size; ?> ],
				['Disk space, MB', <?php echo $model->get_disk_space_size(); ?> ]
			]);

			// Optional; add a title and set the width and height of the chart
			var options = {
				sliceVisibilityThreshold: 0, 'title': '', is3D: true, backgroundColor: 'transparent',
				colors: ['#FE2E2E', '#04B431']
			};
			var options2 = {
				sliceVisibilityThreshold: 0, 'title': '', is3D: true, backgroundColor: 'transparent',
				colors: ['#04B431', '#0174DF']
			};

			// Display the chart inside the <div> element with id="piechart"
			var progress = new google.visualization.PieChart(document.getElementById('progress'));
			var saving = new google.visualization.PieChart(document.getElementById('saving'));
			progress.draw(data, options);
			saving.draw(data2, options2);
		}
	</script>
</div>
