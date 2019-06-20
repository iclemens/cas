<?php
	class CT_Smarty_Paginate
	{
		static function registerPlugin(&$smarty)
		{
			$smarty->register_block('paged_table', 'CT_Smarty_Paginate::smartyPagedTable');
			$smarty->register_block('table_header', 'CT_Smarty_Paginate::smartyTableHeader');
			$smarty->register_function('sortable_column', 'CT_Smarty_Paginate::smartySortableColumn');
			$smarty->register_function('filter_params', 'CT_Smarty_Paginate::smartyFilterParams');
		}

		static function smartyFilterParams($params, &$smarty)
		{
			$out = '';

			if(!array_key_exists('params', $params))
				return $out;
				
			if(!is_array($params['params']))
				return $out;

			foreach($params['params'] as $key=>$value) {
				$urlPart = '';
				
				if(is_array($value)) {
					foreach($value as $val) {
						if($urlPart == '')
							$urlPart = urlencode($key) . '[]=' . urlencode($val);
						else
							$urlPart .= '&amp;' . urlencode($key) . '[]=' . urlencode($val);
					}
				} else {
					$urlPart = urlencode($key) . '=' . urlencode($value); 
				}
				
				if($out == '') {
					$out = $urlPart;
				} else {
					$out .= '&amp;' . $urlPart;
				}
			}
			
			return $out;
		}

		/**
		 * Smarty wrapper for paged and sortable tables.
		 */
		static function smartyPagedTable($params, $content, &$smarty, &$repeat)
		{
			if(!$repeat) {
				// FIXME: These variables are global!
				$smarty->assign('pager', $params['pager']);
				$smarty->assign('filter', $params['filter']);
				
				$pager = $smarty->fetch('pager.tpl');

				$html = "<div>\n";
				$html .= "<table class=\"generic_table\">\n";
				$html .= $content;
				$html .= "</table>\n";
				$html .= "<p>" . $pager . "</p>\n";
				$html .= "</div>\n";
				return $html;
			}
		}
		
		/**
		* Smarty wrapper for table header, containing (sortable) columns.
		*/
		static function smartyTableHeader($params, $content, &$smarty, &$repeat)
		{
			if(!$repeat) {                                                        
				return "<thead><tr>\n" . $content . "</tr></thead>";
			}
		}
		
		/**
		* Smarty wrapper for sortable columns, should be enclosed in a both a
		* pages-table and header block which has the 'sorter' variable set.
		* Note that this helper only provides the table columns, the user
		* should make sure the respective row is also present.
		*/
		static function smartySortableColumn($params, &$smarty)
		{
			foreach($smarty->_tag_stack as &$tag) {
				if($tag[0] == 'paged_table') {
					$tag_params = &$tag[1];
					
					if(array_key_exists('sorter', $tag_params))
						$sorter = &$tag_params['sorter'];
					if(array_key_exists('filter', $tag_params))
						$filter = &$tag_params['filter'];
				}
			}
			
			if(isset($sorter)) {
				$sortString = $sorter->getStringWith($params['name']);
			} else {
				$sortString = '';
			}
			
			if(isset($filter)) {
				$filterString = self::smartyFilterParams(array('params' => $filter), $smarty);
			} else {
				$filterString = '';
			}

			return "<th><a href=\"?sort=" . 
				$sortString . "&amp;" . $filterString . "\">" .
				$params['value'] . "</a></th>";
		}
	
	};
	
	/*
	{paged_table pager=$pager sorter=$sorter}
		{header}
			{sortable_column name="klantnr" value="Klantnummer"}
			{sortable_column name="bedrijfsnaam" value="Bedrijfsnaam"}
			{sortable_column name="achternaam" value="Contact pers."}
			
			<th class="noprint">Bewerken</th>
		{/header}
	
		{foreach from=$klanten item=klant}
			<tr {if $klant.actief eq 0}class="inactive"{/if}>
				<td onclick="document.location='{$base}/klant/bewerk/id/{$klant.klantnummer'">
					{$klant.klantnummer|escape:'html'}
				</td>
				<td>{$klant.bedrijfsnaam|escape:'html'}</td>
				<td>{$klant.aanhef|escape:'html'} {$klant.voornaam|escape:'html'} {$klant.achternaam|escape:'html'}</td>			
				<td class="noprint">
					<a href="{$base}/klant/bewerk/id/{$klant.klantnummer}">Bewerken</a>
				</td>
			</tr>
		{/foreach}
	
	{/paged_table}
	*/