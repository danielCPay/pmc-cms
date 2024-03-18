{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-Base-ConditionBuilderRow c-condition-builder__row d-flex pt-2 form-group-sm js-condition-builder-conditions-row"
		 data-js="container">
		{if empty($SELECTED_FIELD_MODEL) && !empty($CONDITIONS_ROW)}
			{if $CONDITIONS_ROW['fieldname'] eq 'special-current-role'} 
				{assign var=OPERATORS value=['e' => 'LBL_EQUALS', 'n' => 'LBL_NOT_EQUAL_TO']}
			{else}
				{assign var=SELECTED_FIELD_MODEL value=Vtiger_Field_Model::getInstanceFromFilter($CONDITIONS_ROW['fieldname'])}
				{assign var=OPERATORS value=$SELECTED_FIELD_MODEL->getRecordOperators()}
			{/if}
		{/if}
		{if empty($SELECTED_OPERATOR) && !empty($CONDITIONS_ROW)}
			{assign var=SELECTED_OPERATOR value=$CONDITIONS_ROW['operator']}
		{/if}
		{if empty($FIELD_INFO) && !empty($CONDITIONS_ROW)}
			{assign var=FIELD_INFO value=$CONDITIONS_ROW['fieldname']}
		{/if}
		<div class="col-4">
			<select class="select2 form-control js-conditions-fields" data-js="change">
				{foreach key=BLOCK_LABEL item=BLOCK_FIELDS from=$RECORD_STRUCTURE}
					<optgroup label="{\App\Language::translate($BLOCK_LABEL, $SOURCE_MODULE)}">
						{foreach key=FIELD_NAME item=FIELD_MODEL from=$BLOCK_FIELDS}
							<option value="{$FIELD_MODEL->getCustomViewSelectColumnName()}" {if $FIELD_INFO eq $FIELD_MODEL->getCustomViewSelectColumnName()} selected="selected"{/if}>
								{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $SOURCE_MODULE)}
							</option>
						{/foreach}
					</optgroup>
				{/foreach}
				{foreach key=GROUP_LABEL item=GROUP_FIELDS from=$RECORD_STRUCTURE_RELATED_MODULES}
				{foreach key=SOURCE_FIELD item=RECORD_STRUCTURE_FIELDS from=$GROUP_FIELDS}
					<optgroup label="{$GROUP_LABEL}">
						{foreach item=FIELD_MODEL from=$RECORD_STRUCTURE_FIELDS}
							{assign var=CUSTOM_FIELD_NAME value=$FIELD_MODEL->getCustomViewSelectColumnName($SOURCE_FIELD)}
							{assign var=RELATED_FIELD_LABEL value=$FIELD_MODEL->getFieldLabel()}
							<option value="{$CUSTOM_FIELD_NAME}"
									data-field-name="{$GROUP_LABEL} - {\App\Language::translate($FIELD_MODEL->getFieldLabel(), $FIELD_MODEL->getModuleName())}"
									{if $FIELD_INFO eq $FIELD_MODEL->getCustomViewSelectColumnName($SOURCE_FIELD)} selected="selected" {/if}
									data-js="data-sort-index|data-field-name">
								{$GROUP_LABEL}&nbsp;-&nbsp;{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $FIELD_MODEL->getModuleName())}
							</option>
						{/foreach}
					</optgroup>
				{/foreach}
				{/foreach}
				<optgroup label="{\App\Language::translate('LBL_SPECIAL_OPTIONS', $SOURCE_MODULE)}">
					<option value="special-current-role" {if $FIELD_INFO eq 'special-current-role'} selected="selected"{/if}>
						{\App\Language::translate('LBL_SPECIAL_CURRENT_ROLE', $SOURCE_MODULE)}
					</option>
				</optgroup>
			</select>
		</div>
		<div class="col-3">
			<select class="select2 form-control js-conditions-operator" data-js="change">
				{foreach key=OP item=OPERATOR from=$OPERATORS}
					<option value="{$OP}" {if $SELECTED_OPERATOR eq $OP}selected="selected"{/if}>
						{\App\Language::translate($OPERATOR, $SOURCE_MODULE)}
					</option>
				{/foreach}
			</select>
		</div>
		<div class="col-4">
			{if $FIELD_INFO eq 'special-current-role'}
				{assign var=CONDITION_ROW_VALUE value=\App\Purifier::decodeHtml($CONDITIONS_ROW['value'])}
				{assign var=ROLESLIST value=[]}
				{assign var=ACCESSIBLE_ROLES value=Settings_Roles_Record_Model::getAll()}
				{foreach key=ROLE_ID item=ROLE from=$ACCESSIBLE_ROLES}
					{$ROLESLIST[$ROLE_ID] = $ROLE->getName()}
				{/foreach}
				<div class="tpl-Base-ConditionBuilder-PickList">
					<select class="js-picklist-field select2 form-control js-condition-builder-value"
							title="{\App\Language::translate('LBL_SPECIAL_CURRENT_ROLE')}"
							data-placeholder="{\App\Language::translate('LBL_SELECT_OPTION')}">
						{foreach item=PICKLIST_VALUE key=PICKLIST_NAME from=$ROLESLIST}
							<option value="{$PICKLIST_NAME}" title="{\App\Purifier::encodeHtml($PICKLIST_VALUE)}" {if $PICKLIST_NAME eq $CONDITION_ROW_VALUE} selected {/if}>{\App\Purifier::encodeHtml($PICKLIST_VALUE)}</option>
						{/foreach}
					</select>
				</div>
			{else}
				{assign var=TEMPLATE_NAME value=$SELECTED_FIELD_MODEL->getOperatorTemplateName($SELECTED_OPERATOR)}
				{if !empty($TEMPLATE_NAME)}
					{if isset($CONDITIONS_ROW['value'])}
						{assign var=CONDITION_ROW_VALUE value=\App\Purifier::decodeHtml($CONDITIONS_ROW['value'])}
					{else}
						{assign var=CONDITION_ROW_VALUE value=''}
					{/if}
					{assign var=FIELD_MODEL value=$SELECTED_FIELD_MODEL->getConditionBuilderField($SELECTED_OPERATOR)}
					{include file=\App\Layout::getTemplatePath($TEMPLATE_NAME, $SOURCE_MODULE) FIELD_MODEL=$FIELD_MODEL VALUE=$CONDITION_ROW_VALUE}
				{/if}
			{/if}
		</div>
		<div class="col-1 d-flex justify-content-end">
			<button type="button" class="btn btn-sm btn-danger js-condition-delete" data-js="click">
				<span class="fas fa-trash"></span>
			</button>
		</div>
	</div>
{/strip}
