{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
<!-- tpl-Base-Detail-Widget-GeneralInfoMultiColumn -->
{assign var=TRANSLATED_LABEL value=\App\Language::translate('LBL_RECORD_SUMMARY',$MODULE_NAME)}
<div class="c-detail-widget c-detail-widget--general-info-multi-column js-widget-general-info-multi-column" data-js="edit/save">
	<div class="c-detail-widget__header js-detail-widget-header collapsed border-bottom-0">
		<div class="c-detail-widget__header__container d-flex align-items-center py-1">
			<div class="c-detail-widget__toggle collapsed" id="{$TRANSLATED_LABEL}-multi-column" data-toggle="collapse" data-target="#{$TRANSLATED_LABEL}-multi-column-collapse" aria-expanded="false" aria-controls="{$TRANSLATED_LABEL}-multi-column-collapse">
				<span class="u-transform_rotate-180deg mdi mdi-chevron-down" alt="{\App\Language::translate('LBL_EXPAND_BLOCK')}"></span>
			</div>
			<div class="c-detail-widget__header__title">
				<h5 class="mb-0" title="{$TRANSLATED_LABEL}">{$TRANSLATED_LABEL}</h5>
			</div>
			{if !$IS_READ_ONLY}
				<div class="row inline justify-center js-hb__container ml-auto">
					<button type="button" tabindex="0" class="btn js-hb__btn u-hidden-block-btn text-grey-6 py-0 px-1">
						<div class="text-center col items-center justify-center row">
							<i aria-hidden="true" class="mdi mdi-wrench"></i>
						</div>
					</button>
					<div class="u-hidden-block items-center js-comment-actions">
						{assign var="CURRENT_VIEW" value="full"}
						{assign var="CURRENT_MODE_LABEL" value="{\App\Language::translate('LBL_COMPLETE_DETAILS',{$MODULE_NAME})}"}
						<button type="button" class="btn btn-sm btn-light changeDetailViewMode ml-auto">
							<span title="{\App\Language::translate('LBL_SHOW_FULL_DETAILS',$MODULE_NAME)}" class="fas fa-th-list"></span>
						</button>
						{assign var="FULL_MODE_URL" value={$RECORD->getDetailViewUrl()|cat:'&mode=showDetailViewByMode&requestMode=full'}}
						<input type="hidden" name="viewMode" value="{$CURRENT_VIEW}" data-nextviewname="full" data-currentviewlabel="{$CURRENT_MODE_LABEL}" data-full-url="{$FULL_MODE_URL}" />
					</div>
				</div>
			{/if}
		</div>
	</div>
	<div class="c-detail-widget__content js-detail-widget-collapse js-detail-widget-content collapse multi-collapse pt-0{if $IS_READ_ONLY} show{/if}" id="{$TRANSLATED_LABEL}-multi-column-collapse" data-storage-key="GeneralInfoMultiColumn" aria-labelledby="{$TRANSLATED_LABEL}"
		data-js="container|value">
		<table class="c-detail-widget__table u-table-fixed">
			<tbody>
				{if !empty($SUMMARY_RECORD_STRUCTURE['SUMMARY_FIELDS'])}
					{assign var=SUMMARY_FIELDS value=array_values($SUMMARY_RECORD_STRUCTURE['SUMMARY_FIELDS'])}
					{assign var=FIELDS_CNT value=count($SUMMARY_FIELDS)}
					{assign var=COLUMNS_CNT value=$WIDGET['data']['columns']}
					{assign var=COLUMNS_SUM value=0}
					{assign var=COLUMN_FIELDS value=[]}
					{assign var=COLUMN_OFFSETS value=[0]}
					{for $i=1 to $COLUMNS_CNT}
						{assign var=FIELDS_CNT_TMP value=ceil($FIELDS_CNT / ($COLUMNS_CNT - ($i - 1)))}
						{assign var=BLACKHOLE value=array_push($COLUMN_OFFSETS, $COLUMNS_SUM + $FIELDS_CNT_TMP)}
						{assign var=BLACKHOLE value=array_push($COLUMN_FIELDS, $FIELDS_CNT_TMP)}
						{assign var=COLUMNS_SUM value=$COLUMNS_SUM + $FIELDS_CNT_TMP}
						{assign var=FIELDS_CNT value = $FIELDS_CNT - $FIELDS_CNT_TMP}
					{/for}
					{for $i=0 to $COLUMN_FIELDS[0] - 1}
						{if $i > 0}
							</tr>
						{/if}
						<tr class="c-table__row--hover">
							{for $j=0 to $COLUMNS_CNT - 1}
								{assign var=FIELD_IDX value=$i + $COLUMN_OFFSETS[$j]}
								{if $i < $COLUMN_FIELDS[$j]}
									{assign var=FIELD_MODEL value=$SUMMARY_FIELDS[$FIELD_IDX]}
									<td class="{$WIDTHTYPE}" style="text-align: right; {if $j > 0 && $i == $COLUMN_FIELDS[$j] - 1}border-bottom: 0.8px solid rgb(222, 226, 230); {/if}">
										<label class="font-weight-bold mb-0">{\App\Language::translate($FIELD_MODEL->getFieldLabel(),$MODULE_NAME)}
											{assign var=HELPINFO_LABEL value=\App\Language::getTranslateHelpInfo($FIELD_MODEL,$VIEW)}
											{if $HELPINFO_LABEL}
												<a href="#" class="js-help-info float-right u-cursor-pointer" title="" data-placement="top" data-content="{$HELPINFO_LABEL}"
													data-original-title="{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $MODULE_NAME)}">
													<span class="fas fa-info-circle"></span>
												</a>
											{/if}
										</label>
									</td>
									<td class="fieldValue {$WIDTHTYPE}" style="{if $j < $COLUMNS_CNT - 1}border-right: 0.8px solid rgb(222, 226, 230); {/if}{if $j > 0 && $i == $COLUMN_FIELDS[$j] - 1}border-bottom: 0.8px solid rgb(222, 226, 230); {/if}">
										<div class="c-detail-widget__header__container d-flex align-items-center px-0">
											<div class="value px-0 w-100" {if $FIELD_MODEL->getUIType() eq '19' or $FIELD_MODEL->getUIType() eq '20'
												or $FIELD_MODEL->getUIType() eq '21'}style="word-wrap: break-word;"{/if}>
												{include file=\App\Layout::getTemplatePath($FIELD_MODEL->getUITypeModel()->getDetailViewTemplateName())
												FIELD_MODEL=$FIELD_MODEL USER_MODEL=$USER_MODEL MODULE=$MODULE_NAME RECORD=$RECORD SOURCE_TPL='GeneralInfoWidget'}
											</div>
											{if empty($IS_READ_ONLY) && $FIELD_MODEL->isEditable() eq 'true' && $IS_AJAX_ENABLED &&
											$FIELD_MODEL->isAjaxEditable() eq 'true'}
												<div class="d-none edit input-group px-0">
													{include file=\App\Layout::getTemplatePath($FIELD_MODEL->getUITypeModel()->getTemplateName(),
													$MODULE_NAME) FIELD_MODEL=$FIELD_MODEL USER_MODEL=$USER_MODEL MODULE=$MODULE_NAME}
													{if $FIELD_MODEL->getFieldDataType() eq 'boolean' || $FIELD_MODEL->getFieldDataType() eq 'picklist'}
													<input type="hidden" class="fieldname" data-type="{$FIELD_MODEL->getFieldDataType()}" value='{$FIELD_MODEL->getName()}' data-prev-value='{\App\Purifier::encodeHtml($FIELD_MODEL->get(' fieldvalue'))}' />
													{else}
													{assign var=FIELD_VALUE value=$FIELD_MODEL->getEditViewDisplayValue($FIELD_MODEL->get('fieldvalue'),
													$RECORD)}
													{if $FIELD_VALUE|is_array}
													{assign var=FIELD_VALUE value=\App\Json::encode($FIELD_VALUE)}
													{/if}
													<input type="hidden" class="fieldname" value='{$FIELD_MODEL->getName()}' data-type="{$FIELD_MODEL->getFieldDataType()}" data-prev-value='{\App\Purifier::encodeHtml($FIELD_VALUE)}' />
													{/if}
												</div>
												<div class="c-table__action--hover js-detail-quick-edit  u-cursor-pointer px-0 ml-1 u-w-fit" data-js="click">
													<button type="button" class="btn btn-sm btn-light float-right">
														<span class="yfi yfi-full-editing-view" title="{\App\Language::translate('LBL_EDIT',$MODULE_NAME)}"></span>
													</button>
												</div>
											{/if}
										</div>
									</td>
								{/if}
							{/for}
					{/for}
				{/if}
			</tbody>
		</table>
	</div>
</div>
<!-- /tpl-Base-Detail-Widget-GeneralInfo -->
{/strip}
