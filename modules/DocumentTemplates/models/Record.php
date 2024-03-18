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

/**
 * Class DocumentTemplates_Record_Model.
 */

require 'excel.php';

class DocumentTemplates_Record_Model extends Vtiger_Record_Model
{
	private const PLACEHOLDER_FIXER_1_REGEXP = '/\$\(.*?\)\$/s';
	private const PLACEHOLDER_FIXER_2_REGEXP = '/\$\((.*?) : (.*?)\)\$/s';
	private const IF_FIXER_REGEXP = '/~(<[^>]+>)*~(<[^>]+>)*~(<[^>]+>)*(I|F)/s';
	private const IF_FIXER_REGEXP2 = '/(I|F)(<[^>]+>)*~(<[^>]+>)*~(<[^>]+>)*~/s';
	private const TAG_REMOVER_REGEXP = '/<[^>]+>/s';

	/**
	 * Processes template, generates file and creates document related to base record model.
	 */
	public function generateDocument(Vtiger_Record_Model $recordModel, $assignedUser = null)
	{
		\App\Log::warning("DocumentTemplates::generateDocument:" . $this->getId() . '/' . $recordModel->getId());

		// prepare temp directory
		$tmpDirName = 'TMPL' . hrtime(true);
		while (file_exists("cache/pdf/$tmpDirName")) {
			$tmpDirName .= '_';
			if (strlen($tmpDirName) > 63) {
				throw new Exception("Unable to create temporary directory");
			}
		}
		$tempPath = "cache/pdf/$tmpDirName";
		mkdir($tempPath);

		try {
			$resultPath = $this->generate($recordModel, $tempPath);

			$resultDocumentType = Vtiger_Record_Model::getInstanceById($this->get('result_document_type'));
			$relationModel = Vtiger_Relation_Model::getInstance($recordModel->getModule(), Vtiger_Module_Model::getInstance('Documents'));

			$params = [
				'document_area' => $resultDocumentType->get('document_area'),
				'document_type' => $resultDocumentType->getId(),
			];

			if ($assignedUser) {
				$params['assigned_user_id'] = $assignedUser;
			}

			if ($relationModel->getRelationType() == Vtiger_Relation_Model::RELATION_O2M && !empty($relationModel->getRelationField())) {
				$params[$relationModel->getRelationField()->getName()] = $recordModel->getId();
			}

			// create document
			$file = \App\Fields\File::loadFromPath($resultPath);
			$file->name = basename($resultPath);
			['crmid' => $fileId, 'attachmentsId' => $attachmentId] = \App\Fields\File::saveFromContent($file, $params);
			// add relation to current module
			if ($relationModel->getRelationType() != Vtiger_Relation_Model::RELATION_O2M || empty($relationModel->getRelationField())) {
				$relationModel->addRelation($recordModel->getId(), $fileId);
			}

			return $fileId;
		} finally {
			\vtlib\Functions::recurseDelete($tempPath);
		}
	}

