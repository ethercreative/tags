<?php
/**
 * Tags for Craft 3
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) 2018 Ether Creative
 */

namespace ether\tagManager;

use craft\base\Element;
use craft\base\Plugin;
use craft\elements\actions\Edit;
use craft\events\RegisterElementActionsEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\web\UrlManager;
use ether\tagManager\elements\actions\Delete;
use ether\tagManager\elements\Tag;
use yii\base\Event;

/**
 * Class Tags
 *
 * @author  Ether Creative
 * @package ether\tags
 */
class TagManager extends Plugin
{

	// Properties
	// =========================================================================

	public $schemaVersion = '1.0.0';

	public $hasCpSettings = false;

	public $hasCpSection  = true;

	// Init
	// =========================================================================

	public function init ()
	{
		parent::init();

		// Components
		// ---------------------------------------------------------------------

//		$this->setComponents([]);

		// Events
		// ---------------------------------------------------------------------

		Event::on(
			UrlManager::class,
			UrlManager::EVENT_REGISTER_CP_URL_RULES,
			[$this, 'onRegisterCpUrlRules']
		);

		Event::on(
			Tag::class,
			Element::EVENT_REGISTER_ACTIONS,
			[$this, 'onRegisterTagActions']
		);

	}

	// Craft
	// =========================================================================

	public function getCpNavItem ()
	{
		$item = parent::getCpNavItem();

		$item['id'] = 'tag-manager';
		$item['url'] = 'tags';

		return $item;
	}

	// Events
	// =========================================================================

	public function onRegisterCpUrlRules (RegisterUrlRulesEvent $event)
	{
		$event->rules['tags'] = ['template' => 'tag-manager/_index'];
		$event->rules['tags/<groupHandle:{handle}>'] = ['template' => 'tag-manager/_index'];

		$event->rules['tags/<groupHandle:{handle}>/new'] = 'tag-manager/cp/edit';
		$event->rules['tags/<groupHandle:{handle}>/new/<siteHandle:{handle}>'] = 'tag-manager/cp/edit';
		$event->rules['tags/<groupHandle:{handle}>/<tagId:\d+>'] = 'tag-manager/cp/edit';
		$event->rules['tags/<groupHandle:{handle}>/<tagId:\d+>/<siteHandle:{handle}>'] = 'tag-manager/cp/edit';
	}

	public function onRegisterTagActions (RegisterElementActionsEvent $event)
	{
		$event->actions[] = Edit::class;
		$event->actions[] = Delete::class;
	}

}