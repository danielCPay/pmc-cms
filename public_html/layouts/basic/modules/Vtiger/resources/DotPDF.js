/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

$.Class('Vtiger_DotPDF_Js', {
	/*
	 * Function to register the click event for generate button
	 */
	registerPreSubmitEvent: function (container) {
		const self = this;
		container.find('#generate_dot_pdf').on('click', (e) => {
			e.preventDefault();
			
			const loader = $.progressIndicator({
				message: app.vtranslate('JS_PDF_GENERATING'),
				position: 'html',
				blockInfo: {
					enabled: true
				}
			});
			
			let templateElements = container.find('[name="pdf_template[]"]');
			let templateContainer = $(this).closest('.js-pdf-template-content');
			templateContainer.find('.js-pdf-user-variable').toggleClass('d-none');
	
			let templates = [];
			templateElements.filter(':checked').each(function () {
				templates.push($(this).val());
			});

			let params = container.find('form').serializeFormData();
			params.templates = templates;
			params.mode = 'generate';

			AppConnector.request(params)
				.done(function (data) {
					var response = data.result;
					if (response['message']) {
						app.showNotify({
							text: response['message'],
							type: data['success'] ? 'success' : 'error'
						});
					}

					let detailInstance = Vtiger_Detail_Js.getInstance();
					if (detailInstance) {
						detailInstance.reloadTabContent();
					}
				})
				.fail(function (data, err) {
					app.errorLog(data, err);
				});
		
			loader.progressIndicator({ mode: 'hide' });
			app.hideModalWindow();
		});
	},

	/**
	 * Register list view check records
	 *
	 * @param   {jQuery}  container
	 */
	registerListViewCheckRecords(container) {
		let templateElements = container.find('[name="pdf_template[]"]');
		templateElements.on('change', function () {
			document.progressLoader = $.progressIndicator({
				message: app.vtranslate('JS_PDF_RECALCULATING'),
				position: 'html',
				blockInfo: {
					enabled: true
				}
			});

			let templateContainer = $(this).closest('.js-pdf-template-content');
			templateContainer.find('.js-pdf-user-variable').toggleClass('d-none');

			let templates = [];
			templateElements.filter(':checked').each(function () {
				templates.push($(this).val());
			});

			let params = container.find('form').serializeFormData();
			params.mode = 'validateRecords';
			params.templates = templates;

			AppConnector.request(params)
				.done(function (data) {
					var response = data.result;
					if (data.success) {
						let valid = response.valid;
						let info = container.find('.js-records-info').text(response.message).removeClass('d-none');
						if (valid) {
							info.addClass('d-none');
						}
						setTimeout(function () {
							document.progressLoader.progressIndicator({ mode: 'hide' });
						}, 500);

						container.find('.js-submit-button').each(function () {
							$(this).attr('disabled', !valid);
						});
					}
				})
				.fail(function (data, err) {
					app.errorLog(data, err);
				});
		});
	},

	/**
	 * Register select custom columns change
	 */
	registerSelectCustomColumnsChange() {
		this.container.find('[name="isCustomMode"]').on('change', (ev) => {
			if ($(ev.target).is(':checked')) {
				this.container.find('[name="inventoryColumns[]"]').prop('disabled', null);
				this.container.find('.js-save-scheme').prop('disabled', null);
			} else {
				this.container.find('[name="inventoryColumns[]"]').prop('disabled', 'disabled');
				this.container.find('.js-save-scheme').prop('disabled', 'disabled');
			}
		});
	},

	/**
	 * Register save scheme button click
	 */
	registerSaveInventoryColumnSchemeClick() {
		this.container.find('.js-save-scheme').on('click', (e) => {
			e.preventDefault();
			e.stopPropagation();
			const loader = $.progressIndicator({
				position: 'html',
				blockInfo: {
					enabled: true
				}
			});

			let params = this.container.find('form').serializeFormData();
			params.mode = 'saveInventoryColumnScheme';
			params.isCustomMode = this.container.find('[name="isCustomMode"]').is(':checked');

			AppConnector.request(params)
				.done(function (data) {
					const response = data['result'];
					if (data['success']) {
						loader.progressIndicator({ mode: 'hide' });
					}
					if (response['message'] && data['success']) {
						app.showNotify({
							text: response['message'],
							type: 'success'
						});
					}
				})
				.fail(function (data, err) {
					app.errorLog(data, err);
				});
		});
	},
	registerListViewFilter: function (container) {
		const self = this;
		container.find('.templates-filter').on('keyup', (e) => {
			const element = $(e.currentTarget);
			const filter = element.val().toLowerCase();
			const classes = element.attr('class').split(/\s+/);
			let column = 'column-name';
			for (let className of classes) {
				if (className.startsWith('column-')) {
					column = className;
					break;
				}
			}
			container.find('.js-pdf-template-content.row').each(function () {
				const element = $(this);
				const labelElement = element.find('label.' + column);
				const label = labelElement.text();
				if (!filter || label.toLowerCase().indexOf(filter) !== -1) {
					element.removeClass('d-none');
				} else {
					element.addClass('d-none');
				}
			});
		});
	},
	/**
	 * Register events
	 */
	registerEvents() {
		const container = (this.container = $('#dotPdfExportModal').closest('.js-modal-container'));
		this.dynamicTemplatesCount = 0;
		this.recordId = parseInt(container.find('[name="record"]').val());
		this.registerPreSubmitEvent(container);
		this.registerSaveInventoryColumnSchemeClick();
		this.registerSelectCustomColumnsChange();
		this.registerListViewCheckRecords(container);
		this.registerListViewFilter(container);
	}
});
$(function () {
	new Vtiger_DotPDF_Js().registerEvents();
});
