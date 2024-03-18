<?php

 /* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): DOT Systems
 * *********************************************************************************** */

use App\Exceptions\BatchErrorHandledWorkflowException;

/**
 * Class DocumentPackages_Record_Model.
 */
class DocumentPackages_Record_Model extends Vtiger_Record_Model
{
  private const VALID_TYPES = ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/pdf'];

  public static function testFileForEncryption($file) {
    $currentLocale = setlocale(LC_ALL, 0) ?: 'C';
    if (!str_ends_with($currentLocale, 'UTF-8')) {
      setlocale(LC_ALL, "$currentLocale.UTF-8");
    }
    $args = escapeshellarg($file);
    if (!str_ends_with($currentLocale, 'UTF-8')) {
      setlocale(LC_ALL, $currentLocale);
    }

    $output = \App\Utils::process("/usr/bin/pdfinfo $args", '/var/www/html', true);
    
    if (preg_match('/Encrypted:\\s+yes/', $output)) {
      return true;
    }

    return false;
  }

  public function validatePackage(Vtiger_Record_Model $recordModel, $additionalSources = [], $allSources = null) {
    \App\Log::warning("DocumentPackages::validatePackage:{$this->getId()}/{$recordModel->getId()}/" . implode(',', $additionalSources));

    $packageType = $this->get('package_type'); // PDF, ZIP
    $allSources = $allSources ?: VTWorkflowUtils::getAllRelatedRecords($this, 'DocumentPackageSources');
    array_push($additionalSources, $this->getId());

    $sourceErrors = [];

    // validate sources (at least 1, if package type is PDF allow only PDF/DOCX/XLSX sources, do not allow loops)
    if (empty($allSources)) {
      $sourceErrors[] = ['name' => 'Package', 'problem' => 'No sources'];
    } else {
      foreach ($allSources as $source) {
        $sourceDocument = Vtiger_Record_Model::getInstanceById($source['source_document']);

        switch ($sourceDocument->getModuleName()) {
          case 'DocumentPackages':
            /** @var DocumentPackages_Record_Model $sourceDocument */
            // check type is valid
            if (in_array($sourceDocument->getId(), $additionalSources)) {
              $sourceErrors[] = [ 'name' => $source['sequence_number_or_name'], 'problem' => 'Attached document package is or has loop'];
            } else if ($packageType === 'PDF' && $sourceDocument->get('package_type') !== 'PDF') {
              $sourceErrors[] = [ 'name' => $source['sequence_number_or_name'], 'problem' => 'Package result type must be PDF to be source of this package'];
            } else {
              // check if source is valid
              array_merge($sourceErrors, $sourceDocument->validatePackage($recordModel, $additionalSources));
            }
            break;
          case 'DocumentTemplates':
            // all are valid choices
            break;
          case 'Documents':
            /** @var Documents_Record_Model $sourceDocument */
            // document type must be convertable to PDF if package is PDF
            if ($packageType === 'PDF') {
              if (!in_array($sourceDocument->get('filetype'), self::VALID_TYPES)) {
                $sourceErrors[] = [ 'name' => $source['sequence_number_or_name'], 'problem' => 'Attached document is not convertible to PDF'];
              } else if ($this->get('package_type') === 'PDF' && $sourceDocument->get('filetype') === 'application/pdf') {
                // test for encryption
                $fileDetails = $sourceDocument->getFileDetails();
                $documentPath = "{$fileDetails['path']}{$fileDetails['attachmentsid']}";
                if (static::testFileForEncryption($documentPath)) {
                  $sourceErrors[] = [ 'name' => $source['sequence_number_or_name'], 'problem' => "Attached document '{$sourceDocument->get('filename')}' is encrypted and can not be merged. Please re-upload unlocked PDF first for this document."];
                }
              }
            }
            break;
          case 'DocumentTypes':
            // attached documents must all be convertable to PDF if package is PDF
            if ($packageType === 'PDF') {
              $attachedDocuments = VTWorkflowUtils::getAllRelatedRecords($recordModel, 'Documents', ['document_type' => $sourceDocument->getId()]);
              foreach($attachedDocuments as $attachedDocumentRow) {
                /** @var Documents_Record_Model $attachedDocument */
                $attachedDocument = Vtiger_Record_Model::getInstanceById($attachedDocumentRow['id']);
                if (!in_array($attachedDocument->get('filetype'), self::VALID_TYPES)) {
                  $sourceErrors[] = [ 'name' => $source['sequence_number_or_name'], 'problem' => "Document '{$attachedDocument->get('filename')}' of type '{$sourceDocument->getDisplayName()}' is not convertible to PDF"];
                } else if ($this->get('package_type') === 'PDF' && $attachedDocument->get('filetype') === 'application/pdf') {
                  // test for encryption
                  $fileDetails = $attachedDocument->getFileDetails();
                  $documentPath = "{$fileDetails['path']}{$fileDetails['attachmentsid']}";
                  if (static::testFileForEncryption($documentPath)) {
                    $sourceErrors[] = [ 'name' => $source['sequence_number_or_name'], 'problem' => "Document '{$attachedDocument->get('filename')}' of type '{$sourceDocument->getDisplayName()}' is encrypted and can not be merged. Please re-upload unlocked PDF first for this document."];
                  }
                }
              }
            }
            break;
        }
      }
    }

    return $sourceErrors;
  }

