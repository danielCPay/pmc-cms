<?php
chdir(__DIR__ . '/../../');
set_include_path(get_include_path() . PATH_SEPARATOR . __DIR__ . '/../../');

require_once 'include/main/WebUI.php';
require_once 'modules/com_vtiger_workflow/include.php';

// foreach record with www = 'PDC.com' 
$allPDC = (new \App\QueryGenerator('Insureds'))
	->addCondition('www', 'PDC.com', 'e')
	->setFields(['id'])
	->createQuery()
	->all();
foreach ($allPDC as $record) {
	$log = false;
	
	$cacheName = "{$record['id']}:Insureds";
	if (\App\Cache::staticHas('RecordModel', $cacheName)) {
		\App\Cache::staticDelete('RecordModel', $cacheName);
	}
	if (!\App\Record::isExists($record['id'])) {
		continue;
	}
	try {
		$recordModel = Vtiger_Record_Model::getInstanceById($record['id']);
	} catch (\Exception $e) {
		continue;
	}
	if ($recordModel->get('www') !== 'PDC.com') {
		continue;
	}
	
	$firstName = $recordModel->get('insured1_first_name');
	$lastName = $recordModel->get('insured1_last_name');
	$fullName = $recordModel->get('insured_name');
	$newFullName = trim($firstName . ($firstName && $lastName ? ' ' : '') . $lastName);

	
	if ($fullName != $newFullName) {
		echo "Processing Insured {$record['id']} with name '$newFullName'" . PHP_EOL;
		echo "  Updating name from '$fullName'" . PHP_EOL;
		$log = true;

		$recordModel->set('insured_name', $newFullName);
	}

	// find record with www = 'PT.com' and insured_name = record.insured_name
	$allPT = (new \App\QueryGenerator('Insureds'))
		// ->addCondition('www', 'PT.com', 'e')
		->addCondition('id', $recordModel->getId(), 'n')
		->setFields(['id', 'insured_name'])
		->createQuery()
		->all();
  foreach ($allPT as $pt) {
		if (\App\Utils::str_equal($newFullName, $pt['insured_name'])) {
      if (!$log) {
        echo "Processing Insured {$record['id']} with name '$newFullName'" . PHP_EOL;
        if ($fullName != $newFullName) {
          echo "  Updating name from '$fullName'" . PHP_EOL;
        }
        $log = true;
      }

			echo "  Merging record {$pt['id']} with name '{$pt['insured_name']}'" . PHP_EOL;

			$ptRecord = Vtiger_Record_Model::getInstanceById($pt['id']);

			// move all PT.com relations (Claims, Cases, BatchTasks, BatchErrors) to record
			$claims = VTWorkflowUtils::getAllRelatedRecords($ptRecord, 'Claims');
			$cases = VTWorkflowUtils::getAllRelatedRecords($ptRecord, 'Cases');
			$batchErrors = VTWorkflowUtils::getAllRelatedRecords($ptRecord, 'BatchErrors');
			
			foreach ($claims as $claim) {
				$claimModel = Vtiger_Record_Model::getInstanceById($claim['id']);
				$claimModel->set('insured', $record['id']);
				$claimModel->save();
				echo "    Moved claim {$claim['id']}" . PHP_EOL;
			}

			foreach ($cases as $case) {
				$caseModel = Vtiger_Record_Model::getInstanceById($case['id']);
				$caseModel->set('insured', $record['id']);
				$caseModel->save();
				echo "    Moved case {$case['id']}" . PHP_EOL;
			}

			foreach ($batchErrors as $batchError) {
				$batchErrorModel = Vtiger_Record_Model::getInstanceById($batchError['id']);
				$batchErrorModel->set('insured', $record['id']);
				$batchErrorModel->save();
				echo "    Moved batch error {$batchError['id']}" . PHP_EOL;
			}

			// purge PT.com record
			$ptRecord->delete();
			echo "    Purged PT record" . PHP_EOL;
		}
	}
	
	// set record.www = 'PDC.PT.com'
	$recordModel->set('www', 'PT.PDC.com');
	$recordModel->save();
	// echo "  Set WWW" . PHP_EOL;
}

echo PHP_EOL;
echo "Done" . PHP_EOL;
