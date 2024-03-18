{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<form id="dotEmailExportModal" class="tpl-Vtiger-ExportDotEmail" action="index.php?module={$MODULE_NAME}&action=DotEmail&mode=send" method="POST">
		<div class="modal-header">
			<h5 class="modal-title"><span class="fas fa-file-pdf mr-1"></span>{\App\Language::translate('LBL_DOT_EMAIL_SEND_MAIL', $MODULE_NAME)}</h5>
			<button type="button" class="close" data-dismiss="modal" aria-label="Close">
				<span aria-hidden="true">&times;</span>
			</button>
		</div>
		<div class="modal-body" style="height: 500px">
			<input type="hidden" name="module" value="{$MODULE_NAME}"/>
			<input type="hidden" name="action" value="DotEmail"/>
			<input type="hidden" name="viewname" value="{$VIEW_NAME}"/>
			<input type="hidden" name="selected_ids" value="{\App\Purifier::encodeHtml(\App\Json::encode($SELECTED_IDS))}">
			<input type="hidden" name="excluded_ids" value="{\App\Purifier::encodeHtml(\App\Json::encode($EXCLUDED_IDS))}">
			<input type="hidden" name="search_key" value="{$SEARCH_KEY}"/>
			<input type="hidden" name="operator" value="{$OPERATOR}"/>
			<input type="hidden" name="search_value" value="{$ALPHABET_VALUE}"/>
			<input type="hidden" name="search_params" value="{\App\Purifier::encodeHtml(\App\Json::encode($SEARCH_PARAMS))}"/>
			<input type="hidden" name="orderby" value="{\App\Purifier::encodeHtml(\App\Json::encode($ORDER_BY))}"/>
			<input type="hidden" name="record" value="{$RECORD_ID}"/>
			<input type="hidden" name="fromview" value="{$FROM_VIEW}"/>
			<input type="hidden" name="isSortActive" value="1" />
			<div class="card" style="height: 100%">
				<div class="card-header">{\App\Language::translate('LBL_DOT_EMAIL_AVAILABLE_TEMPLATES', $MODULE_NAME)}</div>
				<div>
					<input class="form-control templates-filter" type="text" title="Search" name="search" value="" />
				</div>
				<div class="card-body" style="overflow: auto">
					{foreach from=$TEMPLATES item=TEMPLATE}
						<div class="js-dot-email-template-content row" data-js="container">
							<label class="col-sm-11 text-left pt-0" for="emailTpl{$TEMPLATE->getId()}">
								{\App\Language::translate('LBL_TEMPLATE', $MODULE_NAME)} {\App\Language::translate($TEMPLATE->get('name'), $MODULE_NAME)}
							</label>
							<div class="col-sm-1">
								<input type="checkbox" id="emailTpl{$TEMPLATE->getId()}" name="email_template[]" class="checkbox" value="{$TEMPLATE->getId()}"/>
							</div>
						</div>
					{/foreach}
				</div>
			</div>
		</div>
		<div>
			<div class="js-dot-email-template-content" data-js="container">
				<label class="col-sm-11 col-form-label text-left pt-0" for="redirect_email_to_test_mailbox">
					{\App\Language::translate('LBL_REDIRECT_EMAIL_TO_TEST_MAILBOX', $MODULE_NAME)}
				</label>
				<div class="col-sm-1">
					<input type="checkbox" id="redirect_email_to_test_mailbox" name="redirect_email_to_test_mailbox" class="checkbox" />
				</div>
			</div>
		</div>
		<div class="modal-footer">
			<div class="btn-group mr-0">
				<button id="send_dot_email" type="submit" class="btn btn-success js-submit-button"{if !$ACTIVE} disabled="disabled"{/if} data-js="click">
					<span class="fas fa-envelope mr-1"></span>{\App\Language::translate('LBL_DOT_EMAIL_SEND', $MODULE_NAME)}
				</button>
			</div>
			<button class="btn btn-danger" type="reset" data-dismiss="modal"><span class="fas fa-times mr-1"></span>{\App\Language::translate('LBL_CANCEL', $MODULE_NAME)}</button>
		</div>
	</form>
{/strip}
