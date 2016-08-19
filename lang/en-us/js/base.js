
/**
 * Logout
 */
function logout() {
  $("<div>Are you sure you want to log out?</div>").dialog({
    title: 'Log Out?',
    resizable: false,
    modal: true,
    buttons: {
      "Yes": function() {
        window.location = 'logout';
        $(this).dialog("close");
      },
      "No": function() {
        $(this).dialog("close");
      }
    }
  });
}