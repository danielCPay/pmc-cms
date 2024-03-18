/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

Vtiger_Detail_Js(
	'OSSMailView_Detail_Js',
	{
		printMail: function () {
			let content = window.open();
			let preview = $('.emailPreview > div');
			if (preview.length) {
				var subject = $('#emailPreview_Subject').html();
				var from = $('#emailPreview_From').html();
				var to = $('#emailPreview_To').html();
				var cc = $('#emailPreview_Cc').html();
				var date = $('#emailPreview_Date').html();
				var attachments = '';
				$('#emailPreview_attachment a').each(function () {
					attachments += '<br /><span style="padding-left: 15px">' + $(this).text() + '</span>';
				});
				var body = $('#emailPreview_Content iframe').attr('srcdoc');
			} else {
				var subject = $('#subject').val();
				var from = $('#from_email').val();
				var to = $('#to_email').val();
				var cc = $('#cc_email').val();
				var date = jQuery('#createdtime').val();
				var body = $('#content').val();
			}

			content.document.write('<b>' + app.vtranslate('Subject') + ': ' + subject + '</b><br />');
			content.document.write('<br />' + app.vtranslate('From') + ': ' + from + '<br />');
			content.document.write(app.vtranslate('To') + ': ' + to + '<br />');
			if (cc) {
				content.document.write(app.vtranslate('CC') + ': ' + cc + '<br />');
			}
			content.document.write(app.vtranslate('Date') + ': ' + date + '<br />');
			if (attachments) {
				content.document.write(app.vtranslate('Attachments') + ': ' + attachments + '<br />');
			}
			content.document.write('<hr/>' + body + '<br />');
			content.print();
		}
	},
	{
		registerEvents: function () {
			this._super();
			Vtiger_Index_Js.registerMailButtons($('.detailViewContainer .js-btn-toolbar'));
		}
	}
);
