<?php
/**
 * Tags for Craft CMS
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) 2019 Ether Creative
 */

namespace ether\tagManager\web\assets;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

/**
 * Class TagEditAsset
 *
 * @author  Ether Creative
 * @package ether\tagManager\web\assets
 */
class TagEditAsset extends AssetBundle
{

	public function init ()
	{
		$this->sourcePath = __DIR__;

		$this->depends = [
			CpAsset::class,
		];

		$this->js = [
			'TagEdit.js',
			'DeleteTagModal.js',
		];

		parent::init();
	}

}