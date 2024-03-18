{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
<!-- tpl-Settings-Workflows-Tasks-VTToast -->
	<div class="row padding-bottom1per">
		<span class="col-md-2">{\App\Language::translate('LBL_TITLE', 'Settings:Workflows')}</span>
		<div class="col-md-10">
			<input name="title" class="form-control" type="text" value="{if isset($TASK_OBJECT->title)}{$TASK_OBJECT->title}{/if}">
		</div>
	</div>
	<div class="row padding-bottom1per">
		<span class="col-md-2">{\App\Language::translate('LBL_MESSAGE', 'Settings:Workflows')}</span>
		<div class="col-md-10">
			<textarea class="form-control messageContent" name="message" rows="3">
				{if isset($TASK_OBJECT->message)}
					{$TASK_OBJECT->message}
				{else}

				{/if}
			</textarea>
		</div>
	</div>
	<div class="row padding-bottom1per">
		<span class="col-md-2">{\App\Language::translate('LBL_LEVEL', 'Settings:Workflows')}</span>
		<div class="col-md-2 pb-3">
			<select class="select2 form-control" name="level" data-validation-engine="validate[required]">
				<option {if !isset($TASK_OBJECT->level) || $TASK_OBJECT->level eq 'info'}selected="selected"{/if}
						value="info">info</option>
				<option {if isset($TASK_OBJECT->level) && $TASK_OBJECT->level eq 'success'}selected="selected"{/if}
						value="success">success</option>
				<option {if isset($TASK_OBJECT->level) && $TASK_OBJECT->level eq 'error'}selected="selected"{/if}
						value="error">error</option>
			</select>
		</div>
	</div>
	<div class="row pb-3">
		<span class="col-md-2 col-form-label">{\App\Language::translate('LBL_STICKY', 'Settings:Workflows')}</span>
		<div class="col-md-2">
			<input type="checkbox" name="sticky" id="sticky" class="alignMiddle" {if !empty($TASK_OBJECT->sticky)} checked {/if}/>
		</div>
	</div>
<!-- /tpl-Settings-Workflows-Tasks-VTToast -->
{/strip}
