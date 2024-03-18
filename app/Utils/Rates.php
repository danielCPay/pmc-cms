<?php

namespace App\Utils;

/**
 * Rates class.
 *
 * @copyright DOT Systems
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Michał Jastrzębski
 */
class Rates
{
    /**
	 * Get currency rate for selected date
	 *
	 * @param \$currency
	 * @param \$date
	 */
	 public static function getCurrencyRate($currency, $date)
	 {
		if(empty($currency) || empty($date)) {
			return NULL;
		}
		else if($currency == 'PLN') {
			return 1;
		}

		$row = (new \App\Db\Query())->select(['u_#__currencyrates.rate_to_pln'])->from('u_#__currencyrates')
			->innerJoin('vtiger_crmentity', 'vtiger_crmentity.crmid=currencyratesid')
			->where(['vtiger_crmentity.deleted' => 0])->andWhere(['<=', 'valid_from', $date])->andWhere(['=', 'currency_code', $currency])
			->orderBy(['valid_from' => SORT_DESC])->limit(1)->one();

		$rate = NULL;
		if(!empty($row)) {
			$rate = $row['rate_to_pln'];
		}
		
		// echo "  rate: " .print_r($rate, true) . PHP_EOL;

		return $rate;
	 }

	 /**
	 * Get days between dates
	 *
	 * @param \$start
	 * @param \$end
	 */
	 private static function countDays($start, $end)
	 {
		return round(($end - $start)/(60*60*24));
	 }

	 /**
	 * Get estimated interest rate from claim request
	 *
	 * @param \$dStart
	 * @param \$dEnd
	 * @param \$claimValue
	 */
	 public static function getEstimatedInterestRate($dStart, $dEnd, $claimValue)
	 {
		// Pre-boarding claim request: Estimated interest from claim request - Calculated for number of days from 
		// [Claim request filling date (before onboarding)] to [current day + 30 days] using 
		// the algorithm “Calculating estimated interest from claim request”

		$returnValue = 0;

		// echo "dateStart: " .print_r($dStart, true) . PHP_EOL;
		// echo "dateEnd: " .print_r($dEnd, true) . PHP_EOL;
		// echo "claimValue: " .print_r($claimValue, true) . PHP_EOL;

		if(empty($dStart) || empty($dEnd) || empty($claimValue)) {
			return NULL;
		}
		else if($claimValue == 0) {
			return 0;
		}

		$dateStart = strtotime($dStart);
		$dateEnd = strtotime($dEnd);

		// $dateStart = strtotime('1998-04-15');
		// $dateEnd = strtotime('2021-04-15');
		// $claimValue = 100;

		$rates = (new \App\Db\Query())->select(['u_#__statutoryinterestlate.interest_valid_from', 'u_#__statutoryinterestlate.interest_rate'])
			->from('u_#__statutoryinterestlate')
			->innerJoin('vtiger_crmentity', 'vtiger_crmentity.crmid=statutoryinterestlateid')
			->where(['vtiger_crmentity.deleted' => 0])
			->orderBy(['interest_valid_from' => SORT_DESC])->all();

		// echo "rates: " .print_r($rates, true) . PHP_EOL;

		$lastValidFrom = NULL;

		foreach ($rates as $row) {

			$interestValidFrom = strtotime($row['interest_valid_from']);

			if($dateStart >= $interestValidFrom || $dateEnd > $interestValidFrom) {
				
				$days = 0;

				if ($dateStart >= $interestValidFrom) {
					if ($lastValidFrom != NULL && $lastValidFrom < $dateEnd) {
						$days = \App\Utils\Rates::countDays($dateStart, $lastValidFrom);
					}
					else {
						$days = \App\Utils\Rates::countDays($dateStart, $dateEnd);
					}
				}
				else if ($dateEnd > $interestValidFrom) {
					if ($lastValidFrom != NULL && $lastValidFrom < $dateEnd) {
						$days = \App\Utils\Rates::countDays($interestValidFrom, $lastValidFrom);
					}
					else {
						$days = \App\Utils\Rates::countDays($interestValidFrom, $dateEnd);
					}
				}

				if($days !== 0) {
					$returnValue = $returnValue + round(($claimValue * ($row['interest_rate']/100)/365) * $days, 2);
				}

				// echo "vf: " .print_r(date('Y-m-d', $interestValidFrom), true) . PHP_EOL;
				// echo "days: " .print_r($days, true) . PHP_EOL;
				// echo "rate: " .print_r($row['interest_rate'], true) . PHP_EOL;
				// echo "claim: " .print_r(round(($claimValue * ($row['interest_rate']/100)/365) * $days, 2), true) . PHP_EOL;
				// echo "ret: " .print_r($returnValue, true) . PHP_EOL;

				if($dateStart >= $interestValidFrom) {
					break;
				}
			}

			$lastValidFrom = $interestValidFrom;
		}

		// echo "returnValue: " .print_r($returnValue, true) . PHP_EOL;
		
		return $returnValue;
	 }
}
