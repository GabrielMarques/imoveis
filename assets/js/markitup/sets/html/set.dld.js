// ----------------------------------------------------------------------------
// markItUp!
// ----------------------------------------------------------------------------
// Copyright (C) 2008 Jay Salvat
// http://markitup.jaysalvat.com/
// ----------------------------------------------------------------------------
// Html tags
// http://en.wikipedia.org/wiki/html
// ----------------------------------------------------------------------------
// Basic set. Feel free to add more tags
// ----------------------------------------------------------------------------
mySettings = {
	onShiftEnter:	{keepDefault:false, replaceWith:'<br />\n'},
	onCtrlEnter:	{keepDefault:false, openWith:'\n<p>', closeWith:'</p>\n'},
	//onTab:			{keepDefault:false, openWith:'	 '},
	markupSet: [
	  {group_start: true},          
		{name:'P', key:'P', openWith:'<p(!( class="[![Class]!]")!)>', closeWith:'</p>', tooltip:'Parágrafo'},
		{name:'Negrito', key:'B', openWith:'(!(<strong>|!|<b>)!)', closeWith:'(!(</strong>|!|</b>)!)', tooltip:'Negrito'},
		{name:'Itálico', key:'I', openWith:'(!(<em>|!|<i>)!)', closeWith:'(!(</em>|!|</i>)!)', tooltip:'Itálico'},
		{group_start: true}, 
		{name:'', className:'clean', tooltip:'Limpar', icon:'icon-remove', replaceWith:function(markitup) { return markitup.selection.replace(/<(.*?)>/g, "") } }		
		//{name:'Preview', className:'preview', call:'preview', tooltip:'Itálico' }
	]
}
$(document).ready(function() {
  // markitup
  $('.html-input').markItUp(mySettings);
});