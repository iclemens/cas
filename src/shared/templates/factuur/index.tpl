{include file='page_header.tpl'}

<h2>Facturatie</h2>

<p>Op deze pagina kunt u alle facturen van en vanaf december 2007 opvragen. Verder kunt u openstaande facturen voldoen middels de online betaalmethode iDEAL.</p>

{if $user_type == 'Directie'}
<ul class="menu">
	<li><a href="{$base}/factuur/nieuw">Nieuwe factuur maken</a></li>
	<li><a href="{$base}/factureren/nieuw">Nieuwe items in de wachtrij plaatsen</a></li>
	<li><a href="{$base}/periodiek/nieuw">Nieuwe periodieke factuur maken</a></li>
</ul>

<h2>Overzichten</h2>
{/if}

<ul class="menu">
	<li><a href="{$base}/factuur/zoek">Bestaande factuur opvragen</a></li>
	<li><a href="{$base}/factuur/openstaand">Openstaande facturen opvragen</a></li>
	<li><a href="{$base}/factuur/incasso">Openstaande incassos opvragen</a></li>

{if $user_type == 'Directie'}
	<li>&nbsp;</li>
	<li><a href="{$base}/factureren/lijst">Lijst met te factureren items ophalen</a></li>
	<li><a href="{$base}/periodiek/lijst">Lijst met periodieke facturen ophalen</a></li>
{/if}
</ul>

{include file='page_footer.tpl'}
