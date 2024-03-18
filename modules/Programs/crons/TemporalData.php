<?php

/**
 * Checks Programs and store Temporal Data for Claims
 *
 * @package   Cron
 *
 * @copyright DOT Systems sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Nichał Kamiński <mkaminski@dotsystems.pl>
 */

/**
 * Programs_TemporalData_Cron class.
 */
class Programs_TemporalData_Cron extends \App\CronHandler
{
  const SERVICE_NAME = 'LBL_PROGRAMS_TEMPORALDATA_HANDLER';

  /**
   * {@inheritdoc}
   */
  public function process(?int $id = null)
  {
    $service = \App\Request::_get('service');
    if (isset($id) || $service === self::SERVICE_NAME) {
      \App\Log::warning("Programs::cron::Programs_TemporalData_Cron:" . ($id ?: "NULL"));

      $programsQueryGenerator = (new \App\QueryGenerator('Programs'))
        ->setFields(['id'])
        ->addCondition('program_algorithm', 0, 'ny');
      if (isset($id)) {
        $programsQueryGenerator->addCondition('id', $id, 'e');
      }
      $programIds = $programsQueryGenerator
        ->createQuery()
        ->column();

      foreach ($programIds as $programId) {
        /** @var Programs_Record_Model $program */
        $program = Vtiger_Record_Model::getInstanceById($programId);
        $programAlgorithm = Vtiger_Record_Model::getInstanceById($program->get('program_algorithm'));
        $programAlgorithmNumber = $programAlgorithm->get('number');

        \App\Log::warning("Programs::cron::Programs_TemporalData_Cron:program = $programId/$programAlgorithmNumber");
        
        $claimsQueryGenerator = new \App\QueryGenerator('Claims');
        $claimsExist = $claimsQueryGenerator
          ->setFields(['id'])
          ->addRelatedCondition([
            'sourceField' => 'portfolio_purchase',
            'relatedModule' => 'PortfolioPurchases',
            'relatedField' => 'program',
            'value' => $programId,
            'operator' => 'eid',
          ])
          ->addCondition('claim_status', ['Open', 'Paid'], 'e')
          ->setLimit(1)
          ->createQuery()
          ->exists();
        
        if ($claimsExist) {
          $verificationWarnings = $program->get('verification_warnings');

          \App\Log::warning("Programs::cron::Programs_TemporalData_Cron:has claims, verificationWarnings = $verificationWarnings");

          if (!empty($verificationWarnings)) {
            $entry = Vtiger_Record_Model::getCleanInstance('BatchErrors');

            $programName = $program->get('program_name');

            $entry->set('task_type', 'Workflow');
            $entry->set('task_name', "Program $programName nightly process");
            $entry->set('mod_name', 'Programs');
            $entry->set('item', $programId);

            $entry->set('error_message', "Program $programName has invalid configuration, check Verification Warnings");
            $entry->set('error_description', "Verification Warnings are: $verificationWarnings");

            $entry->save();
          } else if ($programAlgorithmNumber === 'FF_STEP_UPS_AFTER_MONTHS') {
            // prepare thresholds
            $thresholds = $program->parseParametersForMonthStepUps();

            // current date
            $currentDate = date('Y-m-d');

            // get claims and process
            $claimsQueryGenerator = new \App\QueryGenerator('Claims');
            $ppAssigned = $claimsQueryGenerator->getQueryRelatedField('assigned_user_id:PortfolioPurchases:portfolio_purchase');
            $claims = $claimsQueryGenerator
              ->setFields(['id', 'purchased_time'])
              ->addRelatedField($ppAssigned->getRelated())
              ->addCondition('claim_status', ['Open', 'Paid'], 'e')
              ->addCondition('lock_automation', 0, 'e')
              ->addRelatedCondition([
                'sourceField' => 'portfolio_purchase',
                'relatedModule' => 'PortfolioPurchases',
                'relatedField' => 'program',
                'value' => $programId,
                'operator' => 'eid',
              ])
              ->createQuery();

            // \App\Log::warning($claims->createCommand()->getRawSql());

            $claims = $claims
              ->all();

            \App\Log::warning("Programs::cron::Programs_TemporalData_Cron:claims = " . count($claims));
            foreach ($claims as $claim) {
              $shouldSaveIfCorrect = false;

              $purchaseDate = $claim['purchased_time'];
              $dates = array_map(function ($threshold) use ($purchaseDate) { return date('Y-m-d', strtotime("+{$threshold['months']} months", strtotime($purchaseDate))); }, $thresholds);

              $pastThresholds = 0;
              for ($i = 0; $i < count($dates); $i++) {
                $date = $dates[$i];
                
                if ($date === $currentDate) {
                  $shouldSaveIfCorrect = true;
                } else if ($date >= $currentDate) {
                  break;
                }

                $pastThresholds = $i + 1;
              }

              \App\Log::warning("Programs::cron::Programs_TemporalData_Cron:claim = " . var_export(['id' => $claim['id'], 'dates' => $dates, 'pastT' => $pastThresholds], true));

              if ($shouldSaveIfCorrect) {
                $recordModel = Vtiger_Record_Model::getInstanceById($claim['id'], 'Claims');
                
                require_once 'modules/Claims/workflows/ClaimsWorkflow.php';
                ClaimsWorkflow::recalculateFinancialSummary($recordModel);
              }
            }
          }
        }
      }
    }
  }
}
