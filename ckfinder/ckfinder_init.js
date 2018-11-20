$(document).ready(function () {
	var ar_editors = Array();
	var i = 0;
	$('textarea.editor').each(function(){
		ar_editors[i] = CKEDITOR.replace( $(this).attr('id') );
		i++;
	});
	for(var j=0;j<i;j++){
		CKFinder.setupCKEditor( ar_editors[j], '/dasmanov/ckfinder/' ) ;
	}
});