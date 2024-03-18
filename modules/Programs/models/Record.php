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
 * Class Programs_Record_Model.
 */
class Programs_Record_Model extends Vtiger_Record_Model
{
  public function parseParametersForMonthStepUps() {
    $id = $this->getId();
    $algorithmParameters = $this->get('algorithm_parameters');
    $factorFee = $this->get('factor_fee_perc');
    \App\Log::warning("Programs::parseParametersForMonthStepUps:$id/$algorithmParameters");

    $syntaxRE = '/^(?<thresholds>(up to \d+ Months?: \d+((\.|,)\d+)?%\s*)+)more: \d+((\.|,)\d+)?%$/is'; // ignore case, match new lines as spaces
    if (!preg_match($syntaxRE, $algorithmParameters, $matches)) {
      throw new \Exception('Wrong syntax of Algorithm Parameters');
    }

    $thresholds = [];
    $lastThreshold = false;
    $thresholdRE = '/up to (?<months>\d+) Months?: (?<percent>\d+((\.|,)\d+)?)%/is';
    preg_match_all($thresholdRE, $matches['thresholds'], $matches, PREG_SET_ORDER);
    foreach ($matches as $match) {
      $threshold = [
        'months' => $match['months'],
        'percent' => str_replace(',', '.', $match['percent']),
      ];

      if ($lastThreshold) {
        if ($lastThreshold['months'] >= $threshold['months']) {
          throw new \Exception('Wrong syntax of Algorithm Parameters - months should be in ascending order');
          break;
        }
        if ($lastThreshold['percent'] >= $threshold['percent']) {
          throw new \Exception('Wrong syntax of Algorithm Parameters - percentages should be in ascending order');
          break;
        }
      } else if ($threshold['percent'] != $factorFee) {
        throw new \Exception('Wrong syntax of Algorithm Parameters - first threshold percentage should be equal to Factor Fee %');
      }
      $thresholds[] = $lastThreshold = $threshold;
    }

    $moreRE = '/more: (?<more>\d+((\.|,)\d+)?)%/i';
    preg_match($moreRE, $algorithmParameters, $matches);
    $more = str_replace(',', '.', $matches['more']);

    if ($lastThreshold['percent'] >= $more) {
      throw new \Exception('Wrong syntax of Algorithm Parameters - "more" percentage should be greater than last threshold');
    }

    for ($i = 0; $i < count($thresholds); $i++) {
      $thresholds[$i]['percent'] = $i < count($thresholds) - 1 ? $thresholds[$i + 1]['percent'] : $more;
    }

    return $thresholds;
  }
}
