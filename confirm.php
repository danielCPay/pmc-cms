<?php

define('ROOT_DIRECTORY', __DIR__ !== DIRECTORY_SEPARATOR ? __DIR__ : '');

require __DIR__ . '/include/main/WebUI.php';

\App\Process::$requestMode = 'confirm';
try {
  $request = \App\Request::init();
  App\Session::init();

  $codeValidity = '-24 hours';
  $code = $request->get('code');

  $db = \App\Db::getInstance();
  $codeData = (new \App\Db\Query())->from('s_yf_challenges')->where(['and', ['challenge_code' => $code], ['>=', 'challenge_date', date('Y-m-d H:i:s', strtotime($codeValidity))]])->all();
  if (empty($codeData)) {
    \App\Log::warning("CHALLENGE - $code - invalid or expired");
    echo "Invalid or expired code";
  } else if (count($codeData) > 1) {
    \App\Log::warning("CHALLENGE - $code - duplicated code");
    echo "Invalid or expired code";
  } else {
    \App\Log::warning("CHALLENGE - $code - matched " . print_r($codeData, true));
    
    // remove code from challenges table
    $db->createCommand()->delete('s_yf_challenges', ['challenge_code' => $code])->execute();

    // get record and clear email_to_be_confirmed
    $recordId = $codeData[0]['record_id'];
    if (\App\Record::isExists($recordId) && \App\Record::getType($recordId) === $codeData[0]['module_name']) {
      switch (\App\Record::getType($recordId)) {
        case 'Providers':
          $recordModel = Vtiger_Record_Model::getInstanceById($recordId);
          $recordModel->set('email', $recordModel->get('email_to_be_confirmed'));
          $recordModel->set('email_to_be_confirmed', null);
          $recordModel->save();
          break;

      }
      echo "Code verified";
    } else {
      \App\Log::warning("CHALLENGE - $code - mismatched module or record " . \App\Record::getType($recordId) . "/$recordId");
      echo "Invalid or expired code";
    }
  }

  // clear old confirmation codes
  $db->createCommand()->delete('s_yf_challenges', ['<', 'challenge_date', date('Y-m-d H:i:s', strtotime($codeValidity))])->execute();
} catch (Exception $e) {
	\App\Log::error($e->getMessage() . ' => ' . $e->getFile() . ':' . $e->getLine());
	
  echo "Invalid or expired code";
}
