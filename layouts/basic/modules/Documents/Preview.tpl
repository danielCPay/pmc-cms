{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
*
********************************************************************************/
-->*}
{strip}
	<div class="mainContainer container">
		<div class="jumbotron mt-5">
			<div class="row">
				<div class="col-md-12 text-center">
					<h1><span class="yfm-Documents u-fs-10x"></span></h1>
					<h2>{\App\Language::translate('LBL_NO_PREVIEW_AVAILABLE', $MODULE_NAME)}</h2>
					<p class="my-5">
						<a class="btn btn-primary mr-2" role="button" href="{$RECORD_MODEL->getDownloadFileURL()}">
							<span class="fas fa-download mr-2"></span>{\App\Language::translate('LBL_DOWNLOAD_FILE', $MODULE_NAME)}
						</a>
						{if $IS_EDITABLE === true}
						<a class="btn btn-warning mr-2" role="button" href="{$EDIT_URL}">
							<span class="fas fa-edit fa-fw mr-2"></span>{\App\Language::translate('LBL_EDIT_FILE', $MODULE_NAME)}
						</a>
						{/if}
					</p>
				</div>
			</div>
		</div>
	</div>
{/strip}