	/**
	 * Process template and generate file.
	 * 
	 * @param \Vtiger_Record_Model $recordModel Base record for generation
	 * @param string $filePath Path to save file
	 */
	public function generate(Vtiger_Record_Model $recordModel, string $filePath)
	{
		\App\Log::warning("DocumentTemplates::generate:" . $this->getId());

		try {
			// get templates by name
			$templateVariants = (new \App\QueryGenerator($this->getModuleName()))
				->setFields(['id', 'condition', 'document_template_variant_name', 'document_tamplate_doc', 'do_not_generate'])
				->addCondition('document_template_name', $this->get('document_template_name'), 'e')
				->addCondition('mod_name', $recordModel->getModuleName(), 'e')
				->createQuery()
				->all();

			// validate template conditions and check only one matches (0 or more than 1 means error)
			$textParser = \App\TextParser::getInstanceByModel($recordModel);
			try {
				$selectedVariants = [];
				$defaultVariants = [];
				foreach ($templateVariants as $variant) {
					if (!empty($variant['condition'])) {
						if (\App\Utils\Completions::processIfCondition(htmlspecialchars_decode($variant['condition']), $textParser)) {
							$selectedVariants[] = $variant;
						}
					} else {
						$defaultVariants[] = $variant;
					}
				}
			} finally {
				unset($textParser);
			}

			if (empty($selectedVariants) && empty($defaultVariants)) {
				$error = "No document template matched for '{$this->get('document_template_name')}' due to unmet conditions";
			} else if (count($selectedVariants) > 1 || count($defaultVariants) > 1) {
				$error = "Multiple document templates matched for '{$this->get('document_template_name')}'";
			} else if (count($selectedVariants) === 1 || count($defaultVariants) === 1) {
				$selectedVariant = count($selectedVariants) === 1 ? $selectedVariants[0] : $defaultVariants[0];

				// validate matched template has document attached or do not generate marked
				if (empty($selectedVariant['document_tamplate_doc']) && !$selectedVariant['do_not_generate']) {
					$error = "Matched document template '{$this->get('document_template_name')}'/'{$selectedVariant['document_template_variant_name']}' does not have document attached and also does not have Do not generate set";
				}
			}

			if ($error) {
				\App\Log::warning("DocumentTemplates::generate:matching problems:" . var_export(['variants' => $templateVariants, 'selectedVariants' => $selectedVariants, 'defaultVariants' => $defaultVariants, 'selectedVariant' => $selectedVariant], true));
				throw new \Exception($error);
			}

			// process template
			$template = Vtiger_Record_Model::getInstanceById($selectedVariant['id']);

			/** @var \Documents_Record_Model $document */
			$document = Vtiger_Record_Model::getInstanceById($template->get('document_tamplate_doc'));
			$fileDetails = $document->getFileDetails();
			$documentPath = "{$fileDetails['path']}{$fileDetails['attachmentsid']}";
			$fileParts = pathinfo($fileDetails['name']);

			// generate filename
			$textParser = \App\TextParser::getInstanceByModel($recordModel);
			try {
				$fileName = \App\Fields\File::sanitizeUploadFileName($textParser->setContent($template->get('result_file_name'))->parse()->getContent());
				$resultFileName = $fileName . '.' . ($template->get('convert_result_to_pdf') ? 'pdf' : $fileParts['extension']);
				$fullPath = $filePath . DIRECTORY_SEPARATOR . $fileName . ".{$fileParts['extension']}";
			} finally {
				unset($textParser);
			}

			\App\Log::warning("DocumentTemplates::generate:saving to $fullPath");

			// validate type, process
			if ($fileDetails['type'] === 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet') {
				self::processXlsx($template, $recordModel, $fullPath, $documentPath);
			} else if ($fileDetails['type'] === 'application/vnd.openxmlformats-officedocument.wordprocessingml.document') {
				self::processDocx($template, $recordModel, $fullPath, $documentPath);
			} else {
				throw new \Exception("Unhandled document template file type - {$fileDetails['type']}");
			}

			// save result as format specified in matched template
			if ($template->get('convert_result_to_pdf')) {
				self::convertToPdf($filePath, $fullPath);
				unlink($fullPath);
			}

			// return result path
			return $filePath . DIRECTORY_SEPARATOR . $resultFileName;
		} catch (\Exception $e) {
			\App\Log::warning('DocumentTemplates::generate:error <- ' . print_r($e, true));
			if ($resultFileName && file_exists($filePath . DIRECTORY_SEPARATOR . $resultFileName)) {
				unlink($filePath . DIRECTORY_SEPARATOR . $resultFileName);
			}
			throw $e;
		}
	}

	/**
	 * Converts document to pdf using Libre Office.
	 * 
	 * @param string $filePath Folder to output converted file
	 * @param string $sourceFile File to convert
	 * @param int $retries Number of retries if libreoffice didn't return anything
	 */
	public static function convertToPdf($filePath, $sourceFile, $retries = 3)
	{
		\App\Log::warning("DocumentTemplates::convertToPdf($filePath, $sourceFile)");
		$repeat = 0;
		$success = false;
		while (!$success && $repeat++ < $retries) {
			// use libreoffice to convert to pdf
			$output = \App\Utils::process("/usr/bin/libreoffice --nologo --norestore --invisible --nolockcheck --nodefault --headless --convert-to pdf --outdir " . escapeshellarg($filePath) . " " . escapeshellarg($sourceFile), '/var/www/html', ["/libpng warning: iCCP: known incorrect sRGB profile/"]);
			\App\Log::warning("DocumentTemplates::convertToPdf($filePath, $sourceFile) <- LibreOffice: retry $repeat $output");

			[$output, $error] = explode(', E:', $output);
			if (str_starts_with($output, "O: convert")) {
				// ok, break
				$success = true;
				break;
			}
		}

		if (!$success) {
			throw new \Exception(trim($error) ?: "An error has occurred during conversion to PDF");
		}
	}

