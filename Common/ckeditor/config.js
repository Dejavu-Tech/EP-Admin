/**
 * @license Copyright (c) 2003-2014, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.html or http://ckeditor.com/license
 */


CKEDITOR.editorConfig = function( config ) {
	// Define changes to default configuration here.
	// For complete reference see:
	// http://docs.ckeditor.com/#!/api/CKEDITOR.config
	// The toolbar groups arrangement, optimized for two toolbar rows.
	config.plugins = 'about,a11yhelp,basicstyles,bidi,blockquote,clipboard,colorbutton,colordialog,contextmenu,div,elementspath,enterkey,entities,filebrowser,find,floatingspace,font,format,forms,horizontalrule,htmlwriter,image,multiimg,indent,justify,link,list,liststyle,magicline,maximize,newpage,pagebreak,pastefromword,pastetext,preview,print,removeformat,resize,scayt,selectall,showblocks,showborders,sourcearea,specialchar,stylescombo,tab,table,tabletools,templates,toolbar,undo,wsc,wysiwygarea';
	config.toolbarGroups = [
		{ name: 'clipboard',   groups: [ 'clipboard', 'undo' ] },
		{ name: 'editing',     groups: [ 'find', 'selection', 'spellchecker' ] },
		{ name: 'links' },
		{ name: 'others' },  // this is your plugin's position,off course, you can change it!
		{ name: 'insert' },
		{ name: 'forms' },
		{ name: 'tools' },
		{ name: 'document',	   groups: [ 'multiimg', 'mode', 'document', 'doctools' ] },
		'/',
		{ name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ] },
		{ name: 'paragraph',   groups: [ 'list', 'indent', 'blocks', 'align', 'bidi' ] },
		{ name: 'styles' },
		{ name: 'colors' },
		{ name: 'about' }
	];

	// Remove some buttons provided by the standard plugins, which are
	// not needed in the Standard(s) toolbar.
	config.removeButtons = 'Underline,Subscript,Superscript';

	// Set the most common block elements.
	config.format_tags = 'p;h1;h2;h3;h4;h5;h6;pre;address;div';// 'p;h1;h2;h3;pre';

	config.font_names='宋体/宋体;黑体/黑体;Arial/Arial, Helvetica, sans-serif;Century Gothic/Century Gothic;Comic Sans MS/Comic Sans MS, cursive;Courier New/Courier New, Courier, monospace;Georgia/Georgia, serif;Lucida Sans Unicode/Lucida Sans Unicode, Lucida Grande, sans-serif;Tahoma/Tahoma, Geneva, sans-serif;Times New Roman/Times New Roman, Times, serif;Trebuchet MS/Trebuchet MS, Helvetica, sans-serif;Verdana/Verdana, Geneva, sans-serif';
	config.allowedContent = true;

	// Simplify the dialog windows.
	config.removeDialogTabs = 'image:advanced;link:advanced';

	//config.extraPlugins="imagedef"; //注册自定义按钮
	config.extraPlugins="multiimg"; //注册自定义按钮
};
