<?php
/**
 * Google Calendar API wrapper class.
 *
 * @package   App\GCal
 *
 * @copyright DOT Systems Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Michał Kamiński <mkaminski@dotsystems.pl>
 */

namespace App\GCal;

use Calendar_Record_Model;
use Users_Module_Model;

class Api {
  /** @var \Google\Service\Calendar $service */
  private static $service;

  private static $colorMap = [
    'Hearing' => 11,
    'Meeting' => 2,
    'Online meeting' => 2,
    'To Do' => 7,
    'Task' => 7,
    'Pre-Trial Conference' => 3,
  ];

  /**
   * Returns Google Calendar API client instance.
   * 
   * @return \Google\Service\Calendar API client
   */
  public static function getService() {
    if (!self::$service) {
      $credentialsFile = \App\Config::gcal('credentialsFile');
      
      $client = new \Google\Client();
      $client->setAuthConfig($credentialsFile);
      $client->setApplicationName(\App\Config::gcal('applicationName'));
      $client->setScopes(['https://www.googleapis.com/auth/calendar.events']);
      $client->setSubject(\App\Config::gcal('impersonateSubject'));

      self::$service = new \Google\Service\Calendar($client);
    }
    
    return self::$service;
  }

  /**
   * Retrieves events from Google Calendar. If possible, uses incremental sync (if syncToken is set in configuration)
   */
  public static function getEvents($serviceOverride = null, $calendarId = 'primary') {
    \App\Log::warning("App::GCal::getEvents(calendarId = $calendarId)");

    $service = $serviceOverride ?? self::getService();

    $maxResults = \App\Config::gcal('pageSize');
    $events = [];
    $pageToken = null;
    $page = 1;
    $syncToken = \App\Config::gcal('syncToken');
    $minTimeFullSync = date('c', strtotime(\App\Config::gcal('fullSyncLowerBound')));
    do {
      try {
        $params = ['maxResults' => $maxResults, 'singleEvents' => true, 'pageToken' => $pageToken];
        if (!empty($syncToken)) {
          $params['syncToken'] = $syncToken;
        } else {
          $params['timeMin'] = $minTimeFullSync;
        }
        \App\Log::warning("App::GCal::getEvents:Requesting events with parameters = " . var_export($params, true));
        $results = $service->events->listEvents('primary', $params);
      } catch (\Google\Service\Exception $e) {
        if ($e->getCode() == 410) {
          $syncToken = null;
          unset($params['syncToken']);
          $params['timeMin'] = $minTimeFullSync;
          $params['showDeleted'] = true;

          \App\Log::warning("App::GCal::getEvents:Sync invalidated, requesting events with parameters = " . var_export($params, true));
          $results = $service->events->listEvents('primary', $params);
        } else {
          throw $e;
        }
      }
      \App\Log::warning("App::GCal::getEvents:Returned " . count($results) . " results in page $page");
      $page++;

      foreach ($results as $item) {
        $events[] = self::convertFromGCal($item);
      }

      $pageToken = $results['nextPageToken'];
    } while (!empty($pageToken));
    \App\Log::warning("App::GCal::getEvents:Sync token = {$results['nextSyncToken']}");
    self::saveConfig($results['nextSyncToken']);

    \App\Log::warning("App::GCal::getEvents:Returning " . count($events));
    return $events;
  }

