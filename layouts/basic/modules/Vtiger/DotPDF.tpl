{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<form id="dotPdfExportModal" class="tpl-Vtiger-ExportDotPDF" action="index.php?module={$MODULE_NAME}&action=DotPDF&mode=generate" method="POST">
		<div class="modal-header">
			<h5 class="modal-title"><span class="fas fa-file-pdf mr-1"></span>{\App\Language::translate('LBL_GENERATE_DOT_PDF_FILE', $MODULE_NAME)}</h5>
			<button type="button" class="close" data-dismiss="modal" aria-label="Close">
				<span aria-hidden="true">&times;</span>
			</button>
		</div>
		<div class="modal-body" style="height: 500px">
			<input type="hidden" name="module" value="{$MODULE_NAME}"/>
			<input type="hidden" name="action" value="DotPDF"/>
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
				<div class="card-header">
					{\App\Language::translate('LBL_AVAILABLE_PACKAGES_AND_TEMPLATES', $MODULE_NAME)}
				</div>
				<div class="row" data-js="container" style="padding: 0rem 1.25rem">
					<div style="width: 50px"></div>
					<input class="form-control templates-filter column-type col-lg-2 col-xl-1" type="text" title="Search" name="search-type" value="" />
					<input class="form-control templates-filter column-name col-lg-5" type="text" title="Search" name="search-name" value="" />
					<input class="form-control templates-filter column-result col-lg-4 col-xl-5" type="text" title="Search" name="search-result" value="" />
				</div>
				<div class="card-body" style="overflow: auto; padding-top: 0">
					{foreach from=$PACKAGES item=PACKAGE}
						<div class="js-pdf-template-content row" data-js="container">
							<div style="width: 50px">
								<input type="checkbox" id="pdfTpl{$PACKAGE->getId()}" name="pdf_template[]" class="checkbox" value="{$PACKAGE->getId()}"/>
							</div>
							<label class="col-lg-2 col-xl-1 text-left pt-0 column-type" for="pdfTpl{$PACKAGE->getId()}">
								{\App\Language::translate('LBL_PACKAGE', $MODULE_NAME)}
							</label>
							<label class="col-lg-5 text-left pt-0 column-name" for="pdfTpl{$PACKAGE->getId()}">
								{\App\Language::translate($PACKAGE->get('document_package_name'), $MODULE_NAME)}
							</label>
							<label class="col-lg-4 col-xl-5 text-left pt-0 column-result"for="pdfTpl{$PACKAGE->getId()}">
								{\App\Language::translate(Vtiger_Record_Model::getInstanceById($PACKAGE->get('result_document_type'))->getDisplayName(), $MODULE_NAME)}
							</label>
						</div>
					{/foreach}
					{foreach from=$TEMPLATES item=TEMPLATE}
						<div class="js-pdf-template-content row" data-js="container">
							<div style="width: 50px">
								<input type="checkbox" id="pdfTpl{$TEMPLATE->getId()}" name="pdf_template[]" class="checkbox" value="{$TEMPLATE->getId()}"/>
							</div>
							<label class="col-lg-2 col-xl-1 text-left pt-0 column-type" for="pdfTpl{$TEMPLATE->getId()}">
								{\App\Language::translate('LBL_TEMPLATE', $MODULE_NAME)}
							</label>
							<label class="col-lg-5 text-left pt-0 column-name" for="pdfTpl{$TEMPLATE->getId()}">
								{\App\Language::translate($TEMPLATE->get('document_template_name'), $MODULE_NAME)}
								<span class="secondaryName ml-2">[ {\App\Language::translate($TEMPLATE->get('document_template_variant_name'), $MODULE_NAME)} ]</span>
							</label>
							<label class="col-lg-4 col-xl-5 text-left pt-0 column-result"for="pdfTpl{$TEMPLATE->getId()}">
								{\App\Language::translate(Vtiger_Record_Model::getInstanceById($TEMPLATE->get('result_document_type'))->getDisplayName(), $MODULE_NAME)}
							</label>
						</div>
					{/foreach}
				</div>
			</div>
		</div>
		<div class="row" data-js="container">
			<div class="col-sm-{if \App\Config::dropbox('enabled')}3{else}12{/if}">
				<label class="col-sm-12 col-form-label text-left pt-0" for="redirect_email_to_test_mailbox">
					{\App\Language::translate('LBL_REDIRECT_EMAIL_TO_TEST_MAILBOX', $MODULE_NAME)}
				</label>
				<div class="col-sm-1">
					<input type="checkbox" id="redirect_email_to_test_mailbox" name="redirect_email_to_test_mailbox" class="checkbox" />
				</div>
			</div>
			{if \App\Config::dropbox('enabled')}
			<div class="col-sm-9">
				<label class="col-sm-12 col-form-label text-left pt-0" for="send_to_dropbox">
					{\App\Language::translate('LBL_SEND_TO_DROPBOX', $MODULE_NAME)}
				</label>
				<div class="col-sm-12">
					<select class="select2 form-control" name="send_to_dropbox" data-validation-engine="validate[required]"
						data-placeholder="{\App\Language::translate('LBL_SELECT_DROPBOX_DESTINATION',$MODULE)}">
					<optgroup label="{\App\Language::translate('LBL_SELECT_DROPBOX_DESTINATION_SPECIAL',$MODULE)}" class="p-0">
						<option value="-1" selected>{\App\Language::translate('LBL_DROPBOX_DESTINATION_USE_PACKAGE',$MODULE)}</option>
						<option value="-2">{\App\Language::translate('LBL_DROPBOX_DESTINATION_DO_NOT_SEND',$MODULE)}</option>
					</optgroup>
					<optgroup label="{\App\Language::translate('LBL_SELECT_DROPBOX_DESTINATION_DESTINATIONS',$MODULE)}" class="p-0">
						{foreach from=$DESTINATIONS key=$DESTINATION_ID item=$DESTINATION_NAME}
							<option value="{$DESTINATION_ID}">{$DESTINATION_NAME}</option>
						{/foreach}
					</optgroup>
				</select>
				</div>
			</div>
			{/if}
		</div>
		<div class="modal-footer">
			<div class="btn-group mr-0">
				<button id="generate_dot_pdf" type="submit" class="btn btn-success js-submit-button"{if !$ACTIVE} disabled="disabled"{/if} data-js="click">
					<span class="fas fa-file-pdf mr-1"></span>{\App\Language::translate('LBL_GENERATE', $MODULE_NAME)}
				</button>
			</div>
			<button class="btn btn-danger" type="reset" data-dismiss="modal"><span class="fas fa-times mr-1"></span>{\App\Language::translate('LBL_CANCEL', $MODULE_NAME)}</button>
		</div>
	</form>
{/strip}
