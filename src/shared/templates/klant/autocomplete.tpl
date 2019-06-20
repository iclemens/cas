<ul>
{foreach from=$klanten item=klant}
	<li>{$klant.klantnummer|escape:HTML}<span class="informal"> {klantnaam klant=$klant}</span></li>
{/foreach}
</ul>