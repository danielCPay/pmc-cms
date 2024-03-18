<?php

/**
 * Create backup.
 *
 * @package   Action
 *
 * @copyright DOT Systems Sp. z o.o.
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Michał Kamiński <mkaminski@dotsystems.pl>
 */
class Settings_Backup_Backup_Action extends Settings_Vtiger_Basic_Action
{
	use \App\Controller\ExposeMethod;

	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('createBackup');
	}

	/**
	 * Action to export module.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\AppException
	 */
	protected function createBackup(App\Request $request)
	{
		$catalogPath = \App\Utils\Backup::getBackupCatalogPath();
		if (empty($catalogPath) || !App\Fields\File::isAllowedDirectory($catalogPath)) {
			throw new \App\Exceptions\NoPermitted('ERR_NOT_ACCESSIBLE');
		}

		\App\Log::info("Backup started, target: $catalogPath");

		$this->git("checkout -- .", $catalogPath);
		$this->git("pull --rebase", $catalogPath);

		$filteredTables = [
			'/^l_yf_/i',
			'/^u_yf_browsinghistory$/i',
			'/^o_yf_access/i',
			'/^roundcube_/i',
			'/^vtiger_loginhistory$/i',
			'/^w_yf_portal_session$/i',
			'/modtracker/i',
			'/^vw_/i',
			'/^u_yf_import/i',
		];

		$this->exportModules($catalogPath);

		$this->exportSchema($catalogPath, $filteredTables);

		$filteredData = array_merge($filteredTables, [
			'a_yf_bruteforce_blocked',
			'a_yf_settings_access',
			'u_yf_mail_autologin',
			'u_yf_notification',
			'/^vtiger_activity/i',
			'vtiger_attachments',
			'vtiger_notes',
			'/^vtiger_ossmail/i',
			'vtiger_seattachmentsrel',
			'vtiger_senotesrel',
			'o_yf_csrf',
			'/^rep_/i',
			's_yf_mail_queue',
		]);
		foreach ([] as $moduleName) {
			$entityModel = \CRMEntity::getInstance($moduleName);
			$filteredData = array_merge($filteredData, $entityModel->tab_name);
		}
		$filteredData = array_diff($filteredData, ['vtiger_crmentity', '/^vtiger_crmentity$/i', '/ossmail/i',]);
		$this->exportData($catalogPath, $filteredData);

		$this->exportTemplates($catalogPath);

		$this->exportCustomTranslations($catalogPath);

		$this->git('add .', $catalogPath);
		$this->git('commit -m "backup ' . date('Y.m.d H:i') . '"', $catalogPath);
		$this->git('push', $catalogPath);

		\App\Log::info('Backup done');

		header('Location: index.php?module=Backup&parent=Settings&view=Index');
	}

	private function exportModules(string $baseBackupPath)
	{
		$moduleBackupPath = $baseBackupPath . DIRECTORY_SEPARATOR . 'modules';
		$moduleArchive = $baseBackupPath . DIRECTORY_SEPARATOR . 'modules.zip';

		\App\Log::info("Exporting modules: $moduleBackupPath/$moduleArchive");

		$modules = Settings_ModuleManager_Module_Model::getAll();
		$package = new vtlib\PackageExport();

		// clean data, ensure directory
		if (is_dir($moduleBackupPath)) {
			\vtlib\Functions::recurseDelete($moduleBackupPath);
		}
		if (is_file($moduleArchive)) {
			unlink($moduleArchive);
		}
		$result = mkdir($moduleBackupPath, 0777, true);

		if (!$result) {
			throw new \App\Exceptions\AppException("ERR_CREATE_DIR_FAILURE||$moduleBackupPath");
		}

		foreach ($modules as $id => $module) {
			if (!$module->isExportable()) {
				continue;
			}

			$fileName = sprintf('%s.zip', $module->name);
			$modulePath = $moduleBackupPath . DIRECTORY_SEPARATOR . $module->name;
			\App\Log::trace("Backing up $module->name to $modulePath...");
			$package->export($module, '', $fileName, false, true);

			\App\Log::trace("Extracting $fileName...");
			if (!is_dir($modulePath)) {
				$result = mkdir($modulePath, 0777, true);

				if (!$result) {
					throw new \App\Exceptions\AppException("ERR_CREATE_DIR_FAILURE||$modulePath");
				}
			}
			$zip = \App\Zip::openFile($fileName);
			$zip->extractTo($modulePath);
			unlink($fileName);
		}
		\App\Log::trace('Compressing all modules...');
		$zip = \App\Zip::createFile($moduleArchive);
		$zip->addDirectory($moduleBackupPath, 'modules', true);
		$zip->close();
	}

	private function exportSchema(string $baseBackupPath, array &$exclusions)
	{
		$sqlBackupPath = $baseBackupPath . DIRECTORY_SEPARATOR . 'sql';
		$sqlArchive = $baseBackupPath . DIRECTORY_SEPARATOR . 'sql.zip';

		\App\Log::info("Exporting schema: $sqlBackupPath/$sqlArchive");

		// clean data, ensure directory
		if (is_dir($sqlBackupPath)) {
			\vtlib\Functions::recurseDelete($sqlBackupPath);
		}
		if (is_file($sqlArchive)) {
			unlink($sqlArchive);
		}
		$result = mkdir($sqlBackupPath, 0777, true);

		if (!$result) {
			throw new \App\Exceptions\AppException("ERR_CREATE_DIR_FAILURE||$sqlBackupPath");
		}

		$tablesReader = \App\Db::getInstance()->createCommand('SHOW TABLES')->query();
		while ($table = $tablesReader->readColumn(0)) {
			if (!empty($exclusions)) {
				$filtered = false;
				foreach ($exclusions as $key => $exclusion) {
					if (preg_match($exclusion, $table)) {
						$filtered = true;
						break;
					}
				}
				if ($filtered) {
					continue;
				}
			}
			$sqlPath = $sqlBackupPath . DIRECTORY_SEPARATOR . $table . '.sql';
			\App\Log::trace("Backing up $table to $sqlPath...");
			$createTable = preg_replace('/ ?AUTO_INCREMENT=\d+/i', '', \App\Db::getInstance()->createCommand('SHOW CREATE TABLE ' . $table)->queryOne()['Create Table']);

			$file = fopen($sqlPath, 'w');
			fwrite($file, $createTable);
			fclose($file);
		}

		\App\Log::trace('Compressing all sql...');
		$zip = \App\Zip::createFile($sqlArchive);
		$zip->addDirectory($sqlBackupPath, 'sql', true);
		$zip->close();
	}

	private function exportData(string $baseBackupPath, array &$exclusions)
	{
		$sqlBackupPath = $baseBackupPath . DIRECTORY_SEPARATOR . 'sql-data';
		$sqlArchive = $baseBackupPath . DIRECTORY_SEPARATOR . 'sql-data.zip';

		\App\Log::info("Exporting data: $sqlBackupPath/$sqlArchive");

		// clean data, ensure directory
		if (is_dir($sqlBackupPath)) {
			\vtlib\Functions::recurseDelete($sqlBackupPath);
		}
		if (is_file($sqlArchive)) {
			unlink($sqlArchive);
		}
		$result = mkdir($sqlBackupPath, 0777, true);

		if (!$result) {
			throw new \App\Exceptions\AppException("ERR_CREATE_DIR_FAILURE||$sqlBackupPath");
		}

		$tablesReader = \App\Db::getInstance()->createCommand('SHOW TABLES')->query();
		while ($table = $tablesReader->readColumn(0)) {
			if (!empty($exclusions)) {
				$filtered = false;
				foreach ($exclusions as $key => $exclusion) {
					if (($exclusion[0] === '/' && preg_match($exclusion, $table)) || strcasecmp($exclusion, $table) === 0) {
						$filtered = true;
						break;
					}
				}
				if ($filtered) {
					continue;
				}
			}
			$sqlPath = $sqlBackupPath . DIRECTORY_SEPARATOR . $table . '.csv';

			\App\Log::trace("Backing up $table to $sqlPath...");

			$file = fopen($sqlPath, 'w');
			$dataReader = \App\Db::getInstance()->createCommand('select * from ' . $table)->query();
			$headerExported = false;
			while ($row = $dataReader->read()) {
				if (!$headerExported) {
					fputcsv($file, array_keys($row), ',', '"', "\0");
					$headerExported = true;
				}
				foreach ($row as $key => $value) {
					$row[$key] = $value === null ? 'NULL' : $value;
				}
				fputcsv($file, $row, ',', '"', "\0");
			}
			fclose($file);
		}

		\App\Log::trace('Compressing all data...');
		$zip = \App\Zip::createFile($sqlArchive);
		$zip->addDirectory($sqlBackupPath, 'sql-data', true);
		$zip->close();
	}

	private function exportTemplates(string $baseBackupPath)
	{
		$templateBackupPath = $baseBackupPath . DIRECTORY_SEPARATOR . 'templates';
		$templateArchive = $baseBackupPath . DIRECTORY_SEPARATOR . 'templates.zip';

		\App\Log::info("Exporting templates: $templateBackupPath/$templateArchive");

		// clean data, ensure directory
		if (is_dir($templateBackupPath)) {
			\vtlib\Functions::recurseDelete($templateBackupPath);
		}
		if (is_file($templateArchive)) {
			unlink($templateArchive);
		}
		$result = mkdir($templateBackupPath, 0777, true);

		if (!$result) {
			throw new \App\Exceptions\AppException("ERR_CREATE_DIR_FAILURE||$templateBackupPath");
		}

		$files = scandir('storage/Templates');
		foreach ($files as $file) {
			if ($file === '.' || $file === '..') {
				continue;
			}

			copy("storage/Templates/$file", $templateBackupPath . DIRECTORY_SEPARATOR . basename($file));
		}
		\App\Log::trace('Compressing all modules...');
		$zip = \App\Zip::createFile($templateArchive);
		$zip->addDirectory($templateBackupPath, 'templates', true);
		$zip->close();
	}

	private function exportCustomTranslations(string $baseBackupPath)
	{
		$customTranslationsArchive = $baseBackupPath . DIRECTORY_SEPARATOR . 'custom-translations.zip';

		\App\Log::info("Exporting templates: $customTranslationsArchive");

		// clean data
		if (is_file($customTranslationsArchive)) {
			unlink($customTranslationsArchive);
		}
		
		\App\Log::trace('Compressing all custom translations...');
		$zip = \App\Zip::createFile($customTranslationsArchive);
		$zip->addDirectory('custom/languages', 'translations', true);
		$zip->close();
	}

	private function git(string $operation, string $dir)
	{
		$output = \App\Utils::process("git $operation", $dir, true);
		\App\Log::warning($output);
	}

	/**
	 * {@inheritdoc}
	 */
	public function validateRequest(App\Request $request)
	{
		$request->validateWriteAccess();
	}
}