  public function getFileName(Vtiger_Record_Model $recordModel) {
    $packageType = $this->get('package_type'); // PDF, ZIP
    $resultFileName = $this->get('result_file_name');

    try {
      $textParser = \App\TextParser::getInstanceByModel($recordModel);
      $fileName = \App\Fields\File::sanitizeUploadFileName($textParser->setContent($resultFileName)->parse()->getContent());
      $resultFileName = $fileName . '.' . ($packageType === 'PDF' ? 'pdf' : 'zip');
    } finally {
      unset($textParser);
    }
    
    return $resultFileName;
  }

	/**
	 * Process package and generate file.
   * 
   * @param \Vtiger_Record_Model $recordModel Base record for generation
   * @param string $baseDir Base directory to create temp directory for generated sources
   * @param bool $skipRelation Do not relate generated document with base record model
   * @return int Id of generated document
	 */
	public function generate(Vtiger_Record_Model $recordModel, string $baseDir = '', bool $skipRelation = false)
	{
    \App\Log::warning("DocumentPackages::generate:{$this->getId()}/{$recordModel->getId()}/$skipRelation");

		try {
			$moduleName = $this->get('mod_name');
      $packageType = $this->get('package_type'); // PDF, ZIP
      $resultDocumentType = Vtiger_Record_Model::getInstanceById($this->get('result_document_type'));

      if ($moduleName !== $recordModel->getModuleName()) {
        throw new \Exception("Module mismatch, package defined for $moduleName and called for {$recordModel->getModuleName()}");
      }

      $allSources = VTWorkflowUtils::getAllRelatedRecords($this, 'DocumentPackageSources');
      $sources = [];
      
      $sourceErrors = $this->validatePackage($recordModel, [], $allSources);
      if (empty($sourceErrors)) {
        // filter using conditions
        try {
          $textParser = \App\TextParser::getInstanceByModel($recordModel);
          foreach ($allSources as $source) {
            $sourceDocument = Vtiger_Record_Model::getInstanceById($source['source_document']);
            $condition = $source['condition'];

            if(empty($source['condition']) || \App\Utils\Completions::processIfCondition(htmlspecialchars_decode($condition), $textParser)) {
              $sources[] = $sourceDocument;
            }
          }
        } finally {
          unset($textParser);
        }

        if (empty($sources)) {
          $sourceErrors[] = ['name' => 'Package', 'problem' => 'No sources have matching conditions'];
        }
      }
      if (!empty($sourceErrors)) {
        $errorMessage = implode(', ', array_map(function ($element) { return "Source '{$element['name']}' - {$element['problem']}"; }, $sourceErrors));
        throw new \Exception("Sources have following errors: $errorMessage");
      }

      // prepare temp directory
      $tmpDirName = 'PKG' . hrtime(true);
      $baseDir = $baseDir ?: 'cache/pdf/';
      while (file_exists("$baseDir$tmpDirName")) {
        $tmpDirName .= '_';
        if (strlen($tmpDirName) > 63) {
          throw new Exception("Unable to create temporary directory");
        }
      }
      $tempPath = "$baseDir$tmpDirName";
      mkdir($tempPath);

      // generate all sources to temp directory
      usort($sources, function ($a, $b) { return strnatcmp($a->get('sequence_number_or_name'), $b->get('sequence_number_or_name')); });
      $sourceFiles = [];
      $docNum = 1;
      foreach($sources as $sourceDocument) {
        \App\Log::warning("DocumentPackages::generate:source {$sourceDocument->getModuleName()}/{$sourceDocument->getId()}");

        switch ($sourceDocument->getModuleName()) {
          case 'DocumentPackages':
            /** @var DocumentPackages_Record_Model $sourceDocument */
            // generate document from package
            $documentId = $sourceDocument->generate($recordModel, $tempPath, true);
            /** @var Documents_Record_Model $document */
            $document = Vtiger_Record_Model::getInstanceById($documentId);
            $fileDetails = $document->getFileDetails();
            $documentPath = "{$fileDetails['path']}{$fileDetails['attachmentsid']}";
            $targetDocumentPath = "$tempPath/{$document->get('note_no')} - ". basename($document->get('filename'));
            copy($documentPath, $targetDocumentPath);
            $sourceFiles[] = $targetDocumentPath;
            \App\Log::warning("DocumentPackages::generate:generated document {$documentId}/{$targetDocumentPath} from package");
            break;
          case 'DocumentTemplates':
            /** @var DocumentTemplates_Record_Model $sourceDocument */
            // generate document from template
            $document = $sourceDocument->generate($recordModel, $tempPath);
            $targetDocumentPath = "$tempPath/DT{$docNum} - ". basename($document);
            copy($document, $targetDocumentPath);
            $sourceFiles[] = $targetDocumentPath;
            \App\Log::warning("DocumentPackages::generate:generated document {$targetDocumentPath} from template");
            $docNum += 1;
            break;
          case 'Documents':
            // copy file
            $fileDetails = $sourceDocument->getFileDetails();
			      $documentPath = "{$fileDetails['path']}{$fileDetails['attachmentsid']}";
            $targetDocumentPath = "$tempPath/{$sourceDocument->get('note_no')} - ". basename($sourceDocument->get('filename'));
            copy($documentPath, $targetDocumentPath);
            $sourceFiles[] = $targetDocumentPath;
            \App\Log::warning("DocumentPackages::generate:copied document {$targetDocumentPath} from document");
            break;
          case 'DocumentTypes':
            // copy attached files of type
            $attachedDocuments = VTWorkflowUtils::getAllRelatedRecords($recordModel, 'Documents', ['document_type' => $sourceDocument->getId()]);
            \App\Log::warning("DocumentPackages::generate::found " . count($attachedDocuments) . " by type");
            foreach($attachedDocuments as $attachedDocumentRow) {
              /** @var Documents_Record_Model $attachedDocument */
              $attachedDocument = Vtiger_Record_Model::getInstanceById($attachedDocumentRow['id']);
              $fileDetails = $attachedDocument->getFileDetails();
              $documentPath = "{$fileDetails['path']}{$fileDetails['attachmentsid']}";
              if (empty($fileDetails) || empty($documentPath)) {
                \App\Log::error("DocumentPackages::generate:attached document {$attachedDocumentRow['id']} returned empty path");
              } else {
                $targetDocumentPath = "$tempPath/{$attachedDocument->get('note_no')} - " . basename($attachedDocument->get('filename'));
                copy($documentPath, $targetDocumentPath);
                $sourceFiles[] = $targetDocumentPath;
                \App\Log::warning("DocumentPackages::generate:copied document {$targetDocumentPath} from attached document {$attachedDocumentRow['id']}");
              }
            }
            break;
        }
      }

      // generate filename
      $resultFileName = $this->getFileName($recordModel);

      // merge using ZIP or pdfunite (using sequence of source)
      $resultPath = "$tempPath/$resultFileName";
      if ($packageType === 'PDF') {
        $currentLocale = setlocale(LC_ALL, 0) ?: 'C';
        if (!str_ends_with($currentLocale, 'UTF-8')) {
          setlocale(LC_ALL, "$currentLocale.UTF-8");
        }
        $args = '';
        foreach ($sourceFiles as $sourceFile) {
          $pathInfo = pathinfo($sourceFile);
          if (strtolower($pathInfo['extension']) !== 'pdf') {
            DocumentTemplates_Record_Model::convertToPdf($pathInfo['dirname'], $sourceFile);
            $args .= escapeshellarg($pathInfo['dirname'] . DIRECTORY_SEPARATOR . $pathInfo['filename'] . '.pdf') . ' ';
          } else {
            $args .= escapeshellarg($sourceFile) . ' ';
          }
        }
        $args .= escapeshellarg($resultPath);
        // call pdfunite $args $resultFileName
        $output = \App\Utils::process("/usr/bin/pdfunite $args", '/var/www/html', ['/optional content group/', '/Syntax Warning/']);
		    \App\Log::warning("DocumentPackages::generate <- pdfunite: $output");
        if (!str_ends_with($currentLocale, 'UTF-8')) {
          setlocale(LC_ALL, $currentLocale);
        }
      } else {
        $zip = \App\Zip::createFile($resultPath);
        try {
          foreach ($sourceFiles as $sourceFile) {
            $zip->addFile($sourceFile, basename($sourceFile));
          }
        } finally {
          $zip->close();
        }
      }

      if (!\file_exists($resultPath)) {
        throw new \Exception("Package generation failed");
      }

      // save as document related to base $recordModel
      $relationModel = Vtiger_Relation_Model::getInstance($recordModel->getModule(), Vtiger_Module_Model::getInstance('Documents'));

      if ($relationModel) {
        $params = [
          'document_area' => $resultDocumentType->get('document_area'),
          'document_type' => $resultDocumentType->getId(),
        ];

        if ($relationModel->getRelationType() == Vtiger_Relation_Model::RELATION_O2M && !empty($relationModel->getRelationField())) {
          $params[$relationModel->getRelationField()->getName()] = $recordModel->getId();
        }
      }

			// create document
			$file = \App\Fields\File::loadFromPath($resultPath);
			$file->name = $resultFileName;
			['crmid' => $fileId, 'attachmentsId' => $attachmentId] = \App\Fields\File::saveFromContent($file, $params);

      // add relation to current module
      if ($relationModel) {
        if ($relationModel->getRelationType() != Vtiger_Relation_Model::RELATION_O2M || empty($relationModel->getRelationField())) {
          $relationModel->addRelation($recordModel->getId(), $fileId);
        }
      }

			$fileIds[] = $fileId;

      return $fileId;
		} catch (\Exception $e) {
      \App\Log::warning('DocumentPackages::generate:exception <- ' . print_r($e, true));
			throw $e;
		} catch (\Error $e) {
			\App\Log::warning('DocumentPackages::generate:error <- ' . print_r($e, true));
			throw $e;
		} finally {
      // remove temp directory if exists
      // if (file_exists($tempPath)) {
      //   \vtlib\Functions::recurseDelete($tempPath);
      // }
    }
  }

