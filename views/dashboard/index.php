<div class="wrap">
	<?php include( JUSTIMAGEOPTIMIZER_ROOT . '/views/_tabs.php' ); ?>
	<div class="row">
		<div class="column">
			<h2 class="head-title">Stats</h2>
			<div id="progress"></div>
			<div id="saving"></div>
		</div>
		<div class="column-s">
			<h2 class="head-title">We recommends</h2>
			<p class="description">To optimize your site and get high score
				on Google PageSpeed Insight we
				recommend such plugins:</p>
			<ul>
				<li><a href="">Autoptimize</a></li>
				<li><a href="">WP Super Cache</a></li>
				<li><a href="">Yoast SEO</a></li>
				<li><a href="">Just Responsive images</a></li>
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
				['Image in queue', <?php echo $model->get_images_stat( false ); ?>],
				['All Images', <?php echo $model->get_images_stat( true ); ?>]
			]);
			var data2 = google.visualization.arrayToDataTable([
				['Optimizer', 'Saving'],
				['Saving MB', <?php echo $model->get_images_disk_usage( true ); ?> ],
				['Total MB', <?php echo $model->get_images_disk_usage( false ); ?> ]
			]);

			// Optional; add a title and set the width and height of the chart
			var options = {'title': 'Progress', legend: 'true', is3D: true, 'width': '100%', 'height': 400};
			var options2 = {'title': 'Saving', legend: 'true', is3D: true, 'width': '100%', 'height': 400};

			// Display the chart inside the <div> element with id="piechart"
			var progress = new google.visualization.PieChart(document.getElementById('progress'));
			var saving = new google.visualization.PieChart(document.getElementById('saving'));
			progress.draw(data, options);
			saving.draw(data2, options2);
		}
	</script>
</div>
