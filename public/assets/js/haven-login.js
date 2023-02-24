(function ($) {

  function initUI(){
    if( $('#haven_checkemail_form').length > 0 ){ 
      $('#haven_checkemail_form')
        .form({
          on:'blur',
          inline:true,
          keyboardShortcuts: false,
          fields: {
            email: {
              identifier  : 'email',
              rules: [
                {
                  type   : 'email',
                  prompt : 'Please enter a valid e-mail'
                }
              ]
            },
          }
        }).on('submit',(e)=>{
          if(!$('#haven_checkemail_form').form('is valid')){
            e.preventDefault();
          } 
        })
      ;
    }
  }

	$(function () {

		initUI();
		
	});
	
})(jQuery);