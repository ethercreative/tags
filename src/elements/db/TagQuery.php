<?php
/**
 * Tags for Craft CMS
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) 2019 Ether Creative
 */

namespace ether\tagManager\elements\db;

use ether\tagManager\TagManager;
use yii\db\Expression;

/**
 * Class TagQuery
 *
 * @author  Ether Creative
 * @package ether\tagManager\elements\db
 */
class TagQuery extends \craft\elements\db\TagQuery
{

	protected function beforePrepare (): bool
	{
		if (!TagManager::getInstance()->getSettings()->enableUsage)
			return parent::beforePrepare();

		$getUsage = new Expression(
			'(SELECT COUNT(*) FROM (SELECT [[r.sourceId]], [[r.sourceSiteId]] FROM {{%relations}} r WHERE [[r.targetId]] = [[elements.id]] GROUP BY [[r.sourceId]], [[r.sourceSiteId]]) as usage) as [[usage]]'
		);

		$this->addSelect($getUsage);
		$this->subQuery->addSelect($getUsage);

		return parent::beforePrepare();
	}

}