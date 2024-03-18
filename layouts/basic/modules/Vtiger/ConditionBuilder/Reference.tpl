{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-Base-ConditionBuilder-Base input-group input-group-sm">
		{if \str_starts_with($SELECTED_OPERATOR, 'r')}
			<select class="select2 form-control js-condition-builder-value">
				{foreach key=BLOCK_LABEL item=BLOCK_FIELDS from=$RECORD_STRUCTURE}
					{assign var=HAS_GROUP value=false}
					{foreach key=FIELD_NAME item=FIELD_MODEL from=$BLOCK_FIELDS}
						{if $FIELD_MODEL->isReferenceField()}
							{if !$HAS_GROUP}
								<optgroup label="{\App\Language::translate($BLOCK_LABEL, $SOURCE_MODULE)}">
							{/if}
							<option value="{$FIELD_MODEL->getCustomViewSelectColumnName()}" {if $VALUE eq $FIELD_MODEL->getCustomViewSelectColumnName()} selected="selected"{/if}>
								{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $SOURCE_MODULE)}
							</option>
							{if !$HAS_GROUP}
								</optgroup>
								{assign var=HAS_GROUP value=true}
							{/if}
						{/if}
					{/foreach}
				{/foreach}
				{foreach key=MODULE_KEY item=RECORD_STRUCTURE_FIELD from=$RECORD_STRUCTURE_RELATED_MODULES}
					{foreach key=RELATED_FIELD_NAME item=RECORD_STRUCTURE from=$RECORD_STRUCTURE_FIELD}
						{assign var=RELATED_FIELD_LABEL value=Vtiger_Field_Model::getInstance($RELATED_FIELD_NAME, Vtiger_Module_Model::getInstance($SOURCE_MODULE))->getFieldLabel()}
						{foreach key=BLOCK_LABEL item=BLOCK_FIELDS from=$RECORD_STRUCTURE}
							{assign var=HAS_GROUP value=false}
							{foreach key=FIELD_NAME item=FIELD_MODEL from=$BLOCK_FIELDS}
								{if $FIELD_MODEL->isReferenceField()}
									{if !$HAS_GROUP}
										<optgroup label="{\App\Language::translate($RELATED_FIELD_LABEL, $SOURCE_MODULE)}&nbsp;-&nbsp;{\App\Language::translate($MODULE_KEY, $MODULE_KEY)}&nbsp;-&nbsp;{\App\Language::translate($BLOCK_LABEL, $MODULE_KEY)}">
									{/if}
										<option value="{$FIELD_MODEL->getCustomViewSelectColumnName($RELATED_FIELD_NAME)}" {if $VALUE eq $FIELD_MODEL->getCustomViewSelectColumnName($RELATED_FIELD_NAME)} selected="selected"{/if}>
											{\App\Language::translate($RELATED_FIELD_LABEL, $SOURCE_MODULE)}
											&nbsp;-&nbsp;{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $MODULE_KEY)}
										</option>
									{if !$HAS_GROUP}
										</optgroup>
										{assign var=HAS_GROUP value=true}
									{/if}
								{/if}
							{/foreach}
						{/foreach}
					{/foreach}
				{/foreach}
			</select>
		{else}
			<input class="form-control js-condition-builder-value"
			   data-js="val"
			   title="{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $FIELD_MODEL->getModuleName())}"
			   value="{\App\Purifier::encodeHtml($VALUE)}"
			   data-fieldinfo="{\App\Purifier::encodeHtml(\App\Json::encode($FIELD_MODEL->getFieldInfo()))}"
			   data-validation-engine="validate[required,funcCall[Vtiger_Base_Validator_Js.invokeValidation]]"
			   autocomplete="off"/>
		{/if}
	</div>
{/strip}
