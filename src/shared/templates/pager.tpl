<span class="noprint">
	{if $pager->hasPrevious()}
	<a href="?page={$pager->pageNumber()-1}&amp;sort={$sorter->getString()}&amp;{filter_params params=$filter}">&lt;&lt; Vorige pagina</a>
	&nbsp; | &nbsp; 
	{/if}
</span>

Pagina <b>{$pager->pageNumber()}</b> van <b>{$pager->numberOfPages()}</b>

<span class="noprint">
	{if $pager->hasNext()}
	&nbsp; | &nbsp;
	<a href="?page={$pager->pageNumber()+1}&amp;sort={$sorter->getString()}&amp;{filter_params params=$filter}">Volgende pagina &gt;&gt;</a>
	{/if}
</span>