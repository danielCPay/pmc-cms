<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * ********************************************************************************** */

class Portfolios_Detail_View extends Vtiger_Detail_View
{
  /**
   * {@inheritdoc}
   */
  public function checkPermission(App\Request $request)
  {
    parent::checkPermission($request);

    $userModel = \App\User::getCurrentUserModel();
    switch ($userModel->getRole()) {
      case Cases_ListView_Model::PRE_SUIT_ROLE:
        throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
    }
  }
}
