<?php
/**
 * Merge cases action.
 *
 * @copyright DOT Systems sp. z o.o.
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Michał Kamiński <mkaminski@dotsystems.pl>
 */

/**
 * Merge cases class.
 */
class Cases_MergeRecordsSpecial_Action extends Vtiger_MergeRecords_Action
{
	/**
	 * {@inheritdoc}
	 */
	public function process(App\Request $request)
	{
		$moduleModel = Vtiger_Module_Model::getInstance($request->getModule());
		$primaryRecord = $request->getInteger('record');
		$primaryRecordModel = Vtiger_Record_Model::getInstanceById($primaryRecord);

    // get instances of all records, if one is HO select it as base
    $migrate = [];
    $records = [];
    $recordModels = [];
    $recordModels[$primaryRecord] = $primaryRecordModel;
    foreach ($request->getArray('records', 'Integer') as $record) {
      $records[] = $record;

			if ($record !== $primaryRecord) {
				$migrate[$record] = [];
        $recordModels[$record] = Vtiger_Record_Model::getInstanceById($record);
			}

      $type = $recordModels[$record]->get('type_of_claim');
      if ($type === 'HO') {
        $mergeType = 'HO';
        unset($migrate[$record]);
        $migrate[$primaryRecord] = [];

        $primaryRecord = $record;
        $primaryRecordModel = $recordModels[$record];
      } else if ($type === 'AOB') {
        $mergeType = 'AOB';
      }
		}

    \App\Log::warning("Cases::MergeRecordsSpecial:PRIMARY = $primaryRecord, MERGE = $mergeType");

    if ($mergeType !== 'HO') {
      foreach ($moduleModel->getFields() as $field) {
        if ($request->has($field->getName()) && $request->getInteger($field->getName()) !== $primaryRecord && $field->isEditable()) {
          $migrate[$request->getInteger($field->getName())][$field->getName()] = $field->getName();
        }
      }
    }

    $result = false;

    $serviceTypes = explode(' |##| ', $primaryRecordModel->get('types_of_services'));

    $providerFields = Cases_MergeRecordsSpecial_View::getProviderFields();
    $unsetProviderFields = [];
    $currentProviders = [];
    // read all set providers in primary record, with set fields
    foreach ($providerFields as $providerField) {
      $value = $primaryRecordModel->get($providerField);
      if (!empty($value)) {
        $currentProviders[] = $value;
      } else {
        $unsetProviderFields[] = $providerField;
      }
    }
    // find all other providers (array_diff), set in free fields
    foreach (array_keys($migrate) as $recordId) {
      $recordModel = $recordModels[$recordId];

      if ($mergeType === 'AOB') {
        foreach ($providerFields as $providerField) {
          $value = $recordModel->get($providerField);
          if (!empty($value) && !\in_array($value, $currentProviders)) {
            $unsetProviderField = array_shift($unsetProviderFields);
            if (empty($unsetProviderField)) {
              throw new \App\Exceptions\IllegalValue("Too many new providers");
            }
            $migrate[$recordId][$providerField] = $unsetProviderField;
            $currentProviders[] = $value;
          }
        }
      }

      array_push($serviceTypes, ...explode(' |##| ', $recordModel->get('types_of_services')));
    }
    $serviceTypes = array_unique($serviceTypes);

		try {
      $transaction = \App\Db::getInstance()->beginTransaction();
      try {
        \App\RecordTransfer::recordData($primaryRecordModel, $migrate);
        $primaryRecordModel->set('types_of_services', implode(' |##| ', $serviceTypes));
        $primaryRecordModel->ext['modificationType'] = \ModTracker_Record_Model::TRANSFER_EDIT;
        $primaryRecordModel->save();
        \App\RecordTransfer::relations($primaryRecord, array_keys($migrate));
        $transaction->commit();
      } catch (\Throwable $ex) {
        $transaction->rollBack();
        throw $ex;
      }

			foreach (array_keys($migrate) as $recordId) {
				$recordModel = \Vtiger_Record_Model::getInstanceById($recordId);
				$recordModel->ext['modificationType'] = ModTracker_Record_Model::TRANSFER_DELETE;
				$recordModel->changeState('Trash');
			}

      /** @var Cases_Record_Model $primaryRecordModel */
      $primaryRecordModel = Vtiger_Record_Model::getInstanceById($primaryRecordModel->getId());
      $primaryRecordModel->recalculateAll();

			$result = true;
		} catch (\Throwable $ex) {
			\App\Log::error($ex->__toString());
		}
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}
}