  public static function sync($calendarId = 'primary', $serviceOverride = null) {
    \App\Log::warning("App::GCal::sync(calendarId = $calendarId)");

    $service = $serviceOverride ?? self::getService();

    // find all events in CRM pending add to GCal
    \App\Log::warning("App::GCal::sync:starting insert of new events...");
    $eventIdsToSend = (new \App\QueryGenerator('Calendar'))->addCondition('gcal_send', 1, 'e')->setFields(['id'])->createQuery()->column();
    $tz = new \DateTimeZone(\App\Fields\DateTime::getTimeZone());
    foreach ($eventIdsToSend as $eventId) {
      \App\Log::warning("App::GCal::sync:adding event $eventId to GCal");

      $crmEvent = Calendar_Record_Model::getInstanceById($eventId);
      $startTime = new \DateTime($crmEvent->get('date_start') . ' ' . $crmEvent->get('time_start'), $tz);
      $endTime = new \DateTime($crmEvent->get('due_date') . ' ' . $crmEvent->get('time_end'), $tz);
      $attendeeIds = array_filter(array_unique([$crmEvent->get('assigned_user_id'), ...explode(',', $crmEvent->get('shownerid'))]));
      $attendees = array_filter(array_map(function ($a) {
        if (\App\User::isExists($a)) {
          $user = \Users_Record_Model::getInstanceById($a, 'Users');
          if ($user) {
            return ['displayName' => $user->getName(), 'email' => $user->get('email1')];
          }
        }
        return [];
      }, $attendeeIds), function ($e) { if (empty($e['email'])) return false; return true; });

      $crmEvent->set('gcal_send', 0);
      $crmEvent->save();
      
      $event = self::addEvent([
        'summary' => $crmEvent->get('subject'),
        'description' => $crmEvent->get('notes'),
        'location' => $crmEvent->get('location'),
        'start' => $startTime->format('c'),
        'end' => $endTime->format('c'),
        'allday' => $crmEvent->get('allday'),
        'attendees' => $attendees,
        'activitytype' => $crmEvent->get('activitytype'),
      ], $calendarId, $service);

      $crmEvent->set('gcal_id', $event['id']);
      $crmEvent->set('gcal_etag', $event['etag']);
      $crmEvent->set('gcal_link', $event['link']);
      $crmEvent->save();
    }

    // get all events from GCal (incremental sync if possible)
    \App\Log::warning("App::GCal::sync:starting sync of existing events...");
    try {
      $events = self::getEvents($service, $calendarId);
      foreach($events as $event) {
        \App\Log::warning("App::GCal::sync:syncing event " . var_export($event, true));

        // for each GCal event locate it in CRM
        $crmEventData = (new \App\QueryGenerator('Calendar'))->setFields(['id', 'gcal_id', 'gcal_etag'])->addCondition('gcal_id', $event['id'], 'e')->createQuery()->one();

        if (empty($crmEventData)) {
          \App\Log::warning("App::GCal::sync:new event");

          // if missing, then insert into CRM
          $crmEvent = Calendar_Record_Model::getCleanInstance('Calendar');
          self::updateEntity($crmEvent, $event);
          $crmEvent->set('gcal_send', 0);
          $crmEvent->set('assigned_user_id', \App\User::getUserIdByFullName('---'));
          $crmEvent->set('created_user_id', \App\User::getUserIdByName('GoogleCalendar'));
          $crmEvent->save();
        } else if ($event['status'] == 'cancelled') {
          \App\Log::warning("App::GCal::sync:deleted event {$crmEventData['id']}/{$crmEventData['gcal_etag']}");

          $crmEvent = Calendar_Record_Model::getInstanceById($crmEventData['id']);
          $crmEvent->set('gcal_send', 0);
          $crmEvent->delete();
        } else if ($crmEventData['gcal_etag'] != $event['etag']) {
          \App\Log::warning("App::GCal::sync:modified event {$crmEventData['id']}/{$crmEventData['gcal_etag']}");

          // if different etag, update event in CRM
          $crmEvent = Calendar_Record_Model::getInstanceById($crmEventData['id']);
          self::updateEntity($crmEvent, $event);
          $crmEvent->set('gcal_send', 0);
          $crmEvent->save();
        } else {
          \App\Log::warning("App::GCal::sync:unchanged event");

          // unchanged
        }
      }
    } catch (\Exception $e) {
      \App\Log::error("App::GCal::sync:error while importing events: " . var_export($e, true));
      self::saveConfig('');
      throw $e;
    }
  }


  /**
   * Creates event and returns it's representation (with id).
   */
  public static function addEvent($eventData, $calendarId = 'primary', $serviceOverride = null) {
    \App\Log::warning("App::GCal::addEvent(calendarId = $calendarId, eventData = " . var_export($eventData, true) . ")");

    /** @var \Google\Service\Calendar $service */
    $service = $serviceOverride ?? self::getService();

    $eventRO = self::convertToGCal($eventData);

    try {
      $event = $service->events->insert($calendarId, $eventRO, ['sendUpdates' => 'all']);
    } catch (\Exception $e) {
      \App\Log::error(var_export($eventRO, true));
      \App\Log::error(var_export($e, true));

      throw $e;
    }

    $eventInternal = self::convertFromGCal($event);

    \App\Log::warning("App::GCal::addEvent:created event " . var_export($eventInternal, true));

    return $eventInternal;
  }

