<?php
if ( isset( $_SERVER['HTTP_USER_AGENT'] ) ) {
	if ( stripos( $_SERVER['HTTP_USER_AGENT'], 'chrome' ) !== false ) {
		$cache_path = $cache_path . 'chrome/';
	} elseif ( stripos( $_SERVER['HTTP_USER_AGENT'], 'safari' ) !== false ) {
		$cache_path = $cache_path . 'safari/';
	}
}
