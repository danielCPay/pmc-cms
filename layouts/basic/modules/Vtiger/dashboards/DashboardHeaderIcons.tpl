{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
* Contributor(s): YetiForce.com
********************************************************************************/
-->*}
{strip}
	{if isset($SETTING_EXIST)}
		<a class="btn btn-sm btn-light" name="dfilter">
			<span class='icon-cog' title="{\App\Language::translate('LBL_FILTER')}"></span>
		</a>
		&nbsp;
	{/if}
	{assign var=WIDGET_DATA value=\App\Json::decode($WIDGET->get('data'))}
	{if $WIDGET_DATA['module'] == 'ClaimOpportunities'}
		<a class="btn btn-sm btn-light" href="javascript:void(0);" name="dassign"
			data-url="index.php?module=ClaimOpportunities&action=AssignFromPool">
			<span class="fas fa-folder-plus" title="{\App\Language::translate('LBL_ASSIGN')}"></span>
		</a>
		&nbsp;
	{/if}
	<a class="btn btn-sm btn-light" href="javascript:void(0);" name="drefresh"
	   data-url="{$WIDGET->getUrl()}&content=data">
		<span class="fas fa-sync-alt" title="{\App\Language::translate('LBL_REFRESH')}"></span>
	</a>
	{if !$WIDGET->isDefault()}
		<a class="js-widget-remove btn btn-sm btn-light" data-js="click | bootbox" data-url="{$WIDGET->getDeleteUrl()}">
			<span class="fas fa-times" title="{\App\Language::translate('LBL_CLOSE')}"></span>
		</a>
	{/if}
{/strip}
