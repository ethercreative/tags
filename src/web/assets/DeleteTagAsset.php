<?php
/**
 * Tags for Craft 3
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) 2018 Ether Creative
 */

namespace ether\tagManager\web\assets;

use craft\web\assets\cp\CpAsset;
use yii\web\AssetBundle;

/**
 * Class DeleteTagAsset
 *
 * @author  Ether Creative
 * @package ether\tagManager\web\assets
 */
class DeleteTagAsset extends AssetBundle
{

	public function init ()
	{
		$this->sourcePath = __DIR__;

		$this->depends = [
			CpAsset::class,
		];

		$this->js = [
			'DeleteTagModal.js',
		];

		parent::init();
	}

}