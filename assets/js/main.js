jQuery( document ).ready( function ( $ ) {
	const radioInputs = $( 'input:radio' );
	const saveButton = $( '#submit-connect' );
	const connectStatus = $( '#connect-status ul' );
	const checkConverters = $( '#check_converters' );
	const apiKeyContainer = $( '#api_key' );
	const apiKeyConnect = $( '#api_connect' );
	const connectButton = $( '#submit-connect' );
	const findApi = $( '#find_api' );

	$.each( radioInputs, function () {
		if ( $( this ).is( ':checked' ) ) {
			display_frame( $( this ) );
		}
	} );

	radioInputs.on( 'click', function () {
		display_frame( $( this ) );
	} );

	$.ajaxSetup({
		beforeSend: ajaxBeforeSend
	});

	function ajaxBeforeSend() {
		connectStatus.html('<li class="notice-warning">Checking...</li>');
	}

	checkConverters.on( 'click', function () {
		connectStatus.html( '' );
		saveButton.prop( 'disabled', true );
		let $data = {
			action: 'joi_check_connect',
			service: 'localconverter',
		};
		$.post( ajaxurl, $data, function ( response ) {
			if ( 'true' === response ) {
				connectStatus
					.removeClass( 'notice-error' )
					.addClass( 'notice-success' )
					.html( '<li>Ok</li>' );
				saveButton.prop( 'disabled', false );
			} else if ( 'undefined' !== typeof response || '0' !== response ) {
				let results = $.parseJSON( response );
				$.each( results, function ( i, v ) {
					if ( connectStatus.hasClass( 'notice-success' ) ) {
						connectStatus
							.removeClass( 'notice-success' )
							.addClass( 'notice-error' );
					}

					connectStatus.append( '<li>' + v + '</li>' );
				} );
			}
		} );
	} );

	apiKeyContainer.on( 'change', function ( e ) {
		apiKeyConnect.show();
		connectButton.prop( 'disabled', true );
		$( '.notice-error' ).html( '' );
	} );

	apiKeyConnect.on( 'click', function ( e ) {
		e.preventDefault();
		apiKeyConnect.text( 'Connecting...' );
		let api_key = $( '#api_key' ).val();
		let data = {
			action: 'joi_check_api_connect',
			api_key: api_key,
			service: 'google_insights',
		};
		$.post( ajaxurl, data, function ( response ) {
			if ( response === '1' ) {
				$( '.notice-error' ).html( 'Connected' );
				apiKeyConnect.hide();
				connectButton.prop( 'disabled', false );
			} else {
				$( '.notice-error' ).html( 'API key is invalid' );
			}
			apiKeyConnect.text( 'Connect' );
		} );

	} );

	findApi.on( 'click', function ( e ) {
		e.preventDefault();
		var instructions_tip = document.getElementById( "instructions-api" );
		if ( instructions_tip.style.display === "none" ) {
			instructions_tip.style.display = "block";
		} else {
			instructions_tip.style.display = "none";
		}
	} );
} );

function display_frame ( inpt ) {
	let selector = jQuery( '#' + inpt.val() + '-info' );
	let frames = jQuery( '.frames' );
	frames.css( 'display', 'none' );
	selector.css( 'display', 'block' );
}
