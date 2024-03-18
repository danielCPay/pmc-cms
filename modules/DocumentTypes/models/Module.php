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
 * Class DocumentTypes_Module_Model.
 */
class DocumentTypes_Module_Model extends Vtiger_Module_Model
{
	/**
   * Gets all document types (for workflows)
   * 
   * @return Vtiger_Record_Model
   */
  public static function getAllTypeNumbers() {
    $types = 
      (new \App\QueryGenerator('DocumentTypes'))
        ->setFields(['document_area', 'document_type', 'number'])
        ->setOrder('document_area')
        ->setOrder('document_type')
        ->createQuery()
        ->all();

    return $types;
  }
}
