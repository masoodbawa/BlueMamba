tinymce.init({
	selector: "textarea",
	theme: "modern",
  menubar: false,
  statusbar: false,
	content_css: "../../../css/main.css" ,
	plugins: [
		"advlist autolink lists link image charmap preview hr anchor",
		"searchreplace wordcount visualblocks visualchars code fullscreen",
		"insertdatetime nonbreaking save table contextmenu directionality",
		"paste"
	],
	toolbar1: "insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image | print preview media | forecolor backcolor emoticons",
	templates: [
		{title: 'Test template 1', content: '<b>Test 1</b>'},
		{title: 'Test template 2', content: '<em>Test 2</em>'}
	],
	autosave_ask_before_unload: false,
  language: 'en_GB'
});