  /**
   * Handle sending of document package
   * 
   * @param Vtiger_Record_Model $recordModel
   * @param int $documentId Id of document generated by this package
   * @param bool $isTest Flag deciding if package should be sent to test mailbox
   */
  public function send(Vtiger_Record_Model $recordModel, int $documentId, bool $isTest = false) {
    \App\Log::warning("DocumentPackages::send:{$this->getId()}/{$recordModel->getModuleName()}.{$recordModel->getId()}/$documentId/$isTest");

    // get related DocPackageEmailVariants
    $allVariants = VTWorkflowUtils::getAllRelatedRecords($this, 'DocPackageEmailVariants');

    if (empty($allVariants)) {
      \App\Log::warning("DocumentPackages::send:no variants");
      return;
    }

    // validate only single with condition matches or single no condition exists
    $textParser = \App\TextParser::getInstanceByModel($recordModel);
    try {
      $selectedVariants = [];
      $defaultVariants = [];
      foreach ($allVariants as $variant) {
        if (!empty($variant['condition'])) {
          if(\App\Utils\Completions::processIfCondition(htmlspecialchars_decode($variant['condition']), $textParser)) {
            $selectedVariants[] = $variant;
          }
        } else {
          $defaultVariants[] = $variant;
        }
      }

      if (empty($selectedVariants) && empty($defaultVariants)) {
        $error = "No email variant matched due to unmet conditions";
      } else {
        $targetVariants = count($selectedVariants) > 0 ? $selectedVariants : $defaultVariants;
      }
      \App\Log::warning("DocumentPackages::send:matching:" . var_export(['variants' => $allVariants, 'selectedVariants' => $selectedVariants, 'defaultVariants' => $defaultVariants, 'targetVariants' => $targetVariants], true));

      if ($error) {
        throw new \Exception($error);
      }

      // send document as attachment using template specified by selected variants
      foreach ($targetVariants as $selectedVariant) {
        $template = Vtiger_Record_Model::getInstanceById($selectedVariant['id']);
        if (!empty($template->get('email_from')) && \App\Record::isExists($template->get('email_from'))) {
          $emailFromModel = Vtiger_Record_Model::getInstanceById($template->get('email_from'));
          $emailFrom = $emailFromModel->get('smtp');

          $emailTo = $isTest ? $emailFromModel->get('test_mailbox') : Vtiger_Record_Model::getInstanceById($template->get('email_to'))->get('result_text');
          if (!$isTest && $template->get('email_cc')) {
            $emailCc = Vtiger_Record_Model::getInstanceById($template->get('email_cc'))->get('result_text');
          }
          $emailSubject = $textParser->setContent($template->get('email_subject'))->parse()->getContent();
          $emailContent = $template->get('email_content');
          $emailContent = \App\Utils\Completions::processIfs($emailContent, $textParser);
          $emailContent = $textParser->setContent($emailContent)->parse()->getContent();
          
          $emailParser = \App\EmailParser::getInstanceByModel($recordModel);
          try {
            $emailTo = $emailParser->setContent($emailTo)->parse()->getContent(true);
            if ($emailCc) {
              $emailCc = $emailParser->setContent($emailCc)->parse()->getContent(true);
            }
          } finally {
            unset($emailParser);
          }

          $mailerContent = [ 
            'smtp_id' => $emailFrom,
            'to' => $emailTo, 
            'attachments' => [ 'ids' => [ $documentId ] ],
            'subject' => $emailSubject,
            'content' => $emailContent,
            'template' => $template->getId(),
            'recordId' => $recordModel->getId(),
          ];

          if ($emailCc) {
            $mailerContent['cc'] = $emailCc;
          }

          $result = \App\Mailer::sendDirect($mailerContent, $error);
        } else {
          \App\Log::warning("DocumentPackages::send:SMTP in From in template (" . $template->getDisplayName() . ") is empty");
          $error = "SMTP in From in email template (" . $template->getDisplayName() . ") is empty";
          $result = false;
        }
        
        if (!$result) {
          $entry = Vtiger_Record_Model::getCleanInstance('BatchErrors');

          $entry->set('task_type', 'Document Package');
          $entry->set('task_name', "Send Document Package '" . $this->get('document_package_name') . "' for record '" . $recordModel->getDisplayName() . "'");
          $entry->set('mod_name', $recordModel->getModuleName());
          $entry->set('item', $recordModel->getId());

          $entry->set('document_package', $this->getId());

          $entry->set('error_message', "Sending result of document package for variant " . $selectedVariant['email_variant_name'] . " by e-mail failed");
          $entry->set('error_description', $error);

          $entry->save();

          throw new BatchErrorHandledWorkflowException($error);
        }
      }
    } finally {
      unset($textParser);
    }
  }

