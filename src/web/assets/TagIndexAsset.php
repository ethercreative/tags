<?php
/**
 * Tags for Craft 3
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) 2018 Ether Creative
 */

namespace ether\tagManager\web\assets;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

/**
 * Class TagIndexAsset
 *
 * @author  Ether Creative
 * @package ether\tagManager\web\assets\index
 */
class TagIndexAsset extends AssetBundle
{

	public function init ()
	{
		$this->sourcePath = __DIR__;

		$this->depends = [
			CpAsset::class,
		];

		$this->js = [
			'TagIndex.js',
		];

		parent::init();
	}

}