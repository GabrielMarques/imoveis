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
		{name:'H1', key:'1', openWith:'<h1(!( class="[![Class]!]")!)>', closeWith:'</h1>', placeHolder:'O seu título aqui...', tooltip:'Cabeçalho 1'},
		{name:'H2', key:'2', openWith:'<h2(!( class="[![Class]!]")!)>', closeWith:'</h2>', placeHolder:'O seu título aqui...', tooltip:'Cabeçalho 2'},
		{name:'H3', key:'3', openWith:'<h3(!( class="[![Class]!]")!)>', closeWith:'</h3>', placeHolder:'O seu título aqui...', tooltip:'Cabeçalho 3'},
		{name:'H4', key:'4', openWith:'<h4(!( class="[![Class]!]")!)>', closeWith:'</h4>', placeHolder:'O seu título aqui...', tooltip:'Cabeçalho 4'},
		//{name:'H5', key:'5', openWith:'<h5(!( class="[![Class]!]")!)>', closeWith:'</h5>', placeHolder:'O seu título aqui...', tooltip:'Cabeçalho 5'},
		//{name:'H6', key:'6', openWith:'<h6(!( class="[![Class]!]")!)>', closeWith:'</h6>', placeHolder:'O seu título aqui...', tooltip:'Cabeçalho 6'},
		{name:'P', openWith:'<p(!( class="[![Class]!]")!)>', closeWith:'</p>', tooltip:'Parágrafo'},
		{group_start: true},
		{name:'', key:'B', openWith:'(!(<strong>|!|<b>)!)', closeWith:'(!(</strong>|!|</b>)!)', tooltip:'Negrito', icon:'icon-bold'},
		{name:'', key:'I', openWith:'(!(<em>|!|<i>)!)', closeWith:'(!(</em>|!|</i>)!)', tooltip:'Itálico', icon:'icon-italic'},
		{name:'', key:'S', openWith:'<del>', closeWith:'</del>', tooltip:'Tachado', icon:' icon-minus'},
		{group_start: true},
		{name:'', openWith:'<ul>\n', closeWith:'</ul>\n', tooltip:'Lista', icon:'icon-align-justify'},
		{name:'', openWith:'<ol>\n', closeWith:'</ol>\n', tooltip:'Lista ordenada', icon:'icon-list'},
		{name:'', openWith:'<li>', closeWith:'</li>', tooltip:'Íten da lista', icon:'icon-plus'},
		{group_start: true},
		{name:'', key:'P', replaceWith:'<img src="[![Fonte:!:http://]!]" alt="[![Texto alternativo]!]" />', tooltip:'Imagem', icon:'icon-picture'},
		{name:'', key:'L', openWith:'<a href="[![URL:!:http://]!]"(!( title="[![Título]!]")!)>', closeWith:'</a>', placeHolder:'O seu texto aqui...', tooltip:'Link', icon:'icon-share-alt'},
		{group_start: true},
		{name:'', className:'clean', tooltip:'Limpar', icon:'icon-remove', replaceWith:function(markitup) { return markitup.selection.replace(/<(.*?)>/g, "") } }
		//{name:'Preview', className:'preview', call:'preview' }
	]
}

$(document).ready(function() {
  // markitup
  $('.html-input').markItUp(mySettings);
});