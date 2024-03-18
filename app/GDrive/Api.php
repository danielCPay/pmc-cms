<?php
/**
 * Google Drive API wrapper class.
 *
 * @package   App\GDrive
 *
 * @copyright DOT Systems Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Michał Kamiński <mkaminski@dotsystems.pl>
 */

namespace App\GDrive;

class Api {
  /** @var \Google\Service\Drive $service */
  public $service;

  /**
   * Returns Google Drive API client instance.
   * 
   * @return \Google\Service\Drive API client
   */
  public function __construct($userToImpersonate)
  {
    $credentialsFile = \App\Config::gdrive('credentialsFile');
    $userMapping = \App\Config::gdrive('userMapping', []);
    
    $client = new \Google\Client();
    $client->setAuthConfig($credentialsFile);
    $client->setApplicationName(\App\Config::gdrive('applicationName'));
    $client->setScopes(['https://www.googleapis.com/auth/drive.readonly', 'https://www.googleapis.com/auth/drive.file']);
    $client->setSubject($userMapping[$userToImpersonate] ?: $userToImpersonate);

    $this->service = new \Google\Service\Drive($client);
  }

  /**
   * Lists files for specified query. Accoutns for paging etc.
   */
  public function listFiles($additionalFilter = '') {
    \App\Log::warning("App::GDrive::getFileId(additionalFilter = $additionalFilter)");

    $objects = [];

    $nextPageToken = null;
    do {
      $response = $this->service->files->listFiles([
        'q' => $additionalFilter, 
        'spaces' => 'drive', 
        'fields' => 'files(id, name, mimeType, parents)',
        'pageToken' => $nextPageToken,
      ]);

      array_push($objects, ...$response->files);

      $nextPageToken = $response->nextPageToken;
    } while ($nextPageToken != null);

    return $objects;
  }

  /**
   * Searches for file or folder with specified name.
   * 
   * @param string $fileOrFolder Name of file or folder to find
   * 
   * @return string|false Object id
   */
  public function getFileId($fileOrFolder) {
    \App\Log::warning("App::GDrive::getFileId(fileOrFolder = $fileOrFolder)");

    $objectId = false;

    $parentId = $this->service->files->get('root')->id;

    // loop folders to find searched object
    $pathParts = explode('/', $fileOrFolder);
    while ($parentId && ($part = array_shift($pathParts))) {
      $response = $this->service->files->listFiles(['q' => "trashed = false and '$parentId' in parents and name = '$part'", 'spaces' => 'drive', 'fields' => 'files(id, name, mimeType)']);

      $parentId = null;
      foreach ($response->files as $file) {
        if (count($pathParts)) {
          if ($file->mimeType === 'application/vnd.google-apps.folder') {
            $parentId = $file->id;
            break;
          }
        } else {
          $objectId = $file->id;
          break;
        }
      }
    }

    return $objectId;
  }

  /**
   * Returns folder id, creating it if necessary.
   */
  public function ensureFolder(string $folder) {
    \App\Log::warning("App::GDrive::ensureFolder(folder = $folder)");

    $parentId = $this->service->files->get('root')->id;

    // loop folders to find searched object
    $pathParts = explode('/', $folder);
    while ($parentId && ($part = array_shift($pathParts))) {
      $response = $this->service->files->listFiles(['q' => "trashed = false and '$parentId' in parents and name = '$part' and mimeType = 'application/vnd.google-apps.folder'", 'spaces' => 'drive', 'fields' => 'files(id, name, mimeType)']);

      if (count($response->files)) {
        // found
        $parentId = $response->files[0]->id;
      } else {
        // not found, create
        $driveFile = new \Google\Service\Drive\DriveFile(['name' => $part, 'parents' => [$parentId], 'mimeType' => 'application/vnd.google-apps.folder']);
        $parentId = $this->service->files->create($driveFile, ['uploadType' => 'multipart', 'fields' => 'id'])->id;
      }
    }

    return $parentId;
  }

