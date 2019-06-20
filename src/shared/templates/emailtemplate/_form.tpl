
Omschrijving:<br />
{html_textbox field=omschrijving value=$emailtemplate.omschrijving size="50" errors=$errors}

<br />
<br />

Onderwerp:<br />
{html_textbox field=onderwerp value=$emailtemplate.onderwerp size="50" errors=$errors}

<br />
<br />

Inhoud:<br />
<textarea name="inhoud" cols="70" rows="15">{$emailtemplate.inhoud|escape:HTML}</textarea>
<br />