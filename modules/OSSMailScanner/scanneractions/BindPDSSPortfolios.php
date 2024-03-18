<?php

require_once 'modules/com_vtiger_workflow/include.php';

/**
 * Mail scanner action bind Bizon modules.
 *
 * @copyright DOT Systems Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Michał Kamiński <mkaminski@dotsystems.pl>
 */
class OSSMailScanner_BindPDSSPortfolios_ScannerAction extends OSSMailScanner_PrefixScannerAction_Model
{
  /**
	 * Module name.
	 *
	 * @var string
	 */
	public $moduleName = 'Portfolios';

	/**
	 * Table name.
	 *
	 * @var string
	 */
	public $tableName = 'u_yf_portfolios';

	/**
	 * Table column.
	 *
	 * @var string
	 */
	public $tableColumn = 'portfolio_id';

  public function process(OSSMail_Mail_Model $mail)
	{
		$this->mail = $mail;

    \App\Log::warning("OSSMailScanner::BindPDSSPortfolios:". print_r([
      'uniqueId' => $this->mail->getUniqueId(), 
      'crmId' => $this->mail->getMailCrmId(), 
      'folder' => $this->mail->getFolder(), 
      'type' => $this->mail->getTypeEmail(), 
      'owner' => $this->mail->getAccountOwner(), 
      'to' => $this->mail->get('to_email'), 
      'from' => $this->mail->get('from_email'), 
      'subject' => $this->mail->get('subject'), 
      'date' => $this->mail->get('date')
    ], true));

    $portfolioIds = $this->findAndBind(false);

		if (!empty($portfolioIds)) {
      $mail = Vtiger_Record_Model::getInstanceById($this->mail->getMailCrmId());
			$shareWith = explode(',', $mail->get('shownerid'));

			foreach ($portfolioIds as $matchId) {
				$match = Vtiger_Record_Model::getInstanceById($matchId);
				
				// get Assigned To, append to Share with
				$shareWith[] = $match->get('assigned_user_id');
			}
			
			if (!empty($shareWith)) {
				$mail->set('shownerid', array_unique(array_filter($shareWith)));
				$mail->save();
			}

			if ($this->mail->getTypeEmail() != 'Sent') {
				$wfs = new VTWorkflowManager();

				$workflow = $wfs->retrieveByName('New e-mail received (Portfolio)', 'Portfolios');
				
				foreach (array_unique($portfolioIds) as $pId) {
					$recordModel = Vtiger_Record_Model::getInstanceById($pId);

					\App\Log::info("New mail received for Portfolio $pId");

					$workflow->performTasks($recordModel);
				}
			}
    }

    return $portfolioIds;
	}
}
