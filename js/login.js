
function login() {
	if ( ($('#username').val() != '') && ($('#password').val() != '') ) {
		javascript:loginform.submit();
	}
	else {
    $("<div>Please enter your username and password</div>").dialog({
      title: 'Log In',
      resizable: false,
      modal: true,
      buttons: {
        "Ok": function() {
          $(this).dialog("close");
        }
      }
    });
	}
}


function changeLanguage() {
	if($('#lang').val() != '') {
    location.href="login?lang=" + $('#lang').val();
  }
}
