{include file='page_header.tpl'}

<script type="text/javascript">
<!--
	document.write("<h2>Inloggen<\/h2>");
-->
</script>

<noscript>
<div class="errorbox">
	Uw browser ondersteunt geen JavaScript of JavaScript is uitgeschakeld. Schakel JavaScript in of installeer 
	<a href="http://www.mozilla.com">Mozilla Firefox</a> of een andere moderne browser en probeer het opnieuw.
</div>
</noscript>

{if $login_failed}
<div class="errorbox">
	U heeft een verkeerde gebruikersnaam, klantnummer of wachtwoord ingevoerd!
</div>
{/if}

<script type="text/javascript">
<!--
	document.write("<p>Voer uw klantnummer of gebruikersnaam in met het bijhorende wachtwoord.<\/p>");

	document.write("<form action=\"{$base}/sessie/inloggen\" method=\"post\" name=\"login\">");
	document.write("<table><tr><td>Gebruikersnaam:<\/td><td>");

	document.write("<input id=\"gebruikersnaam\" name=\"gebruikersnaam\" class=\"login\" type=\"text\" value=\"{$gebruikersnaam}\" />");
			{if $fouten.gebruikersnaam}
			document.write("<div class=\"errortext\">{$fouten.gebruikersnaam}</div>");
			{/if}
	document.write("<\/td><\/tr><tr><td>Wachtwoord:<\/td><td>");
	document.write("<input id=\"wachtwoord\" name=\"wachtwoord\" class=\"login\" type=\"password\" value=\"{$wachtwoord}\" />");

			{if $fouten.wachtwoord}
			document.write("<div class=\"errortext\">{$fouten.wachtwoord}<\/div>");
			{/if}
	document.write("<\/td><\/tr><tr><td colspan=\"2\">&nbsp;<\/td><\/tr>");
	document.write("<tr><td>&nbsp;<\/td>");
	document.write("<td><input name=\"login\" type=\"submit\" class=\"login\" value=\"Log in\" /><\/td>");
	document.write("<\/tr><\/table><\/form> ");
	
	document.write("<p>Problemen met inloggen? Neem contact op met de administratie van <a href=\"mailto:{$company_email}\">{$company_name}<\/a>.<\/p>");
-->
</script>

{include file='page_footer.tpl'}
