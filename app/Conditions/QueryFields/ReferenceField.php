<?php

namespace App\Conditions\QueryFields;

/**
 * Reference Query Field Class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class ReferenceField extends BaseField
{
	public function getTables()
	{
		return $this->queryGenerator->getReference($this->fieldModel->getName());
	}

	/**
	 * Get related column name.
	 *
	 * @return string[]
	 */
	public function getRelatedTableName()
	{
		if ($this->related) {
			if (\App\Config::performance('SEARCH_REFERENCE_BY_AJAX')) {
				return [$this->fieldModel->getTableName() . $this->related['sourceField'] . '.' . $this->fieldModel->getColumnName()];
			}
			$relatedModuleModel = \Vtiger_Module_Model::getInstance($this->related['relatedModule']);
			$fieldModel = $relatedModuleModel->getField($this->related['relatedField']);
			return $this->getRelatedTables($fieldModel->getReferenceList(), $this->related['relatedField']);
		}
		return $this->getRelatedTables($this->getTables(), $this->fieldModel->getName());
	}

	/**
	 * Get formatted column references from related records.
	 *
	 * @param array  $modules
	 * @param string $fieldName
	 *
	 * @return string[]
	 */
	public function getRelatedTables(array $modules, string $fieldName): array
	{
		$relatedTableName = [];
		foreach ($modules as $moduleName) {
			$formattedTables = [];
			$entityFieldInfo = \App\Module::getEntityInfo($moduleName);
			$relatedModuleModel = \Vtiger_Module_Model::getInstance($moduleName);
			$relTableIndexes = $relatedModuleModel->getEntityInstance()->tab_name_index;
			foreach ($entityFieldInfo['fieldnameArr'] as $column) {
				if ($relField = $relatedModuleModel->getFieldByColumn($column)) {
					$referenceTable = $relField->getTableName() . $fieldName;
					$this->queryGenerator->addJoin([
						'LEFT JOIN',
						"{$relField->getTableName()} {$referenceTable}",
						"{$this->getColumnName()} = {$referenceTable}.{$relTableIndexes[$relField->getTableName()]}"
					]);
					$formattedTables[] = "{$referenceTable}.{$column}";
					$this->queryGenerator->addJoin([
						'LEFT JOIN',
						"vtiger_crmentity {$referenceTable}_entity",
						"{$referenceTable}_entity.crmid = {$referenceTable}.{$relTableIndexes[$relField->getTableName()]}"
					]);
				}
			}
			$relatedTableName[$moduleName] = \count($formattedTables) > 1 ? new \yii\db\Expression('CONCAT(' . implode(",' ',", $formattedTables) . ')') : current($formattedTables);
		}
		return $relatedTableName;
	}

	/**
	 * Auto operator.
	 *
	 * @return array
	 */
	public function operatorA()
	{
		if (\App\Config::performance('SEARCH_REFERENCE_BY_AJAX')) {
			if (false === strpos($this->value, '##')) {
				return [$this->getColumnName() => $this->value];
			}
			$condition = ['or'];
			foreach (explode('##', $this->value) as $value) {
				$condition[] = [$this->getColumnName() => $value];
			}
			return $condition;
		}
		return parent::operatorA();
	}

	/**
	 * Equals operator.
	 *
	 * @return array
	 */
	public function operatorE()
	{
		$condition = ['or'];
		$value = $this->getValue();
		foreach ($this->getRelatedTableName() as $formattedName) {
			if ($value === '\\') {
				$condition[] = ['or', [$formattedName => null], [$formattedName => ''], [substr($formattedName, 0, strpos($formattedName, '.')) . '_entity.deleted' => 1]];
			} else {
				$condition[] = ['=', $formattedName, $value];
			}
		}
		return $condition;
	}

	/**
	 * Search for multiple patterns
	 * Separator - |.
	 *
	 * @return array
	 */
	public function operatorMlti()
	{
		$conditions = ['or'];
		$values = array_filter(array_map(function ($val) { return trim($val, ' "'); }, preg_split('/("[^"]*")|\h+/', $this->getValue(), -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE)));
		foreach ($this->getRelatedTableName() as $formattedName) {
			$subconditions = ['or'];
			foreach ($values as $value) {
				$value = trim($value);

				$subconditions[] = ['like', $formattedName, "%{$value}%", false];
			}
			$conditions[] = $subconditions;
		}
		return $conditions;
	}

	/**
	 * Search for all words in any order, ignore national characters and accents
	 * Grouping - single quotes.
	 *
	 * @return array
	 */
	public function operatorSmlr()
	{
		$conditions = ['and'];
		$values = array_filter(array_map(function ($val) { return trim($val, ' \''); }, preg_split('/(\'[^\']*\')|\h+/', $this->getValue(), -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE)));
		foreach ($values as $value) {
			foreach (explode(' ', $value) as $value) {
				$value = trim($value);
				foreach ($this->getRelatedTableName() as $formattedName) {
					$conditions[] = $formattedName . ' like ' . \App\Db::getInstance()->quoteValue('%' . $value . '%') . ' collate utf8_unicode_520_ci';
				}
			}
		}
		return $conditions;
	}

	/**
	 * Equals Id operator.
	 *
	 * @return array
	 */
	public function operatorEid()
	{
		return [$this->getColumnName() => $this->getValue()];
	}

	/**
	 * Not equal operator.
	 *
	 * @return array
	 */
	public function operatorN()
	{
		$condition = ['or'];
		foreach ($this->getRelatedTableName() as $formattedName) {
			$condition[] = ['<>', $formattedName, $this->getValue()];
		}
		return $condition;
	}

	/**
	 * Starts with operator.
	 *
	 * @return array
	 */
	public function operatorS()
	{
		$condition = ['or'];
		foreach ($this->getRelatedTableName() as $formattedName) {
			$condition[] = ['like', $formattedName, $this->getValue() . '%', false];
		}
		return $condition;
	}

	/**
	 * Ends with operator.
	 *
	 * @return array
	 */
	public function operatorEw()
	{
		$condition = ['or'];
		foreach ($this->getRelatedTableName() as $formattedName) {
			$condition[] = ['like', $formattedName, '%' . $this->getValue(), false];
		}
		return $condition;
	}

	/**
	 * Contains operator.
	 *
	 * @return array
	 */
	public function operatorC()
	{
		$condition = ['or'];
		$value = $this->getValue();
		foreach ($this->getRelatedTableName() as $formattedName) {
			if ($value === '\\') {
				$condition[] = ['or', [$formattedName => null], [$formattedName => ''], [substr($formattedName, 0, strpos($formattedName, '.')) . '_entity.deleted' => 1]];
			} else {
				$condition[] = ['like', $formattedName, $value];
			}
		}
		return $condition;
	}

	/**
	 * Does not contain operator.
	 *
	 * @return array
	 */
	public function operatorK()
	{
		$condition = ['or'];
		foreach ($this->getRelatedTableName() as $formattedName) {
			$condition[] = ['not like', $formattedName, $this->getValue()];
		}
		return $condition;
	}

	/**
	 * Is empty operator.
	 *
	 * @return array
	 */
	public function operatorY()
	{
		return [
			'or',
			[$this->getColumnName() => null],
			['=', $this->getColumnName(), ''],
			['=', $this->getColumnName(), 0],
		];
	}

	/**
	 * Is not empty operator.
	 *
	 * @return array
	 */
	public function operatorNy()
	{
		return [
			'and',
			['not', [$this->getColumnName() => null]],
			['<>', $this->getColumnName(), ''],
			['<>', $this->getColumnName(), 0],
		];
	}

	/**
	 * Reference equals operator.
	 *
	 * @return array
	 */
	public function operatorRe()
	{
		return $this->getColumnName() . ' = ' . $this->getValue();
	}

	/**
	 * Reference not equals operator.
	 *
	 * @return array
	 */
	public function operatorRne()
	{
		return $this->getColumnName() . ' != ' . $this->getValue();
	}

	/**
	 * Get order by.
	 *
	 * @param mixed $order
	 *
	 * @return array
	 */
	public function getOrderBy($order = false)
	{
		$condition = [];
		if ($order && 'DESC' === strtoupper($order)) {
			foreach ($this->getRelatedTableName() as $formattedName) {
				$condition[(string) $formattedName] = SORT_DESC;
			}
		} else {
			foreach ($this->getRelatedTableName() as $formattedName) {
				$condition[(string) $formattedName] = SORT_ASC;
			}
		}
		return $condition;
	}
}
