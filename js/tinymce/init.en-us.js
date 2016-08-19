tinymce.init({
	selector: "textarea",
	theme: "modern",
  menubar: false,
  statusbar: false,
	plugins: [
		"advlist autolink lists link image charmap preview hr anchor",
		"searchreplace wordcount visualblocks visualchars code fullscreen",
		"insertdatetime nonbreaking save table contextmenu directionality",
		"paste"
	],
	toolbar1: "bold italic alignleft aligncenter alignright alignjustify bullist numlist outdent indent link image media forecolor backcolor",
	autosave_ask_before_unload: false
});