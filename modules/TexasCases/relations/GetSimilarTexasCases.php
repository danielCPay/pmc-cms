<?php
/**
 * Special relation for similar TexasCases
 *
 * @package   Relation
 *
 * @copyright DOT Systems sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Michał Kamiński <mkaminski@dotsystems.pl>
 */
use App\Relation\RelationInterface;

/**
 * TexasCases_GetSimilarTexasCases_Relation class.
 */
class TexasCases_GetSimilarTexasCases_Relation implements RelationInterface
{
	/** {@inheritdoc} */
	public function getRelationType(): int
	{
		return Vtiger_Relation_Model::RELATION_O2M;
	}

	/** {@inheritdoc} */
	public function getQuery()
	{
    // get Similar TexasCases record (if exists)
    $similarCasesId = $this->relationModel->get('parentRecord')->get('similar_texascases');
    if (\App\Record::isExists($similarCasesId, 'SimilarTexasCases')) {
      $queryGenerator = $this->relationModel->getQueryGenerator();

			$queryGenerator->addNativeCondition(['u_yf_texascases.similar_texascases' => $similarCasesId]);
			$queryGenerator->addNativeCondition(['!=', 'u_yf_texascases.texascasesid', $this->relationModel->get('parentRecord')->getId()]);
    } else {
      // ensure query doesn't return anything
      $queryGenerator = $this->relationModel->getQueryGenerator();
      $queryGenerator->addNativeCondition(['u_yf_texascases.similar_texascases' => -1]);
    }

    return $queryGenerator;
	}

	/** {@inheritdoc} */
	public function delete(int $sourceRecordId, int $destinationRecordId): bool
	{
		throw new \App\Exceptions\AppException("Operation unsupported for this type of relation");
	}

	/** {@inheritdoc} */
	public function create(int $sourceRecordId, int $destinationRecordId): bool
	{
		throw new \App\Exceptions\AppException("Operation unsupported for this type of relation");
	}

	/** {@inheritdoc} */
	public function transfer(int $relatedRecordId, int $fromRecordId, int $toRecordId): bool
	{
		throw new \App\Exceptions\AppException("Operation unsupported for this type of relation");
	}
}
