<?php
	/**
	 * Writes an HTML header to standard output.
	 */
	function pageHeader()
	{
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
		<title>Project CAS Installatie</title>
		<link rel="stylesheet" type="text/css" media="screen" title="Default style" href="../styles/blue.css" />
	</head>
	<body>

		<h1><b>Project CAS Installatie</b></h1>
		
		<div class="content">
		<div class="content-middle">
<?php
	}


	/**
	 * Writes an HTML footer to standard output
	 */
	function pageFooter()
	{
?>
			</div>
		</div>
	</body>
</html>
<?php
	}


	function pageDone()
	{
		pageHeader();
		
?><h2>Installatie voltooid</h2>

<p>De installatie van CAS is voltooid! Het is nu mogelijk om <a href="/">in te loggen</a>, vergeet niet het wachtwoord (toor) van de standaard gebruiker (root) te veranderen.</p>

<?php	
		pageFooter();
	}


	function pagePartialSQL()
	{
		pageHeader();
		
?><h2>Oude versie gedetecteerd</h2>

<p>Er zijn oude gegevens gedetecteerd in de database. Maak een backup en werk de database
handmatig bij door de bestanden in de /sql directory uit te voeren.</p>

<?php	
		pageFooter();
	}


	function pageAdminPassword($config, $errorMessage)
	{
		pageHeader();

		?><h2>Database installatie</h2><?php

		if(strlen($errorMessage) > 0) {
			echo('<div class="errorbox">' . $errorMessage . '</div>');
		}		
?>

<p>Voordat u CAS kunt gebruiken moet de database worden aangemaakt, dit kan op twee 
manieren: handmatig en automatisch. Om de database automatisch te installeren hoeft
u alleen de gebruikersnaam en het wachtwoord van de database beheerder in te vullen en
op de <i>maak database</i> knop te drukken. Wees u er van bewust dat het wachtwoord 
mogelijk <b>onbeveiligd</b> wordt verstuurd! De handmatige producedure staat beschreven
in het readme.html bestand.</p>

<p>De volgende handelingen zullen worden uitgevoerd op server: "<b><?=$config->database->host?></b>":
<ul>
<li>Aanmaken van nieuwe database genaamd "<?=$config->database->username?>".</li>
<li>Aanmaken van nieuwe gebruiker genaamd "<?=$config->database->name?>".</li>
<li>Toekennen van rechten aan nieuwe gebruiker.</li>
</ul>
</p>

<br /><hr /><br /><br />

<form method="post" action="setup.php">
<input type="hidden" name="setup_step" value="3" />

Gebruikersnaam (database beheerder):<br />
<input type="text" name="admin_username" /><br />
<br />
Wachtwoord (database beheerder):<br />
<input type="password" name="admin_password" /><br />
<br />

<input type="submit" value="Maak database" />

</form>

<?php

		pageFooter();
	}


	function pageCreatorPassword($config, $errorMessage)
	{
		pageHeader();

		?><h2>Database initialiseren</h2><?php

		if(strlen($errorMessage) > 0) {
			echo('<div class="errorbox">' . $errorMessage . '</div>');
		}		
?>

<p>Voordat u CAS kunt gebruiken moeten tabellen in de database worden aangemaakt. Dit kan op twee 
manieren: handmatig en automatisch. Om de database automatisch te vullen hoeft
u alleen de gebruikersnaam en het wachtwoord van een gebruiker met CREATE rechten (e.g. de beheerder) 
in te vullen en op de <i>maak tabellen</i> knop te drukken. Wees u er van bewust dat het wachtwoord 
mogelijk <b>onbeveiligd</b> wordt verstuurd! De handmatige producedure staat beschreven
in het readme.html bestand.</p>

<p>De volgende handelingen zullen worden uitgevoerd op server: "<b><?=$config->database->host?></b>" in database: <b><?=$config->database->name?></b>:
<ul>
<li>Aanmaken van nieuwe tabellen.</li>
</ul>
</p>

<br /><hr /><br /><br />

<form method="post" action="setup.php">
<input type="hidden" name="setup_step" value="4" />

Gebruikersnaam:<br />
<input type="text" name="creator_username" /><br />
<br />
Wachtwoord:<br />
<input type="password" name="creator_password" /><br />
<br />

<input type="submit" value="Maak tabellen" />

</form>

<?php

		pageFooter();
	}


	function pageConfigureXML($fieldData, $template, $errorMessage)
	{
		pageHeader();

		?><h2>Configureer CAS</h2><?php
		
	/*if(strlen($infoMessage) > 0) {
		echo('<div class="infobox">' . $infoMessage . '</div>');
	}*/

	if(strlen($errorMessage) > 0) {
		echo('<div class="errorbox">' . $errorMessage . '</div>');
	}
?>

		<form method="post" action="setup.php">
		<input type="hidden" name="setup_step" value="2" />
				
		<table>
		<tr><td>
		
		<table><tr><td valign="top">
<?php
		$numFields = 0;

		foreach($template as $title => $fields) {
			
			if($title == '')
				$title = 'Default';
			
			$blockHeader = '<tr><td colspan="2">';
			$blockHeader .= '<b>' . fieldNameToLabel($title) . '</b>';
			$blockHeader .= '</td></tr>';

			$blockData = '';
			
			foreach($fields as $fieldName => $fieldOptions) {
				if($fieldOptions['advanced'])
					continue;
					
				if($title == 'Default')
					$fullName = $fieldName;
				else
					$fullName = $title . '+' . $fieldName;
					
				$blockData .= '<tr><td>';
				$blockData .= fieldNameToLabel($fieldName) . ':';
				$blockData .= '</td><td>';
				$blockData .= formField($fullName, $fieldOptions);
				$blockData .= '</td></tr>';
				
				$numFields++;
			}
			
			if($blockData != '') {
				echo($blockHeader . $blockData);
				echo('<tr><td colspan="2">&nbsp;</td></tr>');
			}
			
			if($numFields >= 10) {
				$numFields = 0;
				echo('</td></tr></table></td><td valign="top" style="padding-left: 40px"><table><tr><td>');
			}
		}
?>
		</td></tr>
		</table>

		</td></tr>
		</table>
		
		<input type="submit" value="Configuratie opslaan" />
		
		</form>
<?php
		
		pageFooter();
	}
	
	
	function pageChooseInstall($errorMessage)
	{
		pageHeader();
?>
		<h2>Selecteer te gebruiken installatie</h2>

<?php
	if(strlen($errorMessage) > 0) {
		echo('<div class="errorbox">' . $errorMessage . '</div>');
	}
?>

		<p>
		Het is mogelijk dezelfde CAS installatie voor verschillende bedrijven te gebruiken. Omdat bedrijfsgegevens zoals factureren niet gedeeld kunnen worden is CAS in twee delen gesplitst, het globale en het lokale deel. In deze stap dient u de lokaties van deze beide delen aan te geven.
		</p>

		<p>
		Indien u het pad handmatig opgeeft, let er dan op dat u een absoluut pad (begint met een /) op de webserver invoert. Daarnaast zijn schrijfrechten in bepaalde delen van de lokale directory noodzakelijk.
		</p>

		<form method="post" action="setup.php">
		<input type="hidden" name="setup_step" value="1" />
			<p>
				<b>Kies de globale (i.e. gedeelde) CAS-installatie die u wilt gebruiken:</b>
			</p>

<?php
	foreach(suggestSharedLocation() as $location) {

	    $versionInfo = getCASVersion($location);
?>
		<input type="radio" name="globalOpt" value="<?php echo($location)?>" 
			<?php if($_POST['globalOpt'] == $location) { echo('checked="checked"'); }?> />
			<?php echo(versionString($versionInfo))?>, <i><?php echo($location)?></i><br />
<?php 
	} 
?>
			<input type="radio" name="globalOpt" id="globalCustom" value="custom" 
				<?php if($_POST['globalOpt'] == 'custom') { echo('checked="checked"'); }?> /> 
				Anders, namelijk: <input type="text" size="40" name="globalPath" 
					onkeypress="getElementById('globalCustom').checked = true;" 
					value="<?=$_POST['globalPath']?>" />


			<p>
				<b>Kies de lokale CAS-installatie die u wilt gebruiken:</b>
			</p>

<?php
	foreach(suggestLocalLocation() as $location) { 

	$versionInfo = getCASVersion($location);
?>
	<input type="radio" name="localOpt" value="<?php echo($location)?>" 
		<?php if($_POST['localOpt'] == $location) { echo('checked="checked"'); }?> />
		<?php echo(versionString($versionInfo))?>, <i><?php echo($location)?></i><br />
<?php 
	}
?>
			<input type="radio" name="localOpt" id="localCustom" value="custom" 
				<?php if($_POST['localOpt'] == 'custom') { echo('checked="checked"'); }?> /> 
				Anders, namelijk: 
				<input type="text" size="40" name="localPath" 
					onkeypress="getElementById('localCustom').checked = true;" 
					value="<?=$_POST['localPath']?>" />

			<br />
			<br />

			<input type="submit" value="Opslaan" />

		</form>
<?php		
		pageFooter();
	}