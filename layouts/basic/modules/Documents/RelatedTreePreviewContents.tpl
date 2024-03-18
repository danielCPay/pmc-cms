{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="m-1">
		<div class="mb-2">
			<div class="btn-group btn-group-toggle" data-toggle="buttons">
				<label class="btn btn-outline-primary{if !$FULL_TREE_SELECTED} active{/if}">
					<input class="js-switch--tree" type="radio" name="options" id="options-option1" data-js="change"
								autocomplete="off"{if !$FULL_TREE_SELECTED} checked{/if}
					> {\App\Language::translate('LBL_HIDE_EMPTY',$MODULE)}
				</label>
				<label class="btn btn-outline-primary{if $FULL_TREE_SELECTED} active{/if}">
					<input class="js-switch--tree" type="radio" name="options" id="options-option2" data-js="change"
								autocomplete="off"{if $FULL_TREE_SELECTED} checked{/if}
					> {\App\Language::translate('LBL_SHOW_EMPTY')}
				</label>
			</div>
		</div>
		<input type="hidden" name="tree" class="js-tree-data" value="{\App\Purifier::encodeHtml(\App\Json::encode($TREE))}" data-js="value">
		<input type="hidden" name="full_tree" class="js-full-tree-data" value="{\App\Purifier::encodeHtml(\App\Json::encode($FULL_TREE))}" data-js="value">
		{if $TREE or $FULL_TREE}
			<div class="col-md-12" id="treeContents"></div>
		{else}
			<h6 class="textAlignCenter padding20">{\App\Language::translate('LBL_RECORDS_NO_FOUND', $MODULE_NAME)}</h6>
		{/if}
	</div>
{/strip}
