<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
		<title>{$company_name} | {$application_abbr}</title>
		<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
		<link rel="stylesheet" type="text/css" media="screen" title="Default style" href="{$base}/styles/{$default_stylesheet}.css" />
		<link rel="stylesheet" type="text/css" media="screen" href="{$base}/styles/calendar.css" />
		<link rel="stylesheet" type="text/css" media="print" href="{$base}/styles/{$default_stylesheet}-print.css" />
		<link rel="stylesheet" type="text/css" href="{$base}/styles/autocomplete.css" />

		<script src="{$base}/scripts/scriptaculous/prototype.js" type="text/javascript"></script>
		<script src="{$base}/scripts/scriptaculous/scriptaculous.js" type="text/javascript"></script>

		<script type="text/javascript">
			var baseURL = "{$base}";
		</script>

		<script src="{$base}/scripts/shared/calendar.js" type="text/javascript"></script>
	</head>
	<body>
		
		<h1><b>{$application_name}</b></h1>

		<div class="menubar">

			<a href="{$base}/">{if $use_icons}<img src="{$base}/images/icons/home.gif" height="48" alt="Hoofdpagina" />{else}Hoofdpagina{/if}</a>

{if $user_type neq ''}
			{if $use_icons}<img src="{$base}/images/black.png" height="48" width="1" alt="" />{else}&nbsp;|&nbsp;{/if}
{if $user_type == 'Directie'}
			<a href="{$base}/klant">{if $use_icons}<img src="{$base}/images/icons/klanten.gif" alt="Klanten" />{else}Klanten{/if}</a>
			{if !$use_icons}&nbsp;{/if}

{/if}
			<a href="{$base}/factuur">{if $use_icons}<img src="{$base}/images/icons/facturen.gif" alt="Facturen" />{else}Facturen{/if}</a>			

			{if $use_icons}<img src="{$base}/images/black.png" height="48" width="1" alt="" />{else}&nbsp;|&nbsp;{/if}

{if $user_type == 'Directie'}
<!--			<a href="{$base}/leverancier">
				{if $use_icons}
					<img src="{$base}/images/icons/leveranciers.gif" alt="Leveranciers" />
				{else}Leveranciers{/if}</a>

			{if !$use_icons}&nbsp;{/if}

			<a href="{$base}/inkoop">{if $use_icons}<img src="{$base}/images/icons/inkopen.png" alt="Inkopen" />{else}Inkopen{/if}</a>
			
			{if $use_icons}<img src="{$base}/images/black.png" height="48" width="1" alt="" />{else}&nbsp;|&nbsp;{/if}
			
			<a href="{$base}/urenregistratie">
				{if $use_icons}
					<img src="{$base}/images/icons/uren.png" alt="Urenregistratie">
				{else}Urenregistratie{/if}</a>

			<img src="{$base}/images/black.png" height="48" width="1" />-->
{/if}


{if $user_type == 'Directie'}
			<a href="{$base}/gebruiker">{if $use_icons}<img src="{$base}/images/icons/gebruikers2.gif" alt="Gebruikers" />{else}Gebruikers{/if}</a>

		{if !$use_icons}&nbsp;{/if}
{/if}
			<a href="{$base}/gebruiker/wachtwoord_wijzigen">
				{if $use_icons}
					<img src="{$base}/images/icons/wachtwoord.gif" alt="Wachtwoord wijzigen" />
				{else}Wachtwoord wijzigen{/if}</a>

			{if !$use_icons}&nbsp;{/if}

			<a href="{$base}/sessie/uitloggen">
				{if $use_icons}
					<img src="{$base}/images/icons/disconnect.gif" alt="Afmelden" />
				{else}Afmelden{/if}</a>
{/if}
		</div>

		<div class="content">
			<div class="content-middle">