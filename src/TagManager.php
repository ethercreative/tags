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
use craft\events\RegisterCpNavItemsEvent;
use craft\events\RegisterElementActionsEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\web\twig\variables\Cp;
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

		Event::on(
			Cp::class,
			Cp::EVENT_REGISTER_CP_NAV_ITEMS,
			[$this, 'onRegisterCpNavItems']
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
		$event->rules['tags'] = 'tag-manager/cp/index';
		$event->rules['tags/<groupHandle:{handle}>'] = 'tag-manager/cp/index';

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

	public function onRegisterCpNavItems (RegisterCpNavItemsEvent $event)
	{
		$navItems = $event->navItems;
		$navItemKeys = array_keys($navItems);
		$i = count($navItems);

		$tagsNavItemIndex = null;

		while (--$i)
		{
			$item = $navItems[$navItemKeys[$i]];
			$url = array_key_exists('url', $item) ? $item['url'] : null;

			if ($url === 'tags')
			{
				$tagsNavItemIndex = $i;
				continue;
			}

			if (in_array($url, ['dashboard', 'entries', 'globals', 'categories']))
			{
				$tagsItem = array_splice($navItems, $tagsNavItemIndex, 1);
				array_splice($navItems, $i + 1, 0, $tagsItem);
				break;
			}
		}

		$event->navItems = $navItems;
	}

}