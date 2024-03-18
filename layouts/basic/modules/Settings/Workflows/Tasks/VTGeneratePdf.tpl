{strip}
	{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
	<div id="VtVTGeneratePDFTaskContainer">
		<div class="row">
			<span class="col-md-4 col-form-label text-right">{\App\Language::translate('LBL_PDF_TEMPLATE', $QUALIFIED_MODULE)}</span>
			<div class="col-md-4 pb-3">
				<select class="select2 form-control" name="pdfTemplate" data-validation-engine="validate[required]"
						data-placeholder="{\App\Language::translate('LBL_SELECT_FIELD',$MODULE)}"
						data-select="allowClear">
					<optgroup class="p-0">
						<option value="none">{\App\Language::translate('LBL_SELECT_FIELD',$MODULE)}</option>
					</optgroup>
					{if isset($TASK_OBJECT->pdfTemplate)}
						{if \is_numeric($TASK_OBJECT->pdfTemplate)}
							{assign var=SELECTED_TEMPLATE value=Vtiger_PDF_Model::getNameById($TASK_OBJECT->pdfTemplate)}
						{else}
							{assign var=SELECTED_TEMPLATE value=$TASK_OBJECT->pdfTemplate}
						{/if}
					{/if}
					{foreach from=Vtiger_PDF_Model::getTemplateNamesByModule($SOURCE_MODULE) item=templateName}
						<option {if isset($SELECTED_TEMPLATE) && $SELECTED_TEMPLATE eq $templateName}selected="selected"{/if}
								value="{$templateName}">{$templateName}</option>
					{/foreach}
				</select>
			</div>
		</div>
	</div>
{/strip}
