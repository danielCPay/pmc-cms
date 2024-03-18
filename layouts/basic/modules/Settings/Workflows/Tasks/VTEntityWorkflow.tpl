{strip}
	{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
	<div id="VtVTEntityWorkflowTaskContainer">
		<input type="hidden" id="currentWorkflowModule" value="{$TASK_OBJECT->workflowModule}" />
		<input type="hidden" id="currentWorkflowId" value="{$TASK_OBJECT->otherWorkflowId}" />
		<input type="hidden" id="currentWorkflowField" value="{$TASK_OBJECT->otherWorkflowField}" />
		<label class="col-md-4 col-form-label">
			<strong>{\App\Language::translate('LBL_MODULE',$QUALIFIED_MODULE)}
				<span class="redColor">*</span>
			</strong>
		</label>
		<div class="col-md-6">
			<select class="select2 workflowModule" id="workflowModule" name="workflowModule"
					data-validation-engine='validate[required]'
					data-select="allowClear"
					data-placeholder="{\App\Language::translate('LBL_NONE', $QUALIFIED_MODULE)}">
				<optgroup class="p-0">
					<option {if isset($TASK_OBJECT->workflowModule) && $TASK_OBJECT->workflowModule eq $WORKFLOW_MODEL->getModule()->getName()}selected="" {/if} value="CURRENT||{$WORKFLOW_MODEL->getModule()->getName()}">{\App\Language::translate('LBL_CURRENT', $QUALIFIED_MODULE)}</option>
				</optgroup>
				{assign var=ALL_MODULES_INFO value=$WORKFLOW_MODEL->getParentModules()}
				{assign var=ALL_MODULES value=$ALL_MODULES_INFO|array_keys}
				{foreach from=$ALL_MODULES item=MODULE}
					{assign var=MODULE_EXT value="PARENT||$MODULE"}
					<option {if isset($TASK_OBJECT->workflowModule) && $TASK_OBJECT->workflowModule eq $MODULE_EXT}selected="" {/if} value="{$MODULE_EXT}">
						{\App\Language::translate("Parent",$MODULE)}: {\App\Language::translate($MODULE,$MODULE)}
					</option>
				{/foreach}
				{assign var=ALL_MODULES_INFO value=$WORKFLOW_MODEL->getDependentModules()}
				{assign var=ALL_MODULES value=$ALL_MODULES_INFO|array_keys}
				{foreach from=$ALL_MODULES item=MODULE}
					{assign var=MODULE_EXT value="CHILD||$MODULE"}
					<option {if isset($TASK_OBJECT->workflowModule) && $TASK_OBJECT->workflowModule eq $MODULE_EXT}selected="" {/if} value="{$MODULE_EXT}">
						{\App\Language::translate("Child",$MODULE)}: {\App\Language::translate($MODULE,$MODULE)}
					</option>
				{/foreach}
			</select>
		</div>
		<label class="col-md-4 col-form-label">
			<strong>{\App\Language::translate('LBL_WORKFLOW',$QUALIFIED_MODULE)}
				<span class="redColor">*</span>
			</strong>
		</label>
		<div class="col-md-6">
			<select class="select2 otherWorkflowId" id="otherWorkflowId" name="otherWorkflowId"
					data-validation-engine='validate[required]'
					data-select="allowClear"
					data-placeholder="{\App\Language::translate('LBL_NONE', $QUALIFIED_MODULE)}">
				{foreach from=$OTHER_WORKFLOWS item=WORKFLOW}
					<option {if isset($TASK_OBJECT->otherWorkflowId) && $TASK_OBJECT->otherWorkflowId eq $WORKFLOW->description}selected="" {/if} value="{$WORKFLOW->description}">{\App\Language::translate($WORKFLOW->description,$MODULE)}</option>
				{/foreach}
			</select>
		</div>
		<label class="col-md-4 col-form-label">
			<strong>{\App\Language::translate('LBL_FIELD',$QUALIFIED_MODULE)}
				<span class="redColor">*</span>
			</strong>
		</label>
		<div class="col-md-6">
			<select class="select2 otherWorkflowField" id="otherWorkflowField" name="otherWorkflowField"
					data-validation-engine='validate[required]'
					data-select="allowClear"
					data-placeholder="{\App\Language::translate('LBL_NONE', $QUALIFIED_MODULE)}">
					{if empty($OTHER_FIELDS)}
						<option {if isset($TASK_OBJECT->otherWorkflowField) && $TASK_OBJECT->otherWorkflowField eq 'special-current'}selected="" {/if} value="special-current">{\App\Language::translate('LBL_CURRENT', $QUALIFIED_MODULE)}</option>
					{else}
						{foreach from=$OTHER_FIELDS item=FIELD_DATA}
							<option {if isset($TASK_OBJECT->otherWorkflowField) && $TASK_OBJECT->otherWorkflowField eq $FIELD_DATA['id']}selected="" {/if} value="{$FIELD_DATA['id']}">{\App\Language::translate($FIELD_DATA['name'],$TASK_OBJECT->workflowModule)}</option>
						{/foreach}
					{/if}
			</select>
		</div>
		<label class="col-md-4 col-form-label otherWorkflowFieldValueVersion">
			<strong>{\App\Language::translate('LBL_FIELD_VERSION',$QUALIFIED_MODULE)}
				<span class="redColor">*</span>
			</strong>
		</label>
		<div class="col-md-6 otherWorkflowFieldValueVersion">
			<select class="select2" id="otherWorkflowFieldValueVersion" name="otherWorkflowFieldValueVersion"
					data-validation-engine='validate[required]'>
					<option {if isset($TASK_OBJECT->otherWorkflowFieldValueVersion) && $TASK_OBJECT->otherWorkflowFieldValueVersion eq 'New'}selected="" {/if} value="New">{\App\Language::translate('LBL_NEW_VALUE', $QUALIFIED_MODULE)}</option>
					<option {if isset($TASK_OBJECT->otherWorkflowFieldValueVersion) && $TASK_OBJECT->otherWorkflowFieldValueVersion eq 'Old'}selected="" {/if} value="Old">{\App\Language::translate('LBL_OLD_VALUE', $QUALIFIED_MODULE)}</option>
					<option {if isset($TASK_OBJECT->otherWorkflowFieldValueVersion) && $TASK_OBJECT->otherWorkflowFieldValueVersion eq 'Both'}selected="" {/if} value="Both">{\App\Language::translate('LBL_BOTH_OLD_AND_NEW', $QUALIFIED_MODULE)}</option>
			</select>
		</div>
	</div>
{/strip}