  public static function deleteEvent($eventId, $calendarId = 'primary', $serviceOverrid = null) {
    \App\Log::warning("App::GCal::deleteEvent(calendarId = $calendarId, eventId = $eventId)");

    $service = $serviceOverride ?? self::getService();

    try {
      $service->events->delete($calendarId, $eventId);
    } catch (\Exception $e) {
      \App\Log::error(var_export($e, true));

      throw $e;
    }
  }

  /**
   * Converts Google Calendar API event representation to more usable version.
   */
  public static function convertFromGCal($event) {
    return [
      'id' => $event['id'],
      'etag' => $event['etag'],
      'link' => $event['htmlLink'],
      'status' => $event['status'] /* cancelled means deleted */,
      'eventType' => $event['eventType'],
      'created' => $event['created'],
      'updated' => $event['updated'],
      'summary' => $event['summary'],
      'description' => $event['description'],
      'location' => $event['location'],
      'creator' => [ 'email' => $event['creator']['email'], 'name' => $event['creator']['displayName'] ],
      'organizer' => [ 'email' => $event['organizer']['email'], 'name' => $event['organizer']['displayName'] ],
      'allday' => !empty($event['start']['date']),
      'start' => !empty($event['start']['date']) ? $event['start']['date'] : $event['start']['dateTime'],
      'end' => $event['endTimeUnspecified'] ? null : (!empty($event['start']['date']) ? date('Y-m-d', strtotime('-1 second', strtotime($event['end']['date']))) : $event['end']['dateTime']),
      'attendees' => array_map(function ($attendee) { return [ 'email' => $attendee['email'], 'name' => $attendee['displayName'] ]; }, $event['attendees']),
    ];
  }

  /**
   * Converts usable version of event to Google Calendar API
   */
  public static function convertToGCal($event) {
    return new \Google\Service\Calendar\Event([
      'summary' => $event['summary'],
      'location' => $event['location'],
      'description' => $event['description'],
      'start' => [
        'dateTime' => $event['allday'] ? null : $event['start'],
        'date' => $event['allday'] ? date('Y-m-d', strtotime($event['start'])) : null,
      ],
      'end' => [
        'dateTime' => $event['allday'] ? null : $event['end'],
        'date' => $event['allday'] ? date('Y-m-d', strtotime($event['end'])) : null,
      ],
      'attendees' => $event['attendees'],
      'colorId' => self::$colorMap[$event['activitytype']],
    ]);
  }

  private static function saveConfig(string $syncToken) {
    $config = new \App\ConfigFile('gcal');
    $config->set('syncToken', $syncToken);
    $config->create();
  }

  public static function updateEntity(Calendar_Record_Model $recordModel, $event) {
    $recordModel->set('activitytype', 'Google Calendar');

    $recordModel->set('gcal_id', $event['id']);
    $recordModel->set('gcal_etag', $event['etag']);
    $recordModel->set('gcal_link', $event['link']);

    $recordModel->set('subject', $event['summary']);
    $recordModel->set('location', $event['location']);

    $startDate = strtotime($event['start']);
    $endDate = strtotime($event['end']);
    $recordModel->set('date_start', date('Y-m-d', $startDate));
    $recordModel->set('due_date', date('Y-m-d', $endDate));
    if ($event['allday']) {
      $recordModel->set('allday', 1);
    } else {
      $recordModel->set('time_start', date('H:i:s', $startDate));
      $recordModel->set('time_end', date('H:i:s', $endDate));
    }
    
    $attendees = $event['attendees'];
    $attendeesString = '

Attendees:';
    $attendeeIds = [];
    $assignedUserId = $recordModel->get('assigned_user_id');
    foreach ($attendees as $attendee) {
      $attendeesString .= '
' . $attendee['name'] . (!empty($attendee['name']) ? ' <' : '') . $attendee['email'] . (!empty($attendee['name']) ? '>' : '');

      // find user by email
      $userId = Users_Module_Model::getUserIdByEmail($attendee['email']);
      if (!empty($userId) && $userId != $assignedUserId) {
        $attendeeIds[] = $userId;
      }
    }

    $recordModel->set('notes', nl2br($event['description'] . $attendeesString));
    $recordModel->set('shownerid', $attendeeIds);

    return $recordModel;
  }
}
