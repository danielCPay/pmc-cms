<?php
require_once 'include/main/WebUI.php';

\App\User::setCurrentUserId(\App\User::getActiveAdminId());

// script to move relations for unmerged cases

$targetCaseNames = explode(PHP_EOL, 'PDC22-114383
PDC21-105171
PDC21-106118');

// preparation
// \App\Db::getInstance()->createCommand('DROP TABLE unmerge_merged_relations')->execute();
// \App\Db::getInstance()->createCommand('CREATE TABLE IF NOT EXISTS unmerge_merged_relations AS
//  SELECT b.*, label, targetmodule, targetid
//  FROM vtiger_modtracker_basic b
//  JOIN u_yf_crmentity_label l ON ( l.crmid = b.crmid )
//  JOIN vtiger_modtracker_relations r ON ( r.id = b.id )
//  WHERE DATE(b.changedon) = DATE \'2023-6-19\' AND b.whodid = 1 AND STATUS = 13
//  ORDER BY 1 DESC')->execute();

$relations = \App\Db::getInstance()->createCommand('SELECT COUNT(*) over (partition BY m.id) as cnt, m.*, b.module AS unmerge_module, b.crmid AS unmerge_crmid, d.fieldname AS unmerge_field, d.prevalue AS unmerge_case_crmid
FROM unmerge_merged_relations m
JOIN vtiger_modtracker_basic b ON ( b.crmid = m.targetid )
JOIN vtiger_modtracker_detail d ON ( d.id = b.id AND d.postvalue = m.crmid )
WHERE DATE(b.changedon) = DATE \'2023-6-19\' AND b.whodid = 1 AND b.STATUS = 10
ORDER BY 1 DESC, case when b.module = \'Collections\' then 1 ELSE 0 END, 2')->queryAll();
;

$relationsProcessed = [];
$relationsSourceCases = [];
foreach ($relations as $relation) {
  ['crmid' => $targetCaseId, 'unmerge_module' => $relationModule, 'unmerge_crmid' => $relationEntityId, 'unmerge_field' => $relationField, 'unmerge_case_crmid' => $relationCaseId] = $relation;

  $relationsProcessed[$targetCaseId][] = [ 'relationModule' => $relationModule, 'relationEntityId' => $relationEntityId, 'relationField' => $relationField, 'relationCaseId' => $relationCaseId ];
  $relationsSourceCases[$targetCaseId][$relationCaseId] = 1;
}

// --> Wynik jest w ostatnich kolumnach unmerge_module, unmerge_crmid,  unmerge_field i  unmerge_case_crmid oraz crmid (case wynikowy po merge).

echo "Target Case ID;Target Case;Source Case ID;Source Case;Relation Module;Relation ID;Relation Name;Status;Reason" . PHP_EOL;
foreach ($targetCaseNames as $targetCaseName) {
  $targetCaseId = \App\Record::getCrmIdByLabel('Cases', $targetCaseName);

  // verify not removed
  if (\App\Record::isExists($targetCaseId, 'Cases')) {
    // source cases
    // get source cases and check they are still deleted; if so, restore them
    $sourceCaseIds = array_keys($relationsSourceCases[$targetCaseId]);
    foreach ($sourceCaseIds as $sourceCaseId) {
      $state = \App\Record::getState($sourceCaseId);

      if ($state === 'Trash') {
        $sourceCase = Vtiger_Record_Model::getInstanceById($sourceCaseId);
        $sourceCase->changeState('Active');

        $sourceCaseName = \App\Record::getLabel($sourceCaseId);
        echo "$targetCaseId;$targetCaseName;$sourceCaseId;$sourceCaseName;Cases;$sourceCaseId;\"$sourceCaseName\";Restored;" . PHP_EOL;

        \vtlib\Functions::clearCacheMetaDataRecord($sourceCaseId);
      }
    }

    // relations
    foreach ($relationsProcessed[$targetCaseId] as $relation) {
      ['relationModule' => $relationModule, 'relationEntityId' => $relationEntityId, 'relationField' => $relationField, 'relationCaseId' => $relationCaseId] = $relation;
      
      $blockReason = false;
      // Skrypt powinien sprawdzać dodatkowo:
      // 1. czy  unmerge_case_crmid jest aktywny - jeśli nie, to pominąć przetwarzanie dla tego wpisu
      // 2. czy   unmerge_crmid jest   aktywny - jeśli nie, to pominąć przetwarzanie dla tego wpisu
      // 3.   czy   unmerge_crmid jest   aktywny - jeśli nie, to pominąć przetwarzanie dla tego wpisu  
      // 4. czy case wynikowy po merge  crmid jest aktywny - jeśli nie, to pominąć przetwarzanie dla tego wpisu
      // 5. czy dla obiektu  unmerge_crmid w polu case w aktualnych danych jest crmid (czyli czy obiekt nie został przepisany do innego case w międzyczaise) - jeżeli nie, to pominąć przetwarzanie tego wpisu

      if (!\App\Record::isExists($relationCaseId, 'Cases')) {
        $blockReason = 'Source case is removed';
      } else if (!\App\Record::isExists($relationEntityId, $relationModule)) {
        $blockReason = 'Related object is removed';
      } else if (!\App\Record::isExists($targetCaseId, 'Cases')) {
        $blockReason = 'Target case is removed';
      }
      $targetCase = \Vtiger_Record_Model::getInstanceById($targetCaseId, 'Cases');
      $targetCaseName = $targetCase->get('case_id');
      $sourceCase = \Vtiger_Record_Model::getInstanceById($relationCaseId, 'Cases');
      $sourceCaseName = $sourceCase->get('case_id');
      $relationEntity = \Vtiger_Record_Model::getInstanceById($relationEntityId, $relationModule);
      $relationEntityName = $relationEntity->getDisplayName();

      if ($relationField === 'case' && $relationEntity->get($relationField) === 0 && \App\Record::isExists($relationEntity->get('outside_case'))) {
        $blockReason = 'Related object has been changed to Outside Case and can\'t be restored';
      } else if ($relationField === 'outside_case' && $relationEntity->get($relationField) === 0 && \App\Record::isExists($relationEntity->get('case'))) {
        $blockReason = 'Related object has been changed from Outside Case and can\'t be restored';
      } else if ($relationEntity->get($relationField) != $targetCaseId && $relationEntity->get($relationField) != $relationCaseId) {
        $blockReason = 'Related object has been moved to another Case (' . $relationEntity->get($relationField) . ')';
      } else if ($relationEntity->get($relationField) != $targetCaseId && $relationEntity->get($relationField) == $relationCaseId) {
        $blockReason = 'Related object has already been restored to original Case';
      }

      // Jeżeli wszystko dobrze, to zmienić wartość w polu case na  unmerge_case_crmid. Z zapisem w historii. 
      if (!$blockReason) {
        // update
        $relationEntity->set($relationField, $relationCaseId);
        $relationEntity->ext['modificationType'] = \ModTracker_Record_Model::TRANSFER_EDIT;
        $relationEntity->save();
        $status = 'Restored';
      } else {
        $status = 'Blocked';
      }

      // CSV log
      echo "$targetCaseId;$targetCaseName;$relationCaseId;$sourceCaseName;$relationModule;$relationEntityId;\"$relationEntityName\";$status;$blockReason" . PHP_EOL;
    }

    // comments
    $comments = ModComments_Record_Model::getAllParentComments($targetCaseId, 'Cases');
    foreach ($comments as $comment) {
      $blockReason = false;

      // find last related_to in vtiger_modtracker_basic + vtiger_modtracker_detail
      ['changedon' => $changeDate, 'postvalue' => $originalCaseId] = \App\Db::getInstance()->createCommand("SELECT
      b.changedon, d.postvalue
    FROM
      vtiger_modcomments c
      JOIN vtiger_modtracker_basic b ON ( b.crmid = c.modcommentsid )
      JOIN vtiger_modtracker_detail d ON ( d.id = b.id AND d.fieldname = 'related_to' )
    WHERE 
      c.modcommentsid = {$comment->getId()}
    ORDER BY 
      b.changedon DESC 
    LIMIT 
      1")->queryOne();

      if ($originalCaseId != $targetCaseId) {
        $commentId = $comment->getId();
        if (\App\Record::isExists($originalCaseId, 'Cases')) {
          $comment->set('related_to', $originalCaseId);
          $comment->ext['modificationType'] = \ModTracker_Record_Model::TRANSFER_LINK;
          $comment->save();
          $originalCaseName = \App\Record::getLabel($originalCaseId);
          $status = 'Restored';
        } else {
          $blockReason = "Original Case ($originalCaseId) has been removed";
          $status = 'Blocked';
        }

        echo "$targetCaseId;$targetCaseName;$originalCaseId;$originalCaseName;ModComments;$commentId;;$status;$blockReason" . PHP_EOL;
      }
    }
  } else {
    $status = 'Blocked';
    $blockReason = 'Target case has been removed';
    $relationCaseId = '';
    $sourceCaseName = '';
    $relationModule = '';
    $relationEntityId = '';
    $relationEntityName = '';

    // CSV log
    echo "$targetCaseId;$targetCaseName;$relationCaseId;$sourceCaseName;$relationModule;$relationEntityId;\"$relationEntityName\";$status;$blockReason" . PHP_EOL;
  }
}
