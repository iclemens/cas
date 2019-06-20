<!-- Start of iDEAL Module -->
<h3>iDEAL</h3>

<p><b>Let op:</b> Nadat de transactie
voltooid is duurt het wellicht nog enkele dagen voordat hij daadwerkelijk in het
administratie systeem is afgeboekt.</p>

<form method="post" action="{$idealPost}" id="ideal" name="ideal">

	<input type="hidden" name="merchantID" value="{$merchantID}">
	<input type="hidden" name="subID" value="{$subID}">
	<input type="hidden" name="amount" value="{$amount}">
	<input type="hidden" name="purchaseID" value="{$purchaseID}">
	<input type="hidden" name="language" value="nl">
	<input type="hidden" name="currency" value="EUR">
	<input type="hidden" name="description" value="{$description}">
	<input type="hidden" name="hash" value="{$hash}">
	<input type="hidden" name="paymentType" value="{$paymentType}">
	<input type="hidden" name="validUntil" value="{$validUntil}">

	<input type="hidden" name="itemNumber1" value="{$itemNumber}">
	<input type="hidden" name="itemDescription1" value="{$itemDescription}">
	<input type="hidden" name="itemQuantity1" value="{$itemQuantity}">
	<input type="hidden" name="itemPrice1" value="{$itemPrice}">
<!--	
	<input type="hidden" name="urlCancel" value="{$base}/betaling/ideal_status/status/2">
	<input type="hidden" name="urlSuccess" value="{$base}/betaling/ideal_status/status/1">
	<input type="hidden" name="urlError" value="{$base}/betaling/ideal_status/status/2">
-->
	<input name="Submit" type="submit" value="Betaal met iDEAL">

</form>
<!-- End of iDEAL module -->