<?php
/**
 * Cron task to retry e-mails that stay in processing state
 *
 * @package   Cron
 *
 * @copyright DOT Systems Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Michał Kamiński <mkaminski@dotsystems.pl>
 */

/**
 * OSSMail_RetryHanged_Cron class.
 */
class OSSMail_RetryHanged_Cron extends \App\CronHandler
{
  public const DELAY = '1 hour';

  /**
	 * {@inheritdoc}
	 */
  public function process()
  {
    \App\Log::warning("OSSMail::cron::OSSMail_RetryHanged_Cron");

    // Process mails in processing state
    $db = \App\Db::getInstance('admin');
		$rows = (new \App\Db\Query())->from('s_#__mail_queue')->where(['status' => 3])->all($db);
		foreach ($rows as $row) {
      // check how long mail has been in processing state using property modified in params
      $params = $row['params'];
      $params = !empty($params) ? \App\Json::decode($params) : [];
      $modified = $params['modified'] ?? null;
      \App\Log::warning("OSSMail::cron::OSSMail_RetryHanged_Cron:processing {$row['id']} modified $modified");

      // process only if it's too long
      if ($modified && $modified < date('Y-m-d H:i:s', strtotime('-' . static::DELAY))) {
        // check if exists in ossmailview with similar times
        $historyExists = (new \App\QueryGenerator('OSSMailView'))
          ->addCondition('subject', $row['subject'], 'e')
          ->addCondition('date', "$modified," . date('Y-m-d H:i:s', strtotime(static::DELAY)), 'bw')
          ->createQuery()->exists();
        if ($historyExists) {
          $db->createCommand()->delete('s_#__mail_queue', ['id' => $row['id']])->execute();
          \App\Log::warning("OSSMail::cron::OSSMail_RetryHanged_Cron:removed");
        } else {
          // if it has not been requeued, do it
          // otherwise mark it as failed and create Batch Error
          if (!\array_key_exists('reenqueued', $params)) {
            $params['reenqueued'] = date('Y-m-d H:i:s');
            $params['modified'] = date('Y-m-d H:i:s');
            $db->createCommand()->update('s_#__mail_queue', ['status' => 1, 'params' => \App\Json::encode($params)], ['id' => $row['id']])->execute();
            \App\Log::warning("OSSMail::cron::OSSMail_RetryHanged_Cron:reenqueued");
          } else {
            $db->createCommand()->update('s_#__mail_queue', ['status' => 2, 'error' => 'Hanged twice'], ['id' => $row['id']])->execute();
            \App\Log::warning("OSSMail::cron::OSSMail_RetryHanged_Cron:marked as failed");

            if (!empty($params['recordId']) && \App\Record::isExists($params['recordId'])) {
              $recordModel = \Vtiger_Record_Model::getInstanceById($params['recordId']);
              $templateLabel = "directly";
              if ($params['template'] && \App\Record::isExists($params['template'])) {
                $templateLabel = 'using ' . \App\Record::getLabel($params['template']);
              }

              $entry = \Vtiger_Record_Model::getCleanInstance('BatchErrors');
              $entry->set('task_type', 'Email Template');
              $entry->set('task_name', \App\Purifier::decodeHtml(\App\Purifier::purify("Send " . $recordModel->getDisplayName() . " $templateLabel")));
              $entry->set('mod_name', $recordModel->getModuleName());
              $entry->set('item', $recordModel->getId());
              $entry->set('email_template', \is_numeric($params['template']) ? $params['template'] : null);

              $entry->set('error_message', \App\Purifier::encodeHtml("SMTP Error"));
              $entry->set('error_description', 'E-mail was attempted to be sent twice and hanged both times. Please try to send it again or contact your administrator.');

              $entry->save();
            }
          }
        }
      }
    }
  }
}
