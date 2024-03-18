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
 * Class Portfolios_Module_Model.
 */
class Portfolios_Module_Model extends Vtiger_Module_Model
{
  public static function resetToNew() {
    \App\Log::warning("Portfolios::resetToNew");

    // use Query Generator to get all portfolios with "In Underwriting" status, that have no related Claims in "Pending Underwriting", "Pending Approval", "In Underwriting", "Approved", "Preapproved" status.
    $queryGenerator = new \App\QueryGenerator('Portfolios');
    $queryGenerator->setFields(['id']);
    $queryGenerator->addCondition('portfolio_status', 'In Underwriting', 'e');

    // use query generator to generate query that will return portfolio_id from all claims that are in "Pending Underwriting", "Pending Approval", "In Underwriting", "Approved", "Preapproved" status.
    $queryGenerator2 = new \App\QueryGenerator('Claims');
    $queryGenerator2->setFields(['portfolio']);
    $queryGenerator2->addCondition('onboarding_status', ['Pending Underwriting', 'Pending Approval', 'In Underwriting', 'Approved', 'Preapproved'], 'e');

    $queryGenerator->addNativeCondition(['not in', 'portfoliosid', $queryGenerator2->createQuery()]);
    $portfolioIds = $queryGenerator->createQuery()->column();

    \App\Log::warning("Portfolios::cron::Portfolios_ResetToNew_Cron:updating " . count($portfolioIds) . " portfolios");

    foreach ($portfolioIds as $portfolioId) {
      \App\Log::warning("Portfolios::cron::Portfolios_ResetToNew_Cron:resetting portfolio $portfolioId");

      $portfolio = Vtiger_Record_Model::getInstanceById($portfolioId, 'Portfolios');
      $portfolio->set('portfolio_status', 'New');
      $portfolio->save();
    }
  }
}
