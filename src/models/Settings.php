<?php
/**
 * Tags for Craft CMS
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) 2019 Ether Creative
 */

namespace ether\tagManager\models;

use craft\base\Model;

/**
 * Class Settings
 *
 * @author  Ether Creative
 * @package ether\tagManager\models
 */
class Settings extends Model
{

	/**
	 * @var bool Will enable the usage column (may be slow on larger sites)
	 */
	public $enableUsage = false;

}