window.onload = function(){
  let location = window.location.pathname;

  if (location.indexOf( 'plugins.php' ) === -1 ) {
    return;
  }

  document.querySelector('[data-slug="just-image-optimizer"] a').addEventListener('click', function(event){
    event.preventDefault();
    let urlRedirect = document.querySelector('[data-slug="just-image-optimizer"] a').getAttribute('href');

    jQuery('body').append('<div id="modal-confirm">' +
      '<div class="modal-confirm">Do you want to keep converted images?</div>' +
      '<div class="md-buttons">' +
      '<button class="button button-primary" value="yes">Yes</button>' +
      '<button class="button button-secondary" value="no">No</button>' +
      '</div>');

    jQuery('#modal-confirm').dialog({
      modal: true,
      width: 400,
      height: 150,
      classes: { "ui-dialog" : "md-confirm" },
      resizable: false,
    });

    jQuery('.md-buttons .button').on('click', function(){
      let answer = jQuery(this).val();

      if ('yes' === answer) {
        window.location.href = urlRedirect + '&keep=true';
      } else if ( 'no' === answer ) {
        window.location.href = urlRedirect + '&keep=false';
      }

    });

  });
}