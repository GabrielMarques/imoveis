<?php

/**
 * Cron_jobs Class
 *
 * @package
 * @subpackage
 * @category
 * @author Gabriel Marques
 * @link
 */

class Cron_jobs extends CI_Controller{

	public function __construct(){
		parent::__construct();
	}

	public function get_apartments(){
		//if($this->input->is_cli_request()){

			log_message('info', 'Apartamentos atualizados');
		//}
	}
/*
<div id="ctl00_ContentPlaceHolder1_rpOfertasSD_ctl00_imvSD_pnDivPrincipal" class="item itemSD">
<input type="hidden" id="hidComparar" value="100279|15311">
<div id="ctl00_ContentPlaceHolder1_rpOfertasSD_ctl00_imvSD_divComparar" class="comparar-flag" style="display: none; ">
<input type="CheckBox" class="ckbClsComparar" id="chkImovel2639363" onclick="verificarSelecionados('checkbox', this.id, 'ctl00_ContentPlaceHolder1_rpOfertasSD_ctl00_imvSD_divComparar'); Check(this.id,'100279','15311');">
</div>
<div class="full"><a id="ctl00_ContentPlaceHolder1_rpOfertasSD_ctl00_imvSD_lnkFull" href="http://www.zap.com.br/imoveis/SuperDestaque/Apartamento-Padrao-1-quartos-venda-RIO-DE-JANEIRO-COPACABANA-RUA-BARATA-RIBEIRO/ID-2639363"></a></div>
<div class="itemFoto">
<div>
<a href="http://www.zap.com.br/imoveis/SuperDestaque/Apartamento-Padrao-1-quartos-venda-RIO-DE-JANEIRO-COPACABANA-RUA-BARATA-RIBEIRO/ID-2639363" id="ctl00_ContentPlaceHolder1_rpOfertasSD_ctl00_imvSD_linkFotoPrincipal"><img src="http://img.zapcorp.com.br/201111/25/EXT/Imoveis/1822804/100279/img_2511201194736_gedc2413.jpg" id="ctl00_ContentPlaceHolder1_rpOfertasSD_ctl00_imvSD_imgFotoPrincipal"></a>
</div>
<div class="media">
<p class="mediaFoto"><a id="ctl00_ContentPlaceHolder1_rpOfertasSD_ctl00_imvSD_lnkQtdFotoImovel" href="javascript:window.location.href='http://www.zap.com.br/imoveis/SuperDestaque/Apartamento-Padrao-1-quartos-venda-RIO-DE-JANEIRO-COPACABANA-RUA-BARATA-RIBEIRO/ID-2639363';">17 fotos</a></p>
</div>
</div>
<h3>
<a href="http://www.zap.com.br/imoveis/SuperDestaque/Apartamento-Padrao-1-quartos-venda-RIO-DE-JANEIRO-COPACABANA-RUA-BARATA-RIBEIRO/ID-2639363">COPACABANA</a>
-
<a href="http://www.zap.com.br/imoveis/SuperDestaque/Apartamento-Padrao-1-quartos-venda-RIO-DE-JANEIRO-COPACABANA-RUA-BARATA-RIBEIRO/ID-2639363">RIO DE JANEIRO</a>/<a href="http://www.zap.com.br/imoveis/SuperDestaque/Apartamento-Padrao-1-quartos-venda-RIO-DE-JANEIRO-COPACABANA-RUA-BARATA-RIBEIRO/ID-2639363">RJ</a>
<span class="End">
<a href="http://www.zap.com.br/imoveis/SuperDestaque/Apartamento-Padrao-1-quartos-venda-RIO-DE-JANEIRO-COPACABANA-RUA-BARATA-RIBEIRO/ID-2639363">RUA BARATA RIBEIRO</a>
</span>
</h3>
<div class="pin">
<img src="http://c.zapcorp.com.br/img/imoveis/pin/A.png" id="ctl00_ContentPlaceHolder1_rpOfertasSD_ctl00_imvSD_imgPin" title="Confira a localização desta oferta na mapa!">
</div>
<div class="itemValor">
<a class="valorOferta" href="http://www.zap.com.br/imoveis/SuperDestaque/Apartamento-Padrao-1-quartos-venda-RIO-DE-JANEIRO-COPACABANA-RUA-BARATA-RIBEIRO/ID-2639363"><sup>R$ </sup>600.000</a><br>
<div id="ctl00_ContentPlaceHolder1_rpOfertasSD_ctl00_imvSD_pnlSimulador">
<a class="labelGeneral simularItem">simular financiamento</a>
</div>
</div>
<div class="itemLogo">
<a href="http://www.zap.com.br/imoveis/superdestaque/apartamento-padrao-1-quartos-venda-rio-de-janeiro-copacabana-rua-barata-ribeiro/id-2639363" class="logo-borda"><img src="http://img.zapcorp.com.br/201108/01/ext/imoveis/1822804/img_205_1822804_logo.jpg" alt="lowndes"></a>
</div>
<a href="http://www.zap.com.br/imoveis/SuperDestaque/Apartamento-Padrao-1-quartos-venda-RIO-DE-JANEIRO-COPACABANA-RUA-BARATA-RIBEIRO/ID-2639363" class="itemCaracteristicas">
<p><span class="labelCar">Área</span> 57m²</p><p><span class="labelCar">Dorms</span> 1</p></a>
<div class="itemData">
<span class="labelGeneral">
data de publicação: 22/01/2012</span>
</div>
<div class="itemAction otherAction" style="display: none; ">
<a id="ctl00_ContentPlaceHolder1_rpOfertasSD_ctl00_imvSD_btnDetalhes" href="http://www.zap.com.br/imoveis/SuperDestaque/Apartamento-Padrao-1-quartos-venda-RIO-DE-JANEIRO-COPACABANA-RUA-BARATA-RIBEIRO/ID-2639363"><span class="verItem"></span>Ver Detalhes</a>
<a id="ctl00_ContentPlaceHolder1_rpOfertasSD_ctl00_imvSD_btnContato" href="http://www.zap.com.br/imoveis/SuperDestaque/Apartamento-Padrao-1-quartos-venda-RIO-DE-JANEIRO-COPACABANA-RUA-BARATA-RIBEIRO/ID-2639363"><span class="contatarItem"></span>Contate o Anunciante</a>
<span class="salvarItem2"><a id="ctl00_ContentPlaceHolder1_rpOfertasSD_ctl00_imvSD_lnkSalvar" title="Salvar anúncio" onclick="AtualizaHiddenSalvarAnuncio('|100279*15311|',0,'ctl00_ContentPlaceHolder1_hidChecked','','');"><span class="salvarItem"></span>Salvar</a></span>
</div>
</div>
*/



}
/* End of file cron_jobs.php */
/* Location: ./application/controllers/cron_jobs.php */