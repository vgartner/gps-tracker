// CloudService JS

$(function() {
    console.log( 'custom.js: ready!' );
    btnConfig.setup();

    $('#historico .header').on('click', function(){
      $('#historico').toggleClass('opened');
    })

});

var btnConfig = {
  setup : function(){
      $('.config-btn').on('click', function(){
        $('body').toggleClass('config-btn-opened');
      });
  }
}
