<?php

/**
 * ProgramsWorkflow.
 *
 * @package   Workflow
 *
 * @copyright DOT Systems
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Michał Kamiński <mkaminski@dotsystems.pl>
 */
class ProgramsWorkflow
{
  /**
	 * Verify algorithm parameters.
	 *
	 * @param \Programs_Record_Model $recordModel
	 */
	public static function verifyAlgorithmParameters(Vtiger_Record_Model $recordModel)
	{
		$id = $recordModel->getId();
    $programAlgorithmId = $recordModel->get('program_algorithm');
    $algorithmParameters = $recordModel->get('algorithm_parameters');
		\App\Log::warning("Programs::Workflows::verifyAlgorithmParameters:$id/$programAlgorithmId/$algorithmParameters");

    $programAlgorithm = Vtiger_Record_Model::getInstanceById($programAlgorithmId)->get('number');
    /*
    Jeżeli Program Algorithm = "FF step-ups after months", to:

      1. Składnia w polu Algorithm Parameters musi być zgodna z takim przykładem:
        up to 12 Months: 10%
        up to 18 Months: 15%
        more: 20%
        Liczba progów może być różna, ale musi być co najmniej jeden. Liczby miesięcy i procenty mogą być różne, ale teksty stałe muszą być stałe.
      2. Kolejne liczbny miesięcy i wartości procentowe muszą być rosnące
      3. Pierwsza wartość % w Algorithm Parameters musi być równa wartości pola Factor Fee %.

    Jeżeli coś się nie zgadza, to wpisać czytelnie dla zwykłego użytkownika opis problemu w polu Verification Warnings. Jeżeli wszystko ok, to wyczyścić wartość w tym polu.
    */
    $recordModel->set('verification_warnings', '');
    switch ($programAlgorithm) {
      case 'FF_STEP_UPS_AFTER_MONTHS':
        try {
          $recordModel->parseParametersForMonthStepUps();
        } catch (\Exception $e) {
          $recordModel->set('verification_warnings', $e->getMessage());
        }
        break;
    }

    $recordModel->save();
  }

  /**
	 * Debug temporal data cron
	 *
	 * @param \Programs_Record_Model $recordModel
	 */
	public static function debugTemporalDataCron(Vtiger_Record_Model $recordModel)
	{
		$id = $recordModel->getId();
    $programAlgorithmId = $recordModel->get('program_algorithm');
    $algorithmParameters = $recordModel->get('algorithm_parameters');
		\App\Log::warning("Programs::Workflows::debugTemporalDataCron:$id/$programAlgorithmId/$algorithmParameters");

    (new Programs_TemporalData_Cron(\vtlib\Cron::getInstance('LBL_PROGRAMS_TEMPORALDATA_HANDLER')))->process($id);
  }
}
