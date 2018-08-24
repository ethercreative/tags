<?php
/**
 * Tags for Craft 3
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) 2018 Ether Creative
 */

namespace ether\tagManager\elements;

use craft\elements\Tag;
use craft\helpers\UrlHelper;

/**
 * Class TagManager
 *
 * @author  Ether Creative
 * @package ether\tagManager\elements
 */
class TagManager extends Tag
{

	public function getCpEditUrl ()
	{
		return UrlHelper::cpUrl('tag-manager/' . $this->id);
	}

	protected static function defineSources (string $context = null): array
	{
		return array_merge(
			[
				[
					'key'   => '*',
					'label' => \Craft::t('app', 'All Tags'),
				],
				[
					'heading' => \Craft::t('app', 'Groups'),
				]
			],
			parent::defineSources($context)
		);
	}

	protected static function defineTableAttributes (): array
	{
		return [
			'title' => ['label' => \Craft::t('app', 'Title')],
			'group' => ['label' => \Craft::t('app', 'Group')],
			'dateCreated' => ['label' => \Craft::t('app', 'Date Created')],
			'dateUpdated' => ['label' => \Craft::t('app', 'Date Updated')],
		];
	}

	public static function defaultTableAttributes (string $source): array
	{
		$attrs = parent::defaultTableAttributes($source);

		$attrs[] = 'title';
		$attrs[] = 'group';

		return $attrs;
	}

	public static function sortOptions (): array
	{
		$sort = parent::sortOptions();

		$sort['title'] = \Craft::t('app', 'Title');
		$sort[] = [
			'label'     => \Craft::t('app', 'Date Created'),
			'orderBy'   => '{{%elements}}.dateCreated',
			'attribute' => 'dateCreated',
		];
		$sort[] = [
			'label'     => \Craft::t('app', 'Date Updated'),
			'orderBy'   => '{{%elements}}.dateUpdated',
			'attribute' => 'dateUpdated',
		];

		unset($sort['dateCreated']);
		unset($sort['dateUpdated']);
		unset($sort['group']);

		return $sort;
	}

}