<?php
/**
 * Verify merge cases action.
 *
 * @copyright DOT Systems sp. z o.o.
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Michał Kamiński <mkaminski@dotsystems.pl>
 */

/**
 * Verify merge cases class.
 */
class Cases_VerifyMergeRecordsSpecial_Action extends Vtiger_Mass_Action
{
	/**
	 * {@inheritdoc}
	 */
	public function checkPermission(App\Request $request)
	{
		if (!\App\Privilege::isPermitted($request->getModule(), 'Merge')) {
			throw new \App\Exceptions\NoPermitted('ERR_NOT_ACCESSIBLE', 406);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function process(App\Request $request)
	{
		$result = true;

    $recordModels = [];
		$queryGenerator = Vtiger_Mass_Action::getQuery($request);
		if ($queryGenerator) {
			$moduleModel = $queryGenerator->getModuleModel();
			foreach ($queryGenerator->getModuleFields() as $field) {
				if ($field->isEditable()) {
					$fields[] = $field->getName();
				}
			}
			$queryGenerator->setFields($fields);
			$queryGenerator->setField('id');
			$query = $queryGenerator->createQuery();
			$dataReader = $query->limit(\App\Config::performance('MAX_MERGE_RECORDS'))->createCommand()->query();
			while ($row = $dataReader->read()) {
				$recordModels[$row['id']] = $moduleModel->getRecordFromArray($row);
			}
			$dataReader->close();
		}

		$providerFields = Cases_MergeRecordsSpecial_View::getProviderFields();
    $providers = [];
		$types = [];
    foreach ($recordModels as $recordId => $recordModel) {
			$types[] = $recordModel->get('type_of_claim');

      foreach ($providerFields as $providerField) {
        $value = $recordModel->get($providerField);
        if (!empty($value)) {
          $providers[] = $value;
        }
      }
    }
    $providers = array_unique($providers);

		$validTypes = ['AOB', 'HO', 'LOP'];
		$message = '';
		// VALIDATIONS
		// type different than AOB, HO and LOP
		if (count(array_diff($types, $validTypes)) > 0) {
			$result = false;
			$message .= \App\Language::translate('MERGE_INVALID_TYPE_OF_CLAIM', $request->getModule()) . "<br/>";
		}

		// type equals AOB and different than AOB
		if (in_array('AOB', $types) && count(array_diff($types, ['AOB'])) > 0) {
			$result = false;
			$message .= \App\Language::translate('MERGE_AOB_AND_ANOTHER_TYPE', $request->getModule()) . "<br/>";
		}

		// type equals LOP and no case of type HO
		if (in_array('LOP', $types) && !in_array('HO', $types)) {
			$result = false;
			$message .= \App\Language::translate('MERGE_LOP_AND_NO_HO', $request->getModule()) . "<br/>";
		}

		// type equals HO and something other than LOP
		if (in_array('HO', $types) && count(array_diff($types, ['HO', 'LOP'])) > 0) {
			$result = false;
			$message .= \App\Language::translate('MERGE_HO_AND_ANOTHER_TYPE', $request->getModule()) . "<br/>";
		}

		// more than 1 HO
		if (count(array_intersect($types, ['HO'])) > 1) {
			$result = false;
			$message .= \App\Language::translate('MERGE_MORE_THAN_ONE_HO', $request->getModule()) . "<br/>";
		}

		// check if there are more than 5 providers
    if (count($providers) > count($providerFields)) {
      $result = false;
			$message .= \App\Language::translate('MERGE_TOO_MANY_PROVIDERS_SELECTED', $request->getModule()) . "<br/>";
    }

    $response = new Vtiger_Response();
		$response->setResult(['notify' => $message]);
		$response->emit();
	}
}
