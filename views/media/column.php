<?php
if ( $column_name !== 'optimize' ) {
	return;
}
?>
<p>Queued (#<?php echo $id; ?>)</p>
<a class="optimize-now" href="#<?php echo $id; ?>" data-attach-id="<?php echo $id; ?>">optimize now</a>