{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Calendar-Extended-ActivityButtons -->
	{if !empty($RECORD)}
		{assign var=ACTIVITY_STATE_LABEL value=Calendar_Module_Model::getComponentActivityStateLabel()}
		{assign var=ACTIVITY_STATE value=$RECORD->get('activitystatus')}
		{assign var=EMPTY value=!in_array($ACTIVITY_STATE, [$ACTIVITY_STATE_LABEL.cancelled,$ACTIVITY_STATE_LABEL.completed])}
		{if $RECORD->isEditable() && $EMPTY && (\App\Privilege::isPermitted($MODULE_NAME, 'ActivityCancel', $ID) || \App\Privilege::isPermitted($MODULE_NAME, 'ActivityComplete', $ID))}
			{assign var="FIELD_MODEL" value=$RECORD->getField('note')}
			{assign var="PARAMS" value=$FIELD_MODEL->getFieldParams()}
			<div class="fieldsLabelValue pl-0 pr-0 mb-2 {$WIDTHTYPE} {$WIDTHTYPE_GROUP}">
				{if !(isset($PARAMS['hideLabel']) && in_array($VIEW, $PARAMS['hideLabel']))}
					<div class="col-12 px-2 u-fs-sm">
						<label class="muted mt-0 mb-0">
							<span class="redColor">*</span>
							{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $MODULE_NAME)}
						</label>
					</div>
				{/if}
				<div class="fieldValue col-12 px-2">
					{include file=\App\Layout::getTemplatePath($FIELD_MODEL->getUITypeModel()->getTemplateName(), $MODULE_NAME)}
				</div>
			</div>
		{/if}
		<div class="js-activity-buttons d-flex justify-content-center flex-wrap mb-2" data-js="container">
			{assign var=ID value=$RECORD->getId()}
			{if $RECORD->isEditable()}
				{assign var=ACTIVITY_STATE_LABEL value=Calendar_Module_Model::getComponentActivityStateLabel()}
				{assign var=SHOW_QUICK_CREATE value=App\Config::module('Calendar','SHOW_QUICK_CREATE_BY_STATUS')}
				{if $EMPTY && \App\Privilege::isPermitted($MODULE_NAME, 'ActivityCancel', $ID)}
					<button type="button"
							class="mr-1 mt-1 btn btn-sm btn-warning {if in_array($ACTIVITY_STATE_LABEL.cancelled,$SHOW_QUICK_CREATE)}showQuickCreate{/if}"
							data-state="{$ACTIVITY_STATE_LABEL.cancelled}" data-id="{$ID}"
							data-type="1" data-js="click"
							title="{\App\Language::translate($ACTIVITY_STATE_LABEL.cancelled, $MODULE_NAME)}">
						<span class="fas fa-ban"></span>
						<span class="ml-1">{\App\Language::translate($ACTIVITY_STATE_LABEL.cancelled, $MODULE_NAME)}</span>
					</button>
				{/if}
				{if $EMPTY && \App\Privilege::isPermitted($MODULE_NAME, 'ActivityComplete', $ID)}
					<button type="button"
							class="mr-1 mt-1 btn btn-sm c-btn-done {if in_array($ACTIVITY_STATE_LABEL.completed,$SHOW_QUICK_CREATE)}showQuickCreate{/if}"
							data-state="{$ACTIVITY_STATE_LABEL.completed}" data-id="{$ID}"
							data-type="1" data-js="click"
							title="{\App\Language::translate($ACTIVITY_STATE_LABEL.completed, $MODULE_NAME)}">
						<span class="far fa-check-square fa-lg"></span>
						<span class="ml-1">{\App\Language::translate($ACTIVITY_STATE_LABEL.completed, $MODULE_NAME)}</span>
					</button>
				{/if}
				{if $EMPTY && \App\Privilege::isPermitted($MODULE_NAME, 'ActivityPostponed', $ID)}
					<button type="button" class="mr-1 mt-1 btn btn-sm btn-primary showQuickCreate"
							data-state="{$ACTIVITY_STATE_LABEL.postponed}" data-id="{$ID}"
							data-type="0"
							data-dismiss="modal"
							data-js="click"
							title="{\App\Language::translate($ACTIVITY_STATE_LABEL.postponed, $MODULE_NAME)}">
						<span class="fas fa-angle-double-right"></span>
						<span class="ml-1">{\App\Language::translate($ACTIVITY_STATE_LABEL.postponed, $MODULE_NAME)}</span>
					</button>
				{/if}
			{/if}
		</div>
	{/if}
	<!-- /tpl-Calendar-Extended-ActivityButtons -->
{/strip}
