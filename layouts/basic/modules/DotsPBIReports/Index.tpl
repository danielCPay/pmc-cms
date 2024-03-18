{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{if !$NO_REPORTS && empty($REPORTS)}
<script>
	async function updateToken() {
		// generate new token
		let newToken = await AppConnector.request({
			module: 'DotsPBIReports',
			action: 'GetToken',
			record: {$RECORD_ID},
		});

		if (newToken.success) {
			// Get a reference to the embedded report HTML element
			let embedContainer = $('#embed-container')[0];

			// Get a reference to the embedded report.
			let report = powerbi.get(embedContainer);

			// Set the new access token
			await report.setAccessToken(newToken.result.token);

			setTimeout(updateToken, 50000);
		} else {
			app.showNotify({
				text: app.vtranslate('LBL_DOTS_PBI_REPORTS_COULDNT_REFRESH_TOKEN'),
				type: 'error'
			});
		}
	}

	function report() {
		window.App.Components.Scrollbar.active = false;

		// 1 - Get DOM object for div that is report container 
    let reportContainer = $("#embed-container")[0];

    // 2 - Get report embedding data from view model
    let reportId = '{$REPORT_ID}';
    let embedUrl = '{$EMBED_URL}';
    let token = '{$EMBED_TOKEN}';
		let additionalConfiguration = {$ADDITIONAL_CONFIGURATION};

    // 3 - Embed report using the Power BI JavaScript API.
    let models = window['powerbi-client'].models;

	{if $ACCESS_LEVEL == 0}
    let config = {
      type: 'report',
      id: reportId,
      embedUrl: embedUrl,
      accessToken: token,
      permissions: models.Permissions.Read,
      tokenType: models.TokenType.Embed,
      viewMode: models.ViewMode.View,
			settings: {
				layoutType: models.LayoutType.Custom,
				customLayout: {
					pageSize: {
							type: models.PageSizeType.Widescreen
					},
					displayOption: models.DisplayOption.FitToPage,
				},
				panes: {
					bookmarks: {
						visible: false
					},
					fields: {
						visible: false
					},
					filters: {
						expanded: false,
						visible: true
					},
					pageNavigation: {
						visible: false
					},
					selection: {
						visible: false
					},
					syncSlicers: {
						visible: false
					},
					visualizations: {
						visible: false
					}
				},
				bars: {
					actionBar: {
						visible: false
					}
				},
				background: models.BackgroundType.Transparent,
				hideErrors: true,
				localeSettings: {
					language: "en",
					formatLocale: "US"
				},
			},
    };
	{else}
		let config = {
      type: 'report',
      id: reportId,
      embedUrl: embedUrl,
      accessToken: token,
      permissions: models.Permissions.All,
      tokenType: models.TokenType.Embed,
      viewMode: models.ViewMode.Edit,
			settings: {
				layoutType: models.LayoutType.Custom,
				customLayout: {
					pageSize: {
							type: models.PageSizeType.Widescreen
					},
					displayOption: models.DisplayOption.FitToPage,
				},
				panes: {
					bookmarks: {
						visible: false
					},
					fields: {
						expanded: false,
						visible: true
					},
					filters: {
						expanded: false,
						visible: true
					},
					pageNavigation: {
						visible: true
					},
					selection: {
						visible: false
					},
					syncSlicers: {
						visible: false
					},
					visualizations: {
						expanded: false,
						visible: true
					}
				},
				bars: {
					actionBar: {
						visible: true
					}
				},
				background: models.BackgroundType.Transparent,
				hideErrors: true,
				localeSettings: {
					language: "en",
					formatLocale: "US"
				},
			},
    };
	{/if}

		config = $.extend(config, additionalConfiguration);

    // Embed the report and display it within the div container.
    let report = powerbi.embed(reportContainer, config);

		{if false}
			// show available pages
			// setTimeout(async function () { console.log("Pages"); const pages = await report.getPages(); console.log(pages); console.log("After pages"); }, 10000 );
		{/if}

    // 4 - Add logic to resize embed container on window resize event
		let heightBuffer = 0;
    let newHeight = $(window).height() - $('.js-header').innerHeight() - $('.js-footer').innerHeight() - heightBuffer;
    $("#embed-container").height(newHeight);
    $(window).resize(function () {
      let newHeight = $(window).height() - $('.js-header').innerHeight() - $('.js-footer').innerHeight() - heightBuffer;
      $("#embed-container").height(newHeight);
    });

		setTimeout(updateToken, 50000);
	}

	$(document).ready(report);
</script>
<div id="embed-container" style="height:800px;"></div>
{else if $NO_REPORTS}
	<table class="emptyRecordsDiv">
		<tbody>
		<tr>
			<td>{\App\Language::translate('LBL_NO_REPORTS_FOUND', $MODULE_NAME)}</td>
		</tr>
		</tbody>
	</table>
{else if !empty($REPORTS)}
	<div class="o-breadcrumb widget_header row mb-2">
		<div class="col-md-12">
			{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE_NAME)}
		</div>
	</div>
	<div class="col-sm-12">
		<table class="table table-bordered table-sm dataTable no-footer">
			<colgroup>
				<col width="75%">
				<col width="25%">
			</colgroup>
			<thead>
				<tr role="row">
					<th class="">{\App\Language::translate('LBL_REPORT', $MODULE_NAME)}</th>
					<th class="">{\App\Language::translate('LBL_EDITABLE', $MODULE_NAME)}?</th>
				</tr>
			</thead>
			<tbody>
			{foreach item=REPORT from=$REPORTS}
				<tr class="u-cursor-pointer" onclick="window.location.href = '{$REPORT['url']}'">
					<td>
						{\App\Language::translate($REPORT['name'], $MODULE_NAME)}
					</td>
					{if $REPORT['access_level'] == 0}
						<td class="text-center"><span class="fas fa-times"></span>&nbsp;<span class="d-none">0</span></td>
					{else}
						<td class="text-center"><span class="fas fa-check"></span>&nbsp;<span class="d-none">1</span></td>
					{/if}
				</tr>
			{/foreach}
			</tbody>
		</table>
	</div>
{/if}
