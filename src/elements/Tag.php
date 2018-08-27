<?php
/**
 * Tags for Craft 3
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) 2018 Ether Creative
 */

namespace ether\tagManager\elements;

use craft\helpers\UrlHelper;
use yii\db\Query;

/**
 * Class TagManager
 *
 * @author  Ether Creative
 * @package ether\tagManager\elements
 */
class Tag extends \craft\elements\Tag
{

	// Properties
	// =========================================================================

	/** @var Tag|null */
	public $replaceWith;

	// Methods
	// =========================================================================

	/**
	 * @return null|string
	 * @throws \yii\base\InvalidConfigException
	 */
	public function getCpEditUrl ()
	{
		$url = UrlHelper::cpUrl(
			'tags/' . $this->group->handle . '/' . $this->id
		);

		if (\Craft::$app->isMultiSite)
			$url .= '/' . $this->getSite()->handle;

		return $url;
	}

	public function beforeDelete (): bool
	{
		if ($this->replaceWith === null)
			return parent::beforeDelete();

		if (!parent::beforeDelete())
			return false;

		$db = \Craft::$app->db;
		$transaction = $db->beginTransaction();
		$replaceId = $this->replaceWith->id;

		try {
			// 1. Get all relations for the tag being deleted
			$toReplaceResults = (new Query())
				->select('r.id, r.fieldId, r.sourceId, r.sourceSiteId')
				->from('{{%relations}} r')
				->where(['r.targetId' => $this->id])
				->all();

			// 2. Get all relations for the replacing tag
			$existingResults = (new Query())
				->select('r.id, r.fieldId, r.sourceId, r.sourceSiteId')
				->from('{{%relations}} r')
				->where(['r.targetId' => $replaceId])
				->all();

			// 3. Find all relations to the deleted tag that don't match any
			// relations to the replacing tag
			$existingFilter = [];
			foreach ($existingResults as $result)
				$existingFilter[] =
					$result['fieldId'] . ' ' .
					$result['sourceId'] . ' ' .
					$result['sourceSiteId'];

			$toReplace = [];
			foreach ($toReplaceResults as $result)
			{
				if (!in_array(
					$result['fieldId'] . ' ' .
					$result['sourceId'] . ' ' .
					$result['sourceSiteId'],
					$existingFilter
				)) $toReplace[] = $result['id'];
			}

			// 4. Replace
			foreach ($toReplace as $id)
				$db->createCommand()->update(
					'{{%relations}}',
					[ 'targetId' => $replaceId ],
					[ 'id' => $id ]
				)->execute();

			$transaction->commit();
		} catch (\Throwable $e) {
			$transaction->rollBack();
			throw $e;
		}

		return true;
	}

	protected static function defineSources (string $context = null): array
	{
		$sources = [
			[
				'key'   => '*',
				'label' => \Craft::t('app', 'All Tags'),
			],
			[
				'heading' => \Craft::t('app', 'Groups'),
			]
		];

		foreach (\Craft::$app->getTags()->getAllTagGroups() as $tagGroup)
		{
			$sources[] = [
				'key'      => 'taggroup:' . $tagGroup->id,
				'label'    => \Craft::t('site', $tagGroup->name),
				'data'     => ['handle' => $tagGroup->handle],
				'criteria' => ['groupId' => $tagGroup->id],
			];
		}

		return $sources;
	}

	protected static function defineTableAttributes (): array
	{
		return [
			'title'       => ['label' => \Craft::t('app', 'Title')],
			'group'       => ['label' => \Craft::t('app', 'Group')],
			'dateCreated' => ['label' => \Craft::t('app', 'Date Created')],
			'dateUpdated' => ['label' => \Craft::t('app', 'Date Updated')],
		];
	}

	protected static function defineSortOptions (): array
	{
		return [
			'title' => \Craft::t('app', 'Title'),
			[
				'label'     => \Craft::t('app', 'Date Created'),
				'orderBy'   => 'elements.dateCreated',
				'attribute' => 'dateCreated',
			],
			[
				'label'     => \Craft::t('app', 'Date Updated'),
				'orderBy'   => 'elements.dateUpdated',
				'attribute' => 'dateUpdated',
			]
		];
	}

}