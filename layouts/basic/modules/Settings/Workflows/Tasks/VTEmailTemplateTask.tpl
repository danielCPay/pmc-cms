{strip}
	{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
	<div id="VtVTEmailTemplateTaskContainer">
		<div class="">
			<div class="row pb-3">
				<span class="col-md-4 col-form-label text-right">{\App\Language::translate('EmailTempleteList', $QUALIFIED_MODULE)}</span>
				<div class="col-md-4">
					<select class="select2 form-control" name="template" data-validation-engine="validate[required]"
							data-select="allowClear"
							data-placeholder="{\App\Language::translate('LBL_NONE', $QUALIFIED_MODULE)}">
						<optgroup class="p-0">
							<option value="">{\App\Language::translate('LBL_NONE', $QUALIFIED_MODULE)}</option>
						</optgroup>
						{foreach from=App\Mail::getTemplateList($SOURCE_MODULE,'PLL_RECORD') key=key item=item}
							<option {if isset($TASK_OBJECT->template) && $TASK_OBJECT->template eq $item['number']}selected=""{/if}
									value="{$item['number']}">{\App\Language::translate($item['name'], $QUALIFIED_MODULE)}</option>
						{/foreach}
					</select>
				</div>
			</div>
			<div class="row pb-3">
				<span class="col-md-4"></span>
				<span class="col-md-4">
					<label><input type="checkbox" class="align-text-bottom" value="true" name="emailoptout"
								  {if isset($TASK_OBJECT->emailoptout) && $TASK_OBJECT->emailoptout}checked{/if}>&nbsp;{\App\Language::translate('LBL_CHECK_EMAIL_OPTOUT', $QUALIFIED_MODULE)}</label>
				</span>
			</div>
			<div class="row pb-3">
				{assign var=EMAIL value=settype($TASK_OBJECT->email, 'array')}
				<span class="col-md-4 col-form-label text-right">{\App\Language::translate('Select e-mail address', $QUALIFIED_MODULE)}</span>
				<div class="col-md-4">
					<select class="select2 form-control" name="email"
							data-placeholder="{\App\Language::translate('LBL_SELECT_FIELD',$QUALIFIED_MODULE)}"
							multiple="multiple">
						{assign var=TEXT_PARSER value=App\TextParser::getInstance($SOURCE_MODULE)}
						{foreach item=FIELDS key=BLOCK_NAME from=$TEXT_PARSER->getRecordVariable('email')}
							<optgroup label="{$BLOCK_NAME}">
								{foreach item=ITEM from=$FIELDS}
									<option value="{$ITEM['var_value']}" data-label="{$ITEM['var_label']}"
											{if isset($TASK_OBJECT->email) && $TASK_OBJECT->email && in_array($ITEM['var_value'],$TASK_OBJECT->email)}selected=""{/if}>
										{$ITEM['label']}
									</option>
								{/foreach}
							</optgroup>
						{/foreach}
						{foreach item=FIELDS from=$TEXT_PARSER->getRelatedVariable('email')}
							{foreach item=RELATED_FIELDS key=BLOCK_NAME from=$FIELDS}
								<optgroup label="{$BLOCK_NAME}">
									{foreach item=ITEM from=$RELATED_FIELDS}
										<option value="{$ITEM['var_value']}" data-label="{$ITEM['var_label']}"
												{if isset($TASK_OBJECT->email) && $TASK_OBJECT->email && in_array($ITEM['var_value'],$TASK_OBJECT->email)}selected=""{/if}>
											{$ITEM['label']}
										</option>
									{/foreach}
								</optgroup>
							{/foreach}
						{/foreach}
						{foreach item=RELATED_FIELDS key=BLOCK_NAME from=$TEXT_PARSER->getRelatedLevelVariable('email')}
							<optgroup label="{$BLOCK_NAME}">
								{foreach item=ITEM from=$RELATED_FIELDS}
									<option value="{$ITEM['var_value']}" data-label="{$ITEM['var_label']}"
											{if isset($TASK_OBJECT->email) && $TASK_OBJECT->email && in_array($ITEM['var_value'],$TASK_OBJECT->email)}selected=""{/if}>
										{$ITEM['label']}
									</option>
								{/foreach}
							</optgroup>
						{/foreach}
					</select>
				</div>
			</div>
			<div class="row pb-3">
				<span class="col-md-4 col-form-label text-right">
					{\App\Language::translate('LBL_SELECT_RELATIONS_EMAIL_ADDRESS', $QUALIFIED_MODULE)}
					<span class="js-popover-tooltip ml-1 delay0" data-js="popover" data-placement="top"
						  data-content="{\App\Language::translate('LBL_SELECT_RELATIONS_EMAIL_ADDRESS_INFO',$QUALIFIED_MODULE)}">
						<span class="fas fa-info-circle"></span>
					</span>
				</span>
				<div class="col-md-4">
					<select class="select2 form-control" name="relations_email" data-placeholder="{\App\Language::translate('LBL_SELECT_FIELD',$QUALIFIED_MODULE)}">
						<option value="-">{\App\Language::translate('LBL_NONE')}</option>
						{foreach item=LABEL key=KEY from=$RELATED_RECORDS_EMAIL}
								<option value="{$KEY}" {if isset($TASK_OBJECT->relations_email) && $TASK_OBJECT->relations_email === $KEY}selected=""{/if}>
									{$LABEL}
								</option>
						{/foreach}
					</select>
				</div>
			</div>
			<div class="row pb-3">
				<span class="col-md-4 col-form-label text-right">
					{\App\Language::translate('LBL_SELECT_GROUP', $QUALIFIED_MODULE)}
					<span class="js-popover-tooltip ml-1 delay0" data-js="popover" data-placement="top"
						  data-content="{\App\Language::translate('LBL_SELECT_RELATIONS_EMAIL_ADDRESS_INFO',$QUALIFIED_MODULE)}">
						<span class="fas fa-info-circle"></span>
					</span>
				</span>
				<div class="col-md-4">
					<select class="select2 form-control" name="groups_email" data-placeholder="{\App\Language::translate('LBL_SELECT_GROUP',$QUALIFIED_MODULE)}">
						<option value="-">{\App\Language::translate('LBL_NONE')}</option>
						{foreach item=LABEL key=KEY from=$GROUPS_EMAIL}
								<option value="{$KEY}" {if isset($TASK_OBJECT->groups_email) && $TASK_OBJECT->groups_email == $KEY}selected=""{/if}>
									{$LABEL}
								</option>
						{/foreach}
					</select>
				</div>
			</div>
			<div class="row pb-3">
				<span class="col-md-4 col-form-label text-right">{\App\Language::translate('LBL_TO')}</span>
				<div class="col-md-4">
					<input class="form-control"
						   data-validation-engine="validate[funcCall[Vtiger_Base_Validator_Js.invokeValidation]]"
						   name="address_emails" value="{if isset($TASK_OBJECT->address_emails)}{$TASK_OBJECT->address_emails}{/if}">
				</div>
			</div>
			<div class="row pb-3">
				<span class="col-md-4 col-form-label text-right">{\App\Language::translate('LBL_ATTACH_DOCS_FROM', $QUALIFIED_MODULE)}</span>
				<div class="col-md-4">
					<select class="select2 form-control" name="attachments"
							data-placeholder="{\App\Language::translate('LBL_SELECT_FIELD',$QUALIFIED_MODULE)}">
							<option value="">{\App\Language::translate('LBL_NONE')}</option>
							{if $DOCUMENTS_MODULLES}
								<option value="{$SOURCE_MODULE}" {if isset($TASK_OBJECT->attachments) && $TASK_OBJECT->attachments === $SOURCE_MODULE}selected="selected"{/if}>{\App\Language::translate($SOURCE_MODULE,$SOURCE_MODULE)}</option>
							{/if}
							{foreach from=$DOCUMENTS_RELATED_MODULLES item=RELATED_MODULES}
								{foreach from=$RELATED_MODULES key=RELATED_MODULE_NAME item=FIELD_MODEL}
									<option value="{$RELATED_MODULE_NAME}::{$FIELD_MODEL->getFieldName()}"
											{if isset($TASK_OBJECT->attachments) && $TASK_OBJECT->attachments === {$RELATED_MODULE_NAME}|cat:'::'|cat:{$FIELD_MODEL->getFieldName()}}selected="selected"{/if}>
										{\App\Language::translate($FIELD_MODEL->getFieldLabel(),$SOURCE_MODULE)}&nbsp;({$FIELD_MODEL->getFieldName()})&nbsp;-&nbsp;{\App\Language::translate($RELATED_MODULE_NAME,$RELATED_MODULE_NAME)}
									</option>
								{/foreach}
							{/foreach}
					</select>
				</div>
			</div>
			<div class="row pb-3">
				<span class="col-md-4 col-form-label text-right">{\App\Language::translate('LBL_DOCS_FILTER', $QUALIFIED_MODULE)}</span>
				<div class="col-md-4">
					<select class="select2 form-control" name="filter"
							data-placeholder="{\App\Language::translate('LBL_ALL',$QUALIFIED_MODULE)}">
							<option value="All" {if !isset($TASK_OBJECT->filter) || $TASK_OBJECT->filter === 'All'}selected="selected"{/if} >{\App\Language::translate('All', $QUALIFIED_MODULE)}</option>
							{assign var=DOCUMENT_TYPES value=\DocumentTypes_Module_Model::getAllTypeNumbers()}
							{foreach from=$DOCUMENT_TYPES key=ID item=DOCUMENT_TYPE_DATA}
								<option value="{$DOCUMENT_TYPE_DATA['number']}" {if isset($TASK_OBJECT->filter) && $TASK_OBJECT->filter === $DOCUMENT_TYPE_DATA['number']}selected="selected"{/if}>
									{\App\Language::translate($DOCUMENT_TYPE_DATA['document_area'],'DocumentTypes')} -> {\App\Language::translate($DOCUMENT_TYPE_DATA['document_type'],'DocumentTypes')}
								</option>
							{/foreach}
					</select>
				</div>
			</div>
			<div class="row pb-3">
				<span class="col-md-4 col-form-label text-right">{\App\Language::translate('LBL_BCC')}</span>
				<div class="col-md-4">
					<input class="form-control" data-validation-engine="validate[funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" name="copy_email" value="{if isset($TASK_OBJECT->copy_email)}{$TASK_OBJECT->copy_email}{/if}">
				</div>
			</div>
			<div class="row pb-3">
				<span class="col-md-4 col-form-label text-right">
					{\App\Language::translate('LBL_CONDITION', 'Settings:Workflows')}
					<span class="js-popover-tooltip ml-1 delay0" data-js="popover" data-placement="top"
						  data-content="{\App\Language::translate('LBL_VTEMAIL_TEMPLATE_TASK_CONDITION_INFO',$QUALIFIED_MODULE)}">
						<span class="fas fa-info-circle"></span>
					</span>
				</span>
				<div class="col-md-4">
					<input class="form-control"
								data-validation-engine="validate[funcCall[Vtiger_Base_Validator_Js.invokeValidation]]"
								name="conditionString" value="{if isset($TASK_OBJECT->conditionString)}{htmlspecialchars($TASK_OBJECT->conditionString)}{/if}">
				</div>
			</div>
		</div>
	</div>
{/strip}
