{strip}
	{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
	<div id="VtVTGeneratePDFTaskContainer">
		<div class="row">
			<span class="col-md-4 col-form-label text-right">{\App\Language::translate('LBL_DOCUMENT_TEMPLATE', $QUALIFIED_MODULE)}</span>
			<div class="col-md-4 pb-3">
				<select class="select2 form-control" name="template" data-validation-engine="validate[required]"
						data-placeholder="{\App\Language::translate('LBL_SELECT_FIELD',$MODULE)}"
						data-select="allowClear">
					<optgroup class="p-0">
						<option value="none">{\App\Language::translate('LBL_SELECT_FIELD',$MODULE)}</option>
					</optgroup>
          {foreach from=\DocumentTemplates_Record_Model::getActive($SOURCE_MODULE) item=template}
						<option {if isset($TASK_OBJECT->template) && $TASK_OBJECT->template eq $template->get('number')}selected="selected"{/if}
								value="{$template->get('number')}">{$template->getName()}</option>
					{/foreach}
				</select>
			</div>
		</div>
		<div class="row pb-3">
			<span class="col-md-4 col-form-label text-right">{\App\Language::translate('LBL_CONDITION', 'Settings:Workflows')}</span>
			<div class="col-md-4">
				<input class="form-control"
							data-validation-engine="validate[funcCall[Vtiger_Base_Validator_Js.invokeValidation]]"
							name="conditionString" value="{if isset($TASK_OBJECT->conditionString)}{htmlspecialchars($TASK_OBJECT->conditionString)}{/if}">
			</div>
		</div>
		<div class="row pb-3">
			<span class="col-md-4 col-form-label text-right">{\App\Language::translate('LBL_STOP_ON_ERROR', 'Settings:Workflows')}</span>
			<div class="col-md-4">
				<input type="checkbox" name="stopOnError" id="stopOnError" class="alignMiddle" {if !empty($TASK_OBJECT->stopOnError)} checked {/if}/>
			</div>
		</div>
	</div>
{/strip}
