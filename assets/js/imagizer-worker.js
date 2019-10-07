'use srtict';

/* Variable for getting response from server */
let WP_ACTION = {
	global: 'imagizer',
	renew: 'imagizer_renew_images_count',
	remove: 'imagizer_remove_images',
};
let NONCE = 'nonce';
let API_URL = 'url';
let AJAX_URL = 'admin-ajax.php';

/* Options variables */
let timer = 5000;
let DISABLE_CLASS = 'disabled';

/* Dom elements array */
let $elements = {};

jQuery(document).ready(function () {
	/* DOM elements */
	$elements.$buttons = jQuery('.action-btn');
	$elements.$website_title = jQuery('title');
	$elements.$imgQuality = jQuery('#imgzr-quality');
	$elements.$imgShowQuality = jQuery('#imgzr-show-quality');
	$elements.$resultInfo = jQuery('#results');
	$elements.$navTab = jQuery('.nav-tab');
	$elements.$imgProgress = jQuery('.imgzr-progress');
	$elements.$inputQualityRange = jQuery('#progress');
	$elements.$progressBar = jQuery('.imgzr-progressbar');
	$elements.$progressBarSpan = jQuery('.imgzr-progress span');
	$elements.$webpContainer = jQuery('.imgzr-webp');
	$elements.$jp2sContainer = jQuery('.imgzr-jp2');

	$elements.$imgQuality.on('change', function () {
		let qualityLabel = jQuery(this).val();
		$elements.$imgShowQuality.text(qualityLabel);
	});

	$elements.$buttons.on('click', function () {
		$elements.$imgProgress.show();
		$elements.$navTab.addClass(DISABLE_CLASS);
		let quality = $elements.$imgQuality.val();
		let nonceValue = $elements.$inputQualityRange.data(NONCE);
		let data = {
			action: WP_ACTION.global,
			quality: quality,
			nonce: nonceValue,
		};
		let get_progress = setInterval(imagizer_get_progress, 2000);

		jQuery.ajax({
			url: AJAX_URL,
			type: 'post',
			data: data,
			cache: false,
			async: true,
			beforeSend: function () {
				$elements.$resultInfo.html('Wait.. I\'m working. Please, do not update or leave this page before conversion will be done.');
				render_progress({
					progress: 'Conversion starting...'
				});
			},
			success: function (response) {
				imagizer_get_progress();
				$elements.$resultInfo.html('').append(response);
				jQuery.post(AJAX_URL, {
						action: WP_ACTION.renew,
					})
					.done(function (json) {
						let count = jQuery.parseJSON(json);
						$elements.$webpContainer
							.text('Webp images: ' + count.webps)
							.append('<button id="rm-webp" class="button button-link-delete" onclick="imagizer_remove_images(this)">Delete webp</button>');
						$elements.$jp2sContainer
							.text('JP2 images: ' + count.jp2s)
							.append('<button id="rm-jp2" class="button button-link-delete" onclick="imagizer_remove_images(this)">Delete jp2</button>');
					});
				setTimeout(function () {
					$elements.$imgProgress.fadeOut('fast');
					$elements.$resultInfo.html('');
					$elements.$website_title.text($old_title);
					clearInterval(get_progress);
					$elements.$navTab.removeClass(DISABLE_CLASS);
				}, timer);
			},
			error: function (response) {
				console.log(response);
				$elements.$resultInfo.html('').append(response);
			}
		});
	});
});

function imagizer_get_progress() {
	let url = $elements.$inputQualityRange.data(API_URL);
	let nonce = $elements.$inputQualityRange.data(NONCE);

	jQuery.ajax({
			url: url,
			type: 'get',
			headers: {
				'X-WP-Nonce': nonce
			}
		})
		.done((progress) => {
			render_progress(progress);
		})
		.fail((error) => {
			throw new Error(error);
		});
}

function render_progress(response) {
	let progress_old = '';
	if (!isNaN(response.progress)) {
		$elements.$progressBar.val(response.progress);
		$elements.$progressBarSpan.text(`${response.progress} / ${total}`);
		$elements.$website_title
			.text('')
			.text(`(${response.progress})Converting < ${$title}`);
		progress_old = response.progress;
	} else {
		$elements.$progressBar.val(progress_old);
		$elements.$progressBarSpan.text(response.progress);
	}
}

function imagizer_remove_images(el) {
	let _confirm = confirm('Are you sure?');
	let id = jQuery(el).attr('id');
	let type = id.substr(3, id.length);

	if (_confirm) {
		$elements.$imgProgress.show();
		$elements.$navTab.addClass(DISABLE_CLASS);
		let get_progress = setInterval(imagizer_get_progress, 1000);
		jQuery.post(AJAX_URL, {
				action: WP_ACTION.remove,
				type: type
			})
			.done(function () {
				jQuery.post(AJAX_URL, {
						action: WP_ACTION.renew
					})
					.done(function (json) {
						let count = jQuery.parseJSON(json);

						$elements.$webpContainer.text(`Webp images: ${count.webps}`);
						if (0 !== count.webps) {
							$elements.$webpContainer.append('<button id="rm-webp" class="button button-link-delete" onclick="imagizer_remove_images(this)">Delete webp</button>');
						}

						$elements.$jp2sContainer.text(`JP2 images: ${count.jp2s}`);
						if (0 !== count.jp2s) {
							$elements.$jp2sContainer.append('<button id="rm-jp2" class="button button-link-delete" onclick="imagizer_remove_images(this)">Delete jp2</button>');
						}

						setTimeout(function () {
							$elements.$imgProgress.fadeOut('fast');
							$elements.$resultInfo.html('');
							$elements.$website_title.text($old_title);
							clearInterval(get_progress);
							$elements.$navTab.removeClass(DISABLE_CLASS);
						}, 2000);
					});
			})
			.fail(function (response) {
				console.log(response);
			});
	}
}