  /**
   * Handle upload to Dropbox of document package
   * 
   * @param Vtiger_Record_Model $recordModel
   * @param int $documentId Id of document generated by this package
   * @param $sendToDropbox Option for sending to dropbox
   */
  public function dropbox(Vtiger_Record_Model $recordModel, int $documentId, $sendToDropbox = null) {
    \App\Log::warning("DocumentPackages::dropbox:{$this->getId()}/{$recordModel->getModuleName()}.{$recordModel->getId()}/$documentId/$sendToDropbox");

    $account = \App\Config::dropbox('defaultAccount');

    if (empty($account)) {
      \App\Log::error("DocumentPackages::dropbox:unspecified account");
      throw new \Exception("No Dropbox account specified");
    } else if (empty($sendToDropbox) && empty($this->get('send_to_dropbox'))) {
      \App\Log::warning("DocumentPackages::dropbox:unset destination");
      return;
    } else if (empty($sendToDropbox) && !empty($this->get('send_to_dropbox'))) {
      $sendToDropbox = $this->get('send_to_dropbox');
    } else if ($sendToDropbox == 1) {
      \App\Log::warning("DocumentPackages::dropbox:disable send");
      return;
    } 
    
    if (!\App\Record::isExists($sendToDropbox, 'DropboxDestinations')) {
      \App\Log::error("DocumentPackages::dropbox:missing $sendToDropbox");
      throw new \Exception("Specified Dropbox Destination does not exist");
    }

    $dropboxDestination = Vtiger_Record_Model::getInstanceById($sendToDropbox, 'DropboxDestinations');
    $destinationFolder = $dropboxDestination->get('dropbox_destination_folder');

    $textParser = \App\TextParser::getInstanceByModel($recordModel);
    try {
      $destinationFolder = $textParser->parseData($destinationFolder);
    } finally {
      unset($textParser);
    }


    /** @var Documents_Record_Model $document */
    $document = Vtiger_Record_Model::getInstanceById($documentId);
    $fileDetails = $document->getFileDetails();
    $documentPath = "{$fileDetails['path']}{$fileDetails['attachmentsid']}";
    $fileName = basename($document->get('filename'));

    try {
      \App\Log::warning("DocumentPackages::dropbox:uploading $documentPath to $destinationFolder/$fileName on $account");

      \App\Dropbox\Api::upload($account, $documentPath, $destinationFolder, $fileName);

      \App\Log::warning("DocumentPackages::dropbox:upload finished");
    } catch (\Throwable $e) {
      \App\Log::error("DocumentPackages::dropbox:upload failed with " . $e->getMessage());

      $summary = $e->getMessage();
      if ($e instanceof \Kunnu\Dropbox\Exceptions\DropboxClientException) {
        try {
          $error = \App\Json::decode($summary);
          $summary = $error['error_summary'] ?? $summary;
        } catch (\Exception $e) {
          // ignore
        }
      }

      // save BatchError, continue
      $entry = Vtiger_Record_Model::getCleanInstance('BatchErrors');

      $entry->set('task_type', 'Document Package');
      $entry->set('task_name', "Send Document Package '" . $this->get('document_package_name') . "' for record '" . $recordModel->getDisplayName() . "' to Dropbox");
      $entry->set('mod_name', $recordModel->getModuleName());
      $entry->set('item', $recordModel->getId());

      $entry->set('document_package', $this->getId());

      $errorMessage = "Dropbox error: $summary. ";
      if (str_starts_with($summary, 'too_many')) {
         $errorMessage .= "The system will retry. ";
      }
      $errorMessage .= "Other automatic actions are continued.";
      $entry->set('error_message', $errorMessage);
      $entry->set('error_description', $e->getMessage());

      $entry->save();
    }
  }

  public static function getActive($moduleName, Vtiger_Record_Model $recordModel = null) {
    return array_map(function ($element) { return Vtiger_Record_Model::getInstanceById($element); }, (new \App\QueryGenerator('DocumentPackages'))->setField('id')->addCondition('mod_name', $moduleName, 'e')->createQuery()->column());
  }

  public static function checkActive($moduleName, Vtiger_Record_Model $recordModel = null) {
    return (new \App\QueryGenerator('DocumentPackages'))->setField('id')->addCondition('mod_name', $moduleName, 'e')->createQuery()->count() > 0;
  }
}
