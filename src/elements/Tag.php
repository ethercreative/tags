<?php
/**
 * Tags for Craft 3
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) 2018 Ether Creative
 */

namespace ether\tagManager\elements;

use craft\helpers\UrlHelper;

/**
 * Class TagManager
 *
 * @author  Ether Creative
 * @package ether\tagManager\elements
 */
class Tag extends \craft\elements\Tag
{

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