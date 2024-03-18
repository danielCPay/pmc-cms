<?php

namespace App;

/**
 * Toast manager class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Michał Kamiński <mkaminski@dotsystems.pl>
 */
use yii\db\Query;

class Toasts
{
  /**
   * Adds a new toast to the s_yf_toasts table.
   *
   * @param int $owner The owner of the toast.
   * @param string $message The message of the toast.
   * @param string $level The level of the toast (e.g., 'info', 'success', 'error'). Default is 'info'. Appending 'Sticky' to level makes it stay on screen until closed
   * @param string|null $title Optional title of the toast.
   * @return int Id of added toast.
   */
  public static function addToast($owner, $message, $level = 'info', $title = null)
  {
    \App\Log::warning("App::Toasts::addToast: $owner, $message, $level, $title");
    $now = date('Y-m-d H:i:s');

    $db = \App\Db::getInstance();
    $db->createCommand()
      ->insert('s_yf_toasts', [
        'added' => $now,
        'owner' => $owner,
        'level' => $level,
        'title' => $title,
        'message' => $message,
      ])
      ->execute();

    return $db->getLastInsertID();
  }

  public static function addHajToast($owner, $message, $level = 'info', $title = null)
  {
    return self::addToast($owner, $message, $level, $title);
  }

  /**
   * Retrieves the n oldest undisplayed toasts for a specific owner. $lastId is used to show toasts, that have already been shown in another browser tab/window.
   *
   * @param int $owner The owner of the toasts.
   * @param int $limit The maximum number of toasts to return. Default is 5. 0 and below means all.
   * @param int|null $lastId Last id fetched.
   * @return array An array of the n oldest undisplayed toasts.
   */
  public static function getOldestUndisplayedToasts($owner, $limit = 5, $lastId = null)
  {
    $db = \App\Db::getInstance();
    $query = new Query();

    $query->select('*')
      ->from('s_yf_toasts')
      ->where(['owner' => $owner]);
    if ($lastId) {
      $query->andWhere(['or', ['>', 'id', $lastId], ['displayed' => null]]);
    } else {
      $query->andWhere(['displayed' => null]);
    }

    $query->orderBy(['added' => SORT_ASC]);
    if ($limit > 0) {
      $query->limit($limit);
    }

    $toasts = $query->all($db);

    return $toasts;
  }

  /**
   * Retrieves greatest id of toasts for a specific owner.
   * 
   * @param int $owner The owner of the toasts.
   * @return int|null Greatest id of toasts for a specific owner.
   */
  public static function getGreatestId($owner) {
    $db = \App\Db::getInstance();
    $query = new Query();

    $query->select('MAX(id)')
      ->from('s_yf_toasts')
      ->where(['owner' => $owner])
      ->andWhere(['not', ['displayed' => null]]);

    $id = $query->scalar($db);

    return $id;
  }

  /**
   * Marks the specified toasts as displayed.
   *
   * @param array $toastIds An array of toast IDs to mark as displayed.
   * @return int The number of rows affected by the update operation.
   */
  public static function markToastsAsDisplayed($toastIds)
  {
    \App\Log::warning("App::Toasts::markToastsAsDisplayed: " . implode(',', $toastIds));
    $db = \App\Db::getInstance();
    $now = date('Y-m-d H:i:s');

    return $db->createCommand()
      ->update('s_yf_toasts', ['displayed' => $now], ['id' => $toastIds])
      ->execute();
  }

  /**
   * Updates the message of the specified toast and marks it as undisplayed.
   * 
   * @param int $toastId The id of the toast to update.
   * @param string $message The new message of the toast.
   * @return int The number of rows affected by the update operation.
   */
  public static function updateToast($toastId, $message)
  {
    \App\Log::warning("App::Toasts:updateToast: " . $toastId . " " . $message);
    $db = \App\Db::getInstance();

	  return $db->createCommand()
      ->update('s_yf_toasts', ['displayed' => null, 'message' => $message], ['id' => $toastId])
      ->execute();
  }
}
