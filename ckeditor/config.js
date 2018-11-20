/*
Copyright (c) 2003-2010, CKSource - Frederico Knabben. All rights reserved.
For licensing, see LICENSE.html or http://ckeditor.com/license
*/

CKEDITOR.editorConfig = function( config )
{
	// Define changes to default configuration here. For example:
	// config.language = 'fr';
	// config.uiColor = '#AADC6E';
	config.removePlugins = 'scayt';
	config.extraPlugins = 'stylesheetparser';
	config.contentsCss = '/css/styles.css';
	config.stylesSet = [];

	//config.baseHref = 'http://www.formula-shtor_ru';
	//config.baseHref = '/';
	//config.stylesheetParser_skipSelectors = /(^html\.|\.body\.)/i;
};

CKEDITOR.on( 'instanceReady', function( ev ) {
	ev.editor.dataProcessor.writer.setRules( 'p',
	{
		indent : false,
		breakBeforeOpen : true,
		breakAfterOpen : false,
		breakBeforeClose : false,
		breakAfterClose : true
	});
	ev.editor.dataProcessor.writer.setRules( 'div',
	{
		indent : false,
		breakBeforeOpen : true,
		breakAfterOpen : false,
		breakBeforeClose : false,
		breakAfterClose : true
	});
	ev.editor.dataProcessor.writer.setRules( 'ul',
	{
		indent : false,
		breakBeforeOpen : true,
		breakAfterOpen : false,
		breakBeforeClose : false,
		breakAfterClose : true
	});
	ev.editor.dataProcessor.writer.setRules( 'ol',
	{
		indent : false,
		breakBeforeOpen : true,
		breakAfterOpen : false,
		breakBeforeClose : false,
		breakAfterClose : true
	});
	ev.editor.dataProcessor.writer.setRules( 'li',
	{
		indent : false,
		breakBeforeOpen : true,
		breakAfterOpen : false,
		breakBeforeClose : false,
		breakAfterClose : true
	});
	ev.editor.dataProcessor.writer.setRules( 'h1',
	{
		indent : false,
		breakBeforeOpen : true,
		breakAfterOpen : false,
		breakBeforeClose : false,
		breakAfterClose : true
	});
	ev.editor.dataProcessor.writer.setRules( 'h2',
	{
		indent : false,
		breakBeforeOpen : true,
		breakAfterOpen : false,
		breakBeforeClose : false,
		breakAfterClose : true
	});
	ev.editor.dataProcessor.writer.setRules( 'h3',
	{
		indent : false,
		breakBeforeOpen : true,
		breakAfterOpen : false,
		breakBeforeClose : false,
		breakAfterClose : true
	});
	ev.editor.dataProcessor.writer.setRules( 'h4',
	{
		indent : false,
		breakBeforeOpen : true,
		breakAfterOpen : false,
		breakBeforeClose : false,
		breakAfterClose : true
	});
	ev.editor.dataProcessor.writer.setRules( 'h5',
	{
		indent : false,
		breakBeforeOpen : true,
		breakAfterOpen : false,
		breakBeforeClose : false,
		breakAfterClose : true
	});
	ev.editor.dataProcessor.writer.setRules( 'h6',
	{
		indent : false,
		breakBeforeOpen : true,
		breakAfterOpen : false,
		breakBeforeClose : false,
		breakAfterClose : true
	});
	ev.editor.dataProcessor.writer.setRules( 'h7',
	{
		indent : false,
		breakBeforeOpen : true,
		breakAfterOpen : false,
		breakBeforeClose : false,
		breakAfterClose : true
	});
	ev.editor.dataProcessor.writer.setRules( 'h8',
	{
		indent : false,
		breakBeforeOpen : true,
		breakAfterOpen : false,
		breakBeforeClose : false,
		breakAfterClose : true
	});
	ev.editor.dataProcessor.writer.setRules( 'h9',
	{
		indent : false,
		breakBeforeOpen : true,
		breakAfterOpen : false,
		breakBeforeClose : false,
		breakAfterClose : true
	});
	ev.editor.dataProcessor.writer.setRules( 'table',
	{
		indent : false,
		breakBeforeOpen : true,
		breakAfterOpen : true,
		breakBeforeClose : true,
		breakAfterClose : true
	});
	ev.editor.dataProcessor.writer.setRules( 'tbody',
	{
		indent : false,
		breakBeforeOpen : true,
		breakAfterOpen : true,
		breakBeforeClose : true,
		breakAfterClose : true
	});
	ev.editor.dataProcessor.writer.setRules( 'tfoot',
	{
		indent : false,
		breakBeforeOpen : true,
		breakAfterOpen : true,
		breakBeforeClose : true,
		breakAfterClose : true
	});
	ev.editor.dataProcessor.writer.setRules( 'thead',
	{
		indent : false,
		breakBeforeOpen : true,
		breakAfterOpen : true,
		breakBeforeClose : true,
		breakAfterClose : true
	});
	ev.editor.dataProcessor.writer.setRules( 'tr',
	{
		indent : false,
		breakBeforeOpen : true,
		breakAfterOpen : true,
		breakBeforeClose : true,
		breakAfterClose : true
	});
	ev.editor.dataProcessor.writer.setRules( 'td',
	{
		indent : false,
		breakBeforeOpen : true,
		breakAfterOpen : false,
		breakBeforeClose : false,
		breakAfterClose : true
	});
	ev.editor.dataProcessor.writer.setRules( 'noindex',
	{
		indent : false,
		breakBeforeOpen : true,
		breakAfterOpen : false,
		breakBeforeClose : false,
		breakAfterClose : true
	});
});