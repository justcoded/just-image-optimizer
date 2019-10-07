<?php

use JustCoded\WP\ImageOptimizer\models;

$opt = models\ImagizerSettings::get_options();
?>

<table class="imgagizer-dash-widget">
	<thead>
	<tr>
		<th colspan="2">Converted</th>
		<th colspan="2">Options</th>
	</tr>
	</thead>
	<tbody>
	<tr>
		<td>WEBP:</td>
		<td><?php esc_attr_e( $opt->converted['webp'] ); ?></td>
		<td>Replacement:</td>
		<td class="bigger"><?php esc_attr_e( true === $opt->replacement ? '&#9745;' : '&#9744;' ); ?></td>
	</tr>
	<tr>
		<td>JP2:</td>
		<td><?php esc_attr_e( $opt->converted['jp2'] ); ?></td>
		<td>Lazyload:</td>
		<td class="bigger"><?php esc_attr_e( true === $opt->lazy ? '&#9745;' : '&#9744;' ); ?></td>
	</tr>
	<tr>
		<td>Total images:</td>
		<td><strong><?php esc_attr_e( $opt->images_total ); ?></strong></td>
		<td></td>
		<td></td>
	</tr>
	</tbody>
</table>
