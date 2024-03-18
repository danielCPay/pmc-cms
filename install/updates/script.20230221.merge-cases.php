<?php
require_once 'include/main/WebUI.php';

\App\User::setCurrentUserId(\App\User::getActiveAdminId());

use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
$reader = new Xlsx();
// $spreadsheet = $reader->load('PDC Combo Over 15K.xlsx');
$spreadsheet = $reader->load('HOS-PDC Combo.xlsx');
$ws = $spreadsheet->getSheetByName('Sheet1');

$ci = 2;
$groups = [];
while (!empty($claimNumber = $ws->getCellByColumnAndRow($ci, 1)->getValue())) {
  $groups[$claimNumber] = [];

	$ri = 2;
	while (!empty($caseId = $ws->getCellByColumnAndRow($ci, $ri++)->getValue())) {
		$groups[$claimNumber][] = $caseId;
	}

	$ci += 1;
} while (!empty($claimNumber));

$mergedCases = "";
$moduleModel = Vtiger_Module_Model::getInstance('Cases');
foreach ($groups as $claimNumber => $caseIds) {
	// if (\in_array($claimNumber, ['103738086', '201572968', 'H83650'])) {
	// 	continue;
	// }

	// materialize cases, filter non existing
	$cases = [];
  $baseCase = null;
  $baseCaseId = null;
  $minCaseDate = null;
  $minCaseId = null;
	foreach ($caseIds as $caseId) {
		$caseId = trim($caseId);
		$crmId = \App\Record::getCrmIdByLabel('Cases', $caseId);

		// if (empty($crmId) || !\App\Record::isExists($crmId)) {
		// 	echo "Merging cases for claim number $claimNumber (cases " . implode(', ', $caseIds) . ")" . PHP_EOL;
		// 	echo "  ERROR: $caseId didn't return a record" . PHP_EOL;
		// 	continue 2;
		// }
		if (empty($crmId) || !\App\Record::isExists($crmId)) {
			// ignore removed
			continue;
		}

		$cases[] = $case = Vtiger_Record_Model::getInstanceById($crmId, 'Cases');
    
    // select oldest case as base
    $caseDate = $case->get('createdtime');
    $caseId = substr($case->get('case_id'), 3, 2) . substr($case->get('case_id'), 6);
		$caseNumbers[] = trim($case->get('case_number')) ?: 'empty';
    if (/*/str_starts_with(trim($case->get('case_id')), 'HOS') && /**/($minCaseDate === null || $caseDate < $minCaseDate)) {
      $baseCase = $case;
      $minCaseDate = $caseDate;
    }
    if (/*/str_starts_with(trim($case->get('case_id')), 'HOS') && /**/($minCaseId === null || $caseId < $minCaseId)) {
      $baseCaseId = $case;
      $minCaseId = $caseId;
    }
	}

	if ($baseCase->getId() !== $baseCaseId->getId() && $baseCase->get('createdtime') < '2022-05-01 00:00:00' && $baseCaseId->get('createdtime') < '2022-05-01 00:00:00') {
		$baseCase = $baseCaseId;
	}

	$caseNumbers = [];
	foreach ($cases as $case) {
		$caseNumber = trim($case->get('case_number'));

		if (!empty($caseNumber) || $case->getId() === $baseCaseId->getId()) {
			$caseNumbers[] = $caseNumber ?: 'empty';
		}
	}
	$caseNumbers = array_unique($caseNumbers);

	if (empty($baseCase)) {
		echo "  ERROR: no case could be selected as base for claim number $claimNumber" . PHP_EOL;
    continue;
	} else if (count($caseNumbers) !== 1) {
		if (empty(trim($baseCase->get('case_number')))) {
			echo "  ERROR: base case (" . $baseCase->get('case_id') . ") has empty case number, but some other case has case number set for claim number $claimNumber" . PHP_EOL;
			continue;
		} else {
			echo "  ERROR: multiple case numbers (" . join(',', $caseNumbers) . ") for claim number $claimNumber" . PHP_EOL;
			continue;
		}
	}

	// remove baseCase from cases
	$cases = array_filter($cases, function ($el) use ($baseCase) { return $el->get('case_id') !== $baseCase->get('case_id'); });

	// check any cases to merge
	if (empty($cases)) {
		// echo "  ERROR: no cases to merge for base case " . $baseCase->get('case_id') . " for claim number $claimNumber" . PHP_EOL;
		continue;
	}

	// merge cases
	try {
		$primaryRecord = $baseCase->getId();
		$migrate = [];
		$result = false;
		foreach (array_map(function ($el) { return $el->getId(); }, $cases) as $record) {
			if ($record !== $primaryRecord) {
				$migrate[$record] = [];
			}
		}

    $serviceTypes = [];
    $primaryRecordModel = Vtiger_Record_Model::getInstanceById($primaryRecord);
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
		sort($unsetProviderFields);
    array_push($serviceTypes, ...explode(' |##| ', $primaryRecordModel->get('types_of_services')));
    // find all other providers (array_diff), set in free fields
    foreach (array_keys($migrate) as $recordId) {
      $recordModel = Vtiger_Record_Model::getInstanceById($recordId);
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
      array_push($serviceTypes, ...explode(' |##| ', $recordModel->get('types_of_services')));
    }
    $serviceTypes = array_unique($serviceTypes);

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
	} catch (\Throwable $ex) {
		echo "Merging cases [" . implode(', ', array_map(function ($el) { return $el->get('case_id'); }, $cases)) . "] into " . $baseCase->get('case_id') . " (" . \App\Config::main('site_URL') . $baseCase->getDetailViewUrl() . ") for claim number $claimNumber" . PHP_EOL;

		echo $ex->__toString();
		var_export($ex);
    echo $mergedCases;
		throw $ex;
	}

  $mergedCases .= $claimNumber . ',' . $baseCase->get('case_id') . ',' . $baseCase->getId() . ',' . \App\Config::main('site_URL') . $baseCase->getDetailViewUrl() . PHP_EOL;
}
echo $mergedCases;
