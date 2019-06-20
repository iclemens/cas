<ul>
{foreach from=$artikelcodes item=artikelcode}
	<li>{$artikelcode.artikelcode|escape:HTML}<span class="informal"> {$artikelcode.omschrijving|escape:HTML}</span></li>
{/foreach}
</ul>