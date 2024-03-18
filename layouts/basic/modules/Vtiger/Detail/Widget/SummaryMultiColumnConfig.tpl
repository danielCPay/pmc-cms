{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="modal fade" tabindex="-1">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-modalAddWidget">
					{if !empty($WID)}<input type="hidden" name="wid" value="{$WID}" />{/if}
					<input type="hidden" name="type" value="{$TYPE}" />
					<div class="modal-header">
						<h5 id="massEditHeader" class="modal-title">{\App\Language::translate('Add widget', $QUALIFIED_MODULE)}</h5>
						<button type="button" data-dismiss="modal" class="close" title="{\App\Language::translate('LBL_CLOSE', $QUALIFIED_MODULE)}">&times;</button>
					</div>
					<div class="modal-body">
						<div class="modal-Fields">
							<div class="form-horizontal">
								<div class="form-group">
									<div class="col-md-3"><strong>{\App\Language::translate('Type widget', $QUALIFIED_MODULE)}</strong>:</div>
									<div class="col-md-7">
										{\App\Language::translate($TYPE, $QUALIFIED_MODULE)}
									</div>
								</div>
								<div class="form-group">
									<div class="col-md-3"><label class="col-form-label">{\App\Language::translate('Columns', $QUALIFIED_MODULE)}:</label></div>
									<div class="col-md-7">
										<div class="col-6 paddingLRZero">
											<input name="columns" class="form-control" type="number" min="2" max="4" placeholder="Columns" data-validation-engine="validate[required,min[2],max[4]]" value="{$WIDGETINFO['data']['columns']}" />
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
					{include file=\App\Layout::getTemplatePath('Modals/Footer.tpl', $QUALIFIED_MODULE) BTN_SUCCESS='LBL_SAVE' BTN_DANGER='LBL_CANCEL'}
				</form>
			</div>
		</div>
	</div>
{/strip}