  /**
   * Uploads file to Google Drive, optionally sending specified metadata. Otherwise filename is taken from passed 
   * argument and MIME type will be application/octet-stream. If `parentId` is not specified, it will be derived from 
   * configuration setting `Gdrive::uploadFolder`. If that setting is also empty, it will be placed in My Drive folder.
   * 
   * @param string $file Path to file to upload
   * @param array{fileName: string|null, mimeType: string|null, parentId: string|null} $metadata Optional file metadata
   * 
   * @return array{id: string, link: string} File ID and link
   */
  public function uploadFile($file, $metadata = []) {
    \App\Log::warning("App::GDrive::uploadFile(file = $file, metadata = " . var_export($metadata, true) . ")");

    $parents = null;
    $parentId = $metadata['parentId'];
    if (empty($parentId)) {
      if (\App\Config::gdrive('uploadFolder')) {
        $parentId = $this->ensureFolder(\App\Config::gdrive('uploadFolder'));

        if (!$parentId) {
          throw new \Exception("Parent folder " . \App\Config::gdrive('uploadFolder') . " not found");
        }
      }
    } else {
      try {
        $this->service->files->get($parentId);
      } catch (\Exception $e) {
        throw $e;
      }
    }
    if (!empty($parentId)) {
      $parents = [$parentId];
    }
    try {
      $fileName = $metadata['fileName'] ?? basename($file);
      $driveFile = new \Google\Service\Drive\DriveFile(['name' => $fileName, 'parents' => $parents, ]);
      $size = filesize($file);
      \App\Log::warning("App::GDrive::uploadFile:Uploading file $file with name $fileName, size = $size into $parentId");
      $content = file_get_contents($file);
      $result = $this->service->files->create($driveFile, ['data' => $content, 'mimeType' => $metadata['mimeType'], 'uploadType' => 'multipart', 'fields' => 'id,webViewLink,webContentLink']);
      \App\Log::warning("App::GDrive::uploadFile:Uploaded file = " . var_export($result, true));
    } catch (\Google\Service\Exception $e) {
      \App\Log::error("App::GDrive::uploadFile:ERROR " . $e->getCode() . " - " . $e->getMessage());
      throw $e;
    }
    return ['id' => $result->id, 'link' => $result->webContentLink ?? $result->webViewLink];
  }

  /**
   * Shares file uploaded to Google Drive. Depending on target type different parameters may be required to be passed in `$parameters` array.
   * 
   * @param string $fileId
   * @param 'owner'|'organizer'|'fileOrganizer'|'writer'|'commenter'|'reader' $role
   * @param 'user'|'group'|'domain'|'anyone' $type Type of share target. `user` and `group` require `emailAddress` parameter, `domain` requires `domain`.
   * @param array{emailAddress: string, domain: string} $parameters Optional permission parameters
   * @param array{emailMessage: string|null, sendNotificationEmail: bool} $requestParameters Optional request parameters
   * 
   * @return string Permission ID
   */
  public function shareFile($fileId, $role = 'reader', $type = 'anyone', $parameters = [], $requestParameters = []) {
    \App\Log::warning("App::GDrive::shareFile(fileId = $fileId, role = $role, type = $type, parameters = " . var_export($parameters, true) . ", request parameters = " . var_export($requestParameters, true) . ")");

    try {
      $permission = new \Google\Service\Drive\Permission(['type' => $type, 'role' => $role, ...($parameters ?? [])]);
      
      $result = $this->service->permissions->create($fileId, $permission, $requestParameters ?? []);

      \App\Log::warning("App::GDrive::shareFile:created permission = " . var_export($result, true));
    } catch (\Google\Service\Exception $e) {
      \App\Log::error("App::GDrive::shareFile:ERROR " . $e->getCode() . " - " . $e->getMessage());
      throw $e;
    }

    return $result->getId();
  }
}
