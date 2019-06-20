<?xml version="1.0" encoding="UTF-8"?>
{if $success eq 1}
<removed state="{$success}" ref="{$volgnummer}" />
{else}
<removed state="{$success}" />
{/if}