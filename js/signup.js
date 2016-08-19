var loading = false;


/**
 * Change Language
 */
function changeLanguage() {
	if($('#lang').val() != '') {
    var path = "signup?lang=" + $('#lang').val();
    if($('#referral').val() != '') {
      path += "&id=" + $('#referral').val();
    }
    location.href = path;
  }
}

/**
 * Sign Up
 */
function signup() {
  
  // Kick back if not complete
  $('#signup_creating_account').show();
  $('#signup_login').hide();

  $(".inputrequire").removeClass("inputrequire");

  $.ajax({
    url: '/signup',
    data: {
      action: 'finalize',
      default_lang: $('#default_lang').val(),
      referral: $('#referral').val(),
      firstname: $('#firstname').val(),
      email: $('#email').val(),
      password: $('#password').val()
    },
    dataType: 'json',
    type: 'post',
    success: function(data) {

      if(data.error == 'true') {
        // Highlight Field
        if(typeof(data.field) == "undefined" || data.field == '') {
          $("#" + data.field).addClass("inputrequire");
        }
        $('#signup_login').show();
        $('#signup_creating_account').hide();
        showError(data.message);
      }
      if(data.message == 'ok') {
        $('#signup_account_ready').show();
        $('#signup_creating_account').hide();
        // Facebook Pixel
        fbq('track', 'CompleteRegistration');  
      }

    },
    error: function(response, status, errorThrown) {
      var err = $('Error', response.responseText).text();
      if (err)
        showError(err);
      else
        showError('Unable to function: ' + status);
    }
  });    
    
}



/**
 * Go Login
 */
function login() {
  showLoading();
  location.href = '/login?lang=' + $('#default_lang').val();
}



/**
 * Show Notice Dialog
 */
function showNotice(message, title, url) {
  hideLoading();
  var dialogClass = '';
  if(typeof(title) == "undefined" || title == '') {
    dialogClass = 'no-titlebar';
  }
  var okval = $('#default_lang').val();
  $("<div style='text-align:center'" + message + "</div>").dialog({
    title: title,
    dialogClass: dialogClass,
    resizable: false,
    modal: true,
    open: function( event, ui ) {
        $("#dialog-ok-btn").html('<span class="ui-button-text">'+ $('#ok_lang').val() +'</span>');
    },
    buttons: [{
      text: "Ok",
      id: "dialog-ok-btn",
      click: function() {
        if(typeof(url) == "undefined") {
          $(this).dialog("destroy");
        } 
        else {
          showLoading();
          window.location.href = url;
          $(this).dialog("destroy");
        }
      }
    }]
  });
}

/**
 * Show Error Dialog
 */
function showError(error, title) {
  hideLoading();
  var dialogClass = '';
  if(typeof(title) == "undefined") {
    dialogClass = 'no-titlebar';
  }
  $("<div style='text-align:center'>" + error + "</div>").dialog({
    title: title,
    dialogClass: dialogClass,
    resizable: false,
    modal: true,
    open: function( event, ui ) {
        $("#dialog-ok-btn").html('<span class="ui-button-text">'+ $('#ok_lang').val() +'</span>');
    },
    buttons: [{
      text: "Ok",
      id: "dialog-ok-btn",
      click: function() {
        if(typeof(url) == "undefined") {
          $(this).dialog("destroy");
        } 
        else {
          showLoading();
          window.location.href = url;
          $(this).dialog("destroy");
        }
      }
    }]
  });
}

/**
 * Show loading div
 */
function showLoading(message) {
  if(typeof(message) == "undefined") {
    message = '';
  }
  $('#loading_message').html(message);
  $('#loading').dialog({
    dialogClass: 'no-titlebar',
    resizable: false,
    modal: true,
    width: 150,
    height: 123
  });
  loading = true;
}

/**
 * Hide loading div
 */
function hideLoading() {
  loading = false;
  if(loading == true) {
    $("#loading").dialog("close");
    $('#loading_message').html("");
  }
}