	/**
	 * Processes Docx template.
	 * 
	 * @param DocumentTemplates_Record_Model $template
	 * @param Vtiger_Record_Model $recordModel
	 * @param string $filePath
	 * @param string $templatePath
	 */
	public static function processDocx(DocumentTemplates_Record_Model $template, Vtiger_Record_Model $recordModel, string $filePath, string $templatePath)
	{
		\App\Log::warning("DocumentTemplates::processDocx:{$template->getId()}-{$template->get('document_template_variant_name')}/{$recordModel->getModuleName()}.{$recordModel->getId()}/$filePath/$templatePath");

		copy($templatePath, $filePath);
		try {
			$archive = new \ZipArchive();
			if ($archive->open($filePath) === true) {
				$filesToProcess = [];
				for ($i = 0; $i < $archive->numFiles; $i++) {
					['name' => $fileName] = $archive->statIndex($i);
					$baseFileName = basename($fileName);
					if (
						\in_array($baseFileName, ['document.xml', 'footnotes.xml', 'endnotes.xml'])
						|| (str_starts_with($baseFileName, 'footer') && str_ends_with($baseFileName, '.xml'))
						|| (str_starts_with($baseFileName, 'header') && str_ends_with($baseFileName, '.xml'))
					) {
						$filesToProcess[] = $fileName;
					}
				}

				foreach ($filesToProcess as $keyFileName) {
					$message = $archive->getFromName($keyFileName);
					if (!$message) {
						throw new \Exception('Invalid DOCX template');
					}
					$message = preg_replace_callback(
						self::PLACEHOLDER_FIXER_1_REGEXP,
						function ($matches1) {
							$text = preg_replace(self::TAG_REMOVER_REGEXP, '', $matches1[0]);
							return preg_replace_callback(self::PLACEHOLDER_FIXER_2_REGEXP, function ($matches) {
								return '$(' . trim($matches[1]) . ' : ' . preg_replace('/ /', '', $matches[2]) . ')$';
							}, $text);
						},
						$message
					);
					$message = preg_replace_callback(self::IF_FIXER_REGEXP, function ($matches) {
						return preg_replace(self::TAG_REMOVER_REGEXP, '', $matches[0]);
					}, $message);
					$message = preg_replace_callback(self::IF_FIXER_REGEXP2, function ($matches) {
						return preg_replace(self::TAG_REMOVER_REGEXP, '', $matches[0]);
					}, $message);
					if (!$message) {
						throw new \Exception('Invalid processed DOCX template');
					}

					$textParser = \App\TextParser::getInstanceByModel($recordModel);
					$textParser->isHtml = false;
					$textParser->isXml = true;
					// process IF
					$message = \App\Utils\Completions::processIfs($message, $textParser);
					// process images
					['basename' => $baseFileName, 'dirname' => $dirName] = pathinfo($keyFileName);
					$relsFileName = "$dirName/_rels/$baseFileName.rels";
					if ($archive->locateName($relsFileName) !== false) {
						$message = self::processImages($archive, $keyFileName, $relsFileName, $message, $textParser);
					}
					// process TextParser
					$message = $textParser->setContent($message)->parse()->getContent();
					// process SPELLOUT[number]
					$message = \App\Utils\Completions::processSpellout($message);

					$archive->addFromString($keyFileName, $message);
				}

				$archive->close();
			} else {
				throw new \Exception('Can\'t open DOCX template');
			}
		} catch (\Exception $e) {
			// cleanup copied template
			unlink($filePath);
			throw $e;
		}
	}

	/**
	 * Processes Xlsx template.
	 * 
	 * @param DocumentTemplates_Record_Model $template
	 * @param Vtiger_Record_Model $recordModel
	 * @param string $filePath
	 * @param string $templatePath
	 */
	public static function processXlsx(DocumentTemplates_Record_Model $template, Vtiger_Record_Model $recordModel, string $filePath, string $templatePath)
	{
		\App\Log::warning("DocumentTemplates::processXlsx:{$template->getId()}-{$template->get('document_template_variant_name')}/{$recordModel->getModuleName()}.{$recordModel->getId()}/$filePath/$templatePath");

		$ex = new excel();

		try {
			$ex->przetworzSzablon($recordModel->getId(), $templatePath, $filePath);
		} catch (\Throwable $e) {
			// cleanup copied template
			unlink($filePath);
			throw $e;
		}
	}

	public static function getActive($moduleName, Vtiger_Record_Model $recordModel = null)
	{
		$templatesForModule = array_map(function ($element) {
			return Vtiger_Record_Model::getInstanceById($element);
		}, (new \App\QueryGenerator('DocumentTemplates'))->setField('id')->addCondition('mod_name', $moduleName, 'e')->addCondition('do_not_generate', 0, 'e')->createQuery()->column());

		if ($recordModel) {
			$textParser = \App\TextParser::getInstanceByModel($recordModel);
			try {
				foreach ($templatesForModule as $key => $template) {
					if (!empty($template->get('condition')) && !\App\Utils\Completions::processIfCondition(htmlspecialchars_decode($template->get('condition')), $textParser)) {
						unset($templatesForModule[$key]);
					}
				}

				$templatesForModule = array_values($templatesForModule);
			} finally {
				unset($textParser);
			}
		}

		return $templatesForModule;
	}

