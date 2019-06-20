{include file='page_header.tpl'}

<h2>Voorbeeld factuur</h2>

<pre style="border: 1px #000000 solid" width="80">{$mail_body}</pre>

<br />

<form name="herzien" method="post" action="{$base}/factuur/voorbeeld_pdf">
{include file='factuur/_hidden.tpl'}
<input type="submit" value="Bekijk PDF" />
</form>

<br />

<table>
	<tr>
		<td>
<form name="herzien" method="post" action="{$base}/factuur/herzien">
{include file='factuur/_hidden.tpl'}
<input type="submit" value="Bewerken" />
</form>

		</td>
		<td>

<form name="maak" method="post" action="{$base}/factuur/maak">
{include file='factuur/_hidden.tpl'}
<input type="submit" value="Maak factuur" />
</form>

		</td>
		<td valign="top">

<input type="button" value="Annuleren" onClick="document.location = '{$base}/index'" />

		</td>
	</tr>
</table>

{include file='page_footer.tpl'}
