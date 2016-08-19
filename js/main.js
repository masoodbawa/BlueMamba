var loadingVisible = false;

$(document).ready(function() { 
  // Optimalisation: Store the references outside the event handler:

  function checkWidth() {
    var windowsize = $(window).width();
    if (windowsize >= 460) {
      $('#mobile-rotate').hide();
    } else {
      $('#mobile-rotate').show();
    }
  }
  // Execute on load
  checkWidth();
  // Bind event listener
  $(window).resize(checkWidth);
}); 



/**
 * Show loading div
 */
function showLoading(message) {
  loadingVisible = true;
  if(typeof(message) == "undefined") {
    message = 'Loading...';
  }
  $('<div id="loading" style="text-align:center;"><img src="images/loading.gif" style="padding:8px;"/><br>' + message + '</div>').dialog({
    dialogClass: 'no-titlebar',
    resizable: false,
    modal: true,
    width: 150,
    height: 123
  });
}

/**
 * Hide loading div
 */
function hideLoading() {
  if(loadingVisible === true) {
    $("#loading").dialog("close");
    loadingVisible = false;
  }
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
  $("<div>" + message + "</div>").dialog({
    title: title,
    dialogClass: dialogClass,
    resizable: false,
    modal: true,
    buttons: {
      "Ok": function() {
        if(typeof(url) == "undefined") {
          $(this).dialog("close");
        } 
        else {
          window.location.href = url;
        }
      }
    }
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
  $("<div>" + error + "</div>").dialog({
    title: title,
    dialogClass: dialogClass,
    resizable: false,
    modal: true,
    buttons: {
      "Ok": function() {
        $(this).dialog("close");
      }
    }
  });
}

