<?php
/**
 * Looped hierarchy handler.
 *
 * @package Handler
 *
 * @copyright DOT Systems sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Michał Kamiński <mkaminski@dotsystems.pl>
 */
/**
 * DocumentTypes_Loop_Handler class.
 */
class DocumentTypes_Loop_Handler
{
	const QUERY = 'WITH RECURSIVE dt AS (
		SELECT *
		FROM u_yf_documenttypes
		WHERE documenttypesid = :currentId
		UNION ALL
		SELECT dt1.*
		FROM u_yf_documenttypes dt1 JOIN dt dt2 ON dt2.documenttypesid = dt1.parent_document_type
	) 
	SELECT * FROM dt WHERE documenttypesid = :parentId';

	/**
	 * EditViewPreSave handler function.
	 *
	 * @param App\EventHandler $eventHandler
	 */
	public function editViewPreSave(App\EventHandler $eventHandler)
	{
		$response = ['result' => true];
		
		$recordModel = $eventHandler->getRecordModel();
		if (!$recordModel->isNew() && \App\Record::isExists($recordModel->get('parent_document_type'))) {
			$documentTypeId = $recordModel->getId();
			$parentDocumentType = $recordModel->get('parent_document_type');

			// find if there is a document type that has this document type as a parent
			$db = \App\Db::getInstance();
			if (!empty($db->createCommand(self::QUERY, ['currentId' => $documentTypeId, 'parentId' => $parentDocumentType])->queryOne())) {

			$response = [
				'result' => false,
				'hoverField' => 'parent_document_type',
				'message' => App\Language::translate('LBL_HIERARCHY_LOOP_ERROR', $recordModel->getModuleName())
			];
			}
		}

		return $response;
	}
}
