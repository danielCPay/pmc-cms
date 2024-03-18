/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 *************************************************************************************/
'use strict';

Vtiger_List_Js(
	'Cases_List_Js',
	{},
	{
		validateMergeRecordsSpecial() {
			let searchParams = Vtiger_List_Js.getInstance().getSearchParams();
			let actionParams = {
				type: 'POST',
				url: 'index.php?module=Cases&action=VerifyMergeRecordsSpecial',
				async: false,
				data: searchParams
			};
			let result = false;

			AppConnector.request(actionParams)
				.done(function (data) {
					if (typeof(data) === 'string') {
						data = JSON.parse(data);
					}
					result = data.result;
				});

			return result;
		},
		registerMassActionsBtnMergeSpecialEvents() {
			this.getListViewContainer().on('click', '.js-mass-action--merge-special', (e) => {
				let url = $(e.target).data('url');
				if (typeof url !== 'undefined') {
					if (this.checkListRecordSelected(2) !== false) {
						this.noRecordSelectedAlert('JS_SELECT_ATLEAST_TWO_RECORD_FOR_MERGING');
					} else {
						let result = this.validateMergeRecordsSpecial();
						if (result.notify) {
							app.showNotify({
								text: result.notify,
								type: 'error'
							});
						} else {
							Vtiger_List_Js.triggerMassAction(url);
						}
					}
				}
			});
		},
		registerEvents: function () {
			this._super();
			this.registerMassActionsBtnMergeSpecialEvents();
		}
	}
);
