<?php

/**
 * DocumentTypesWorkflow.
 *
 * @package   Workflow
 *
 * @copyright DOT Systems
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Michał Jastrzębski <mjastrzebski@dotsystems.pl>
 */
class DocumentTypesWorkflow
{
    /**
	 * Validate paths loop in children
	 *
	 * @param \Vtiger_Record_Model $recordModel
	 */
	public static function validateChildrenPathsLoop(Vtiger_Record_Model $recordModel, $path)
	{
        $documentTypes = Vtiger_RelationListView_Model::getInstance($recordModel, "DocumentTypes");
        $documentTypesRows = $documentTypes->getRelationQuery()->all();
        $documentTypesRecords = $documentTypes->getRecordsFromArray($documentTypesRows);

        foreach ($documentTypesRecords as $id => $docType) {
            $child = Vtiger_Record_Model::getInstanceById($docType->getId());

            $childPath = $child->get('document_type_path');

            if($path === $childPath) {
                throw new Exception('validateChildrenPathsLoop');
            }
            else {
                self::validateChildrenPathsLoop($child, $path);
            }
        }
    }

    /**
	 * Validate paths loop in parents
	 *
	 * @param \Vtiger_Record_Model $recordModel
	 */
	public static function validateParentsPathsLoop(Vtiger_Record_Model $recordModel, $parents)
	{
        $path = $recordModel->get('document_type_path');

        if(in_array($path, $parents)) {
            throw new Exception('validateParentsPathsLoop');
        }
        else {
            array_push($parents, $path);

            if(!empty(($recordModel->get('parent_document_type')))) {
                $parent = Vtiger_Record_Model::getInstanceById($recordModel->get('parent_document_type'));

                self::validateParentsPathsLoop($parent, $parents);
            }
        }
    }

    /**
	 * Refreshes child document types paths
	 *
	 * @param \Vtiger_Record_Model $recordModel
	 */
	public static function refreshDocumentTypesPaths(Vtiger_Record_Model $recordModel)
	{
		$id = $recordModel->getId();
        $path = $recordModel->get('document_type_path');
        $parents = [];

		\App\Log::warning("DocumentTypes::Workflows::refreshDocumentTypesPaths:" . $id);
        \App\Log::trace("DocumentTypes::refreshDocumentTypesPaths:path = $path");

        try {
            self::validateParentsPathsLoop($recordModel, $parents);
            // self::validateChildrenPathsLoop($recordModel, $path);
        }
        catch (Exception $e) {
            VTWorkflowUtils::createBatchErrorEntryRaw("Refreshes document types paths loop", $id, $recordModel->getModuleName(), "Error during processing", $id, 
                "Error occurred while processing record - document types paths loop");
            
            throw $e;
        }

        $documentTypes = Vtiger_RelationListView_Model::getInstance($recordModel, "DocumentTypes");
        $documentTypesRows = $documentTypes->getRelationQuery()->all();
        $documentTypesRecords = $documentTypes->getRecordsFromArray($documentTypesRows);

        foreach ($documentTypesRecords as $id => $docType) {
            $docType = Vtiger_Record_Model::getInstanceById($docType->getId());

            $childType = $docType->get('document_type');
            $spacer = !empty($path) && !empty($childType) ? " / " : "";
            $tryPath = $path . $spacer . $childType;

            $docType->set('document_type_path', $tryPath);
            $docType->save();
        }
	}
}
