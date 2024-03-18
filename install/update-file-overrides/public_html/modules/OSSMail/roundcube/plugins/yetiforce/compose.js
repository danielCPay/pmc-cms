'use strict';
/* {[The file is published on the basis of MIT License]} */
if (window.rcmail) {
	rcmail.addEventListener('init', function () {
		rcmail.crm = rcmail.getCrmWindow();
		if (rcmail.crm != false) {
			rcmail.env.compose_commands.push('yetiforce.addFilesFromCRM');
			rcmail.env.compose_commands.push('yetiforce.selectTemplate');
			rcmail.env.compose_commands.push('yetiforce.selectAdress');
			rcmail.env.compose_commands.push('yetiforce.selectLabel');
			rcmail.env.compose_commands.push('yetiforce.chooseRecord');
			rcmail.env.compose_commands.push('yetiforce.refreshTemplate');
			rcmail.register_command(
				'yetiforce.addFilesFromCRM',
				function () {
					rcmail.addFilesFromCRM();
				},
				true
			);
			rcmail.register_command(
				'yetiforce.selectTemplate',
				function () {
					rcmail.selectTemplate();
				},
				true
			);
			rcmail.register_command(
				'yetiforce.selectAdress',
				function (module, part) {
					rcmail.selectAdress(module, part);
				},
				true
			);
			rcmail.register_command(
				'yetiforce.selectLabel',
				function (module, part) {
					rcmail.selectLabel(module, part);
				},
				true
			);
			rcmail.register_command(
				'yetiforce.chooseRecord',
				function (module, part) {
					rcmail.chooseRecord(module, part);
				},
				true
			);
			rcmail.register_command(
				'yetiforce.refreshTemplate',
				function (module, part) {
					rcmail.refreshTemplate();
				},
				true
			);

			if (rcmail.env.yf_crmRecordLabel) {
				$('#baseRecordId').val(rcmail.env.yf_crmRecord);
				$('#baseRecordModule').val(rcmail.env.yf_crmModule);
				$('#baseRecordLink').text(rcmail.env.yf_crmRecordLabel);
				$('#baseRecordLink').attr('title', rcmail.env.yf_crmRecordLabel);
				$('#baseRecordLink').attr('href', rcmail.crm.CONFIG.siteUrl + 'index.php?module=' + rcmail.env.yf_crmModule + '&view=Detail&record=' + rcmail.env.yf_crmRecord);
				if (!$('#baseRecordEmpty').hasClass('d-none')) {
					$('#baseRecordEmpty').addClass('d-none');
				}
				$('#baseRecordLink').removeClass('d-none');
			}
		} else {
			jQuery('.yetiforce').hide();
		}
	});
}
//Document selection
rcube_webmail.prototype.addFilesFromCRM = function () {
	rcmail.crm.app.showRecordsList(
		{
			module: 'Documents',
			src_module: 'Documents',
			multi_select: true,
			additionalInformations: true,
			search_params: [[['filelocationtype', 'e', 'I']]]
		},
		(modal, instance) => {
			instance.setSelectEvent((responseData) => {
				rcmail.addFilesToMail({
					ids: Object.keys(responseData)
				});
			});
		}
	);
};
//Add files to mail
rcube_webmail.prototype.addFilesToMail = function (data) {
	data._id = rcmail.env.compose_id;
	data._uploadid = new Date().getTime();
	this.http_post('plugin.yetiforce-addFilesToMail', data, this.set_busy(true, 'loading'));
};
// Select template
rcube_webmail.prototype.selectTemplate = function () {
	rcmail.crm.app.showRecordsList(
		{
			module: 'EmailTemplates',
			src_module: 'EmailTemplates',
		},
		(modal, instance) => {
			instance.setSelectEvent((responseData) => {
				var recordId = $('#baseRecordId').val() || rcmail.env.yf_crmRecord,
					module = $('#baseRecordModule').val() || rcmail.env.yf_crmModule,
					view = rcmail.env.yf_crmView;
				if (view == 'List') {
					var chElement = jQuery(crm.document).find('.listViewEntriesCheckBox')[0];
					recordId = jQuery(chElement).val();
				}
				jQuery.ajax({
					type: 'Get',
					url: '?_task=mail&_action=plugin.yetiforce-getContentEmailTemplate&_id=' + rcmail.env.compose_id,
					data: {
						id: responseData.id,
						record_id: recordId,
						select_module: module
					},
					dataType: 'json',
					success: function (data) {
						$('#currentTemplate').val(responseData.id);
						let html = jQuery('<div/>').html(data.content).html(),
							ed = '';
						jQuery('[name="_subject"]').val(data.subject);
						if (window.tinyMCE && (ed = tinyMCE.get(rcmail.env.composebody))) {
							tinymce.activeEditor.setContent(html);
						} else {
							jQuery('#composebody').val(html);
						}
						jQuery('[name="_to"]').val(data.to);
						jQuery('[name="_to"]').change();
						jQuery('[name="_cc"]').val(data.cc);
						jQuery('[name="_cc"]').change();
						$('[href="#delete"]').each(function () {
							$(this).trigger('click');
						});
						if (typeof data.attachments !== 'undefined' && data.attachments !== null) {
							rcmail.addFilesToMail(data.attachments);
						}
					}
				});
			});
		}
	);
};
rcube_webmail.prototype.selectAdress = function (module, part) {
	rcmail.crm.app.showRecordsList(
		{
			module: module,
			src_module: 'OSSMail',
			multi_select: true,
			additionalInformations: false
		},
		(modal, instance) => {
			instance.setSelectEvent((responseData, e) => {
				rcmail.getEmailAddresses(responseData, e, module).done((emails) => {
					if (emails.length) {
						let paetElement = $('#' + part);
						let value = paetElement.val();
						if (value != '' && value.charAt(value.length - 1) != ',') {
							value = value + ',';
						}
						paetElement.val(value + emails.join(','));
						paetElement.change();
					} else {
						rcmail.crm.app.showNotify({
							text: rcmail.crm.app.vtranslate('NoFindEmailInRecord'),
							animation: 'show'
						});
					}
				});
			});
		}
	);
};
rcube_webmail.prototype.getEmailAddresses = function (responseData, e, module) {
	let aDeferred = $.Deferred(),
		emails = [],
		label = '',
		email = '';
	if (
		typeof e.target !== 'undefined' &&
		($(e.target).data('type') === 'email' || $(e.target).data('type') === 'multiEmail')
	) {
		emails.push($(e.target).text());
		aDeferred.resolve(emails);
	} else {
		let i = 0;
		for (let id in responseData) {
			rcmail.crm.app
				.getRecordDetails({
					record: id,
					module: module,
					fieldType: ['email', 'multiEmail']
				})
				.done((data) => {
					i++;
					label = email = rcmail.getFirstEmailAddress(data.result.data);
					if (responseData[id]) {
						label = responseData[id];
					}
					emails.push(label + '<' + email + '>');
					if (i === Object.keys(responseData).length) {
						//last iteration
						aDeferred.resolve(emails);
					}
				});
		}
	}
	return aDeferred.promise();
};
rcube_webmail.prototype.getFirstEmailAddress = function (data) {
	let emails = [];
	for (let key in data) {
		if (data[key]) {
			if (rcmail.crm.app.isJsonString(data[key])) {
				let multiEmail = JSON.parse(data[key]);
				for (let i in multiEmail) {
					emails.push(multiEmail[i].e);
				}
				break;
			} else {
				emails.push(data[key]);
				break;
			}
		}
	}
	return emails;
};
rcube_webmail.prototype.selectLabel = function (module, part) {
	rcmail.crm.app.showRecordsList(
		{
			module: module,
			src_module: 'OSSMail',
			multi_select: true,
			additionalInformations: false
		},
		(modal, instance) => {
			instance.setSelectEvent((responseData, e) => {
				const labels = []
				for (let id in responseData) {
					labels.push('[' + responseData[id] + ']');
				}
				let ed = undefined;
				if (part === 'composebody' && window.tinyMCE && (ed = tinyMCE.get(rcmail.env.composebody))) {
					ed.execCommand('mceInsertContent', false, labels.join(', '));
				} else {
					let partElement = $('#' + part);
					const caretPos = partElement.get(0).selectionStart;
					const caretEnd = partElement.get(0).selectionEnd;
					const oldText = partElement.val();
					const txtToAdd = labels.join(', ');
					partElement.val(oldText.substring(0, caretPos) + txtToAdd + oldText.substring(caretEnd));

					partElement.get(0).selectionStart = caretPos + txtToAdd.length;
					partElement.get(0).selectionEnd = caretPos + txtToAdd.length;
					partElement.focus();
					partElement.change();
				}
			});
		}
	);
};
rcube_webmail.prototype.chooseRecord = function (module, part) {
	rcmail.crm.app.showRecordsList(
		{
			module: module,
			src_module: 'OSSMail',
			multi_select: false,
			additionalInformations: true
		},
		(modal, instance) => {
			instance.setSelectEvent((responseData, e) => {
				$('#baseRecordId').val(responseData.id);
				$('#baseRecordModule').val(module);
				$('#baseRecordLink').text(responseData.name);
				$('#baseRecordLink').attr('title', responseData.name);
				$('#baseRecordLink').attr('href', rcmail.crm.CONFIG.siteUrl + 'index.php?module=' + module + '&view=Detail&record=' + responseData.id);
				if (!$('#baseRecordEmpty').hasClass('d-none')) {
					$('#baseRecordEmpty').addClass('d-none');
				}
				$('#baseRecordLink').removeClass('d-none');

				let templateId = $('#currentTemplate').val();
				if (templateId > 0) {
					jQuery.ajax({
						type: 'Get',
						url: '?_task=mail&_action=plugin.yetiforce-getContentEmailTemplate&_id=' + rcmail.env.compose_id,
						data: {
							id: templateId,
							record_id: responseData.id,
							select_module: module
						},
						dataType: 'json',
						success: function (data) {
							let html = jQuery('<div/>').html(data.content).html(),
								ed = '';
							jQuery('[name="_subject"]').val(data.subject);
							if (window.tinyMCE && (ed = tinyMCE.get(rcmail.env.composebody))) {
								tinymce.activeEditor.setContent(html);
							} else {
								jQuery('#composebody').val(html);
							}
							jQuery('[name="_to"]').val(data.to);
							jQuery('[name="_to"]').change();
							jQuery('[name="_cc"]').val(data.cc);
							jQuery('[name="_cc"]').change();
							$('[href="#delete"]').each(function () {
								$(this).trigger('click');
							});
							if (typeof data.attachments !== 'undefined' && data.attachments !== null) {
								rcmail.addFilesToMail(data.attachments);
							}
						}
					});
				}
			});
		}
	);
};
rcube_webmail.prototype.refreshTemplate = function () {
	let templateId = $('#currentTemplate').val();
	let recordId = $('#baseRecordId').val();
	let module = $('#baseRecordModule').val();

	if (templateId > 0 && recordId > 0 && !!module) {
		jQuery.ajax({
			type: 'Get',
			url: '?_task=mail&_action=plugin.yetiforce-getContentEmailTemplate&_id=' + rcmail.env.compose_id,
			data: {
				id: templateId,
				record_id: recordId,
				select_module: module
			},
			dataType: 'json',
			success: function (data) {
				let html = jQuery('<div/>').html(data.content).html(),
					ed = '';
				jQuery('[name="_subject"]').val(data.subject);
				if (window.tinyMCE && (ed = tinyMCE.get(rcmail.env.composebody))) {
					tinymce.activeEditor.setContent(html);
				} else {
					jQuery('#composebody').val(html);
				}
				jQuery('[name="_to"]').val(data.to);
				jQuery('[name="_to"]').change();
				jQuery('[name="_cc"]').val(data.cc);
				jQuery('[name="_cc"]').change();
				$('[href="#delete"]').each(function () {
					$(this).trigger('click');
				});
				if (typeof data.attachments !== 'undefined' && data.attachments !== null) {
					rcmail.addFilesToMail(data.attachments);
				}
			}
		});
	}
};
rcube_webmail.prototype.getCrmWindow = function () {
	if (opener !== null && typeof opener.parent.CONFIG == 'object') {
		return opener.parent;
	} else if (parent !== null && typeof parent.CONFIG == 'object') {
		return parent;
	} else if (parent !== null && parent.opener !== null && typeof parent.opener.CONFIG == 'object') {
		return parent.opener;
	} else if (parent !== null && parent.parent !== null && typeof parent.parent.CONFIG == 'object') {
		return parent.parent;
	} else if (opener !== null && opener.crm !== null && typeof opener.crm == 'object' && typeof opener.crm.CONFIG == 'object') {
		return opener.crm;
	}
	return false;
};
