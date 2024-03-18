<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com, DOT Systems
 * *********************************************************************************** */

/**
 * Vtiger Paging Offset Model Class.
 * 
 * Partial class for use in API for Related Records. Will fail in UI.
 */
class Vtiger_PagingOffset_Model extends Vtiger_Paging_Model
{
	const DEFAULT_OFFSET = 0;

	public function getStartIndex()
	{
		if (!$this->has('offset')) {
			$this->set('offset', self::DEFAULT_OFFSET);
		}

		return $this->get('offset');
	}
}