	public static function checkActive($moduleName, Vtiger_Record_Model $recordModel = null)
	{
		$templatesForModule = (new \App\QueryGenerator('DocumentTemplates'))->setFields(['id', 'condition'])->addCondition('mod_name', $moduleName, 'e')->addCondition('do_not_generate', 0, 'e')->createQuery()->column();

		if ($recordModel) {
			$textParser = \App\TextParser::getInstanceByModel($recordModel);
			try {
				foreach ($templatesForModule as $templateRow) {
					if (empty($templateRow['condition']) || \App\Utils\Completions::processIfCondition(htmlspecialchars_decode($templateRow['condition']), $textParser)) {
						return true;
					}
				}

				return false;
			} finally {
				unset($textParser);
			}
		}

		return count($templatesForModule) > 0;
	}

	private static function processImages(\ZipArchive $archive, string $docFile, string $relsFile, string $docContent, \App\TextParser $textParser)
	{
		\App\Log::warning("DocumentTemplates::processImages:$docFile/$relsFile");

		$doc = new DOMDocument();
		$doc->loadXML($docContent);

		$rels = new DOMDocument();
		$rels->loadXML($archive->getFromName($relsFile));

		$docXpath = new DOMXPath($doc);
		// read namespaces
		foreach ($docXpath->query('namespace::*') as $node) {
			$docXpath->registerNamespace($node->prefix, $node->nodeValue);
		}
		$docXpath->registerNamespace('a', 'http://schemas.openxmlformats.org/drawingml/2006/main');
		$docXpath->registerNamespace('pic', 'http://schemas.openxmlformats.org/drawingml/2006/picture');

		$relsXpath = new DOMXPath($rels);
		$relsXpath->registerNamespace('ns', 'http://schemas.openxmlformats.org/package/2006/relationships');

		// find drawings
		$imagePlaceHolder = '$(';
		foreach ($docXpath->query("//*[contains(@descr, '$imagePlaceHolder')]") as $node) {
			/** @var DOMElement $node */
			$imageSelector = $node->attributes->getNamedItem('descr')->nodeValue;

			// locate image
			$image = $textParser->parseData($imageSelector);
			$replacementImage = \App\Json::decode($image)[0]['path'];

			\App\Log::warning("Found placeholder {$imageSelector} in {$node->nodeName}, image JSON is $image, path is $replacementImage");
			if ($node->nodeName === 'pic:cNvPr') {
				$blipEmbed = $docXpath->query("//a:blip/@r:embed", $node->parentNode)[0];
				$embedId = $blipEmbed->nodeValue;
				['dirname' => $dirName] = pathinfo($docFile);
				$imagePath = $dirName . '/' . $relsXpath->query("//ns:Relationship[@Id='$embedId']/@Target")[0]->nodeValue;
				$archive->addFile($replacementImage, $imagePath);
				\App\Log::warning("Replaced $imagePath with $replacementImage for selector $imageSelector");
			}
		}

		$docContent = preg_replace('/ descr="([^"]*)\$\([^")]*\)\$([^"]*)"/is', ' descr="${1} ${2}"', $docContent, -1, $cnt);
		// $docContent = str_ireplace(' descr="' . $imageSelector . '"', '', $docContent, $cnt);
		\App\Log::warning("Cleared $cnt descriptions");
			

		return $docContent;
	}

	/**
   * Handle upload to Dropbox of document
   * 
   * @param Vtiger_Record_Model $recordModel
   * @param int $documentId Id of document generated by this template
   * @param $sendToDropbox Option for sending to dropbox
   */
  public function dropbox(Vtiger_Record_Model $recordModel, int $documentId, $sendToDropbox = null) {
    \App\Log::warning("DocumentTemplates::dropbox:{$this->getId()}/{$recordModel->getModuleName()}.{$recordModel->getId()}/$documentId/$sendToDropbox");

    $account = \App\Config::dropbox('defaultAccount');

    if (empty($account)) {
      \App\Log::error("DocumentTemplates::dropbox:unspecified account");
      throw new \Exception("No Dropbox account specified");
    } else if (empty($sendToDropbox)) {
      \App\Log::warning("DocumentTemplates::dropbox:unset $sendToDropbox");
      return;
    }  else if ($sendToDropbox == 1) {
      \App\Log::warning("DocumentTemplates::dropbox:disable send");
      return;
    } else if (!\App\Record::isExists($sendToDropbox, 'DropboxDestinations')) {
      \App\Log::error("DocumentTemplates::dropbox:missing $sendToDropbox");
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

    \App\Log::warning("DocumentTemplates::dropbox:uploading $documentPath to $destinationFolder/$fileName on $account");

    \App\Dropbox\Api::upload($account, $documentPath, $destinationFolder, $fileName);

    \App\Log::warning("DocumentTemplates::dropbox:upload finished");
  }
}
