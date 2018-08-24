<?php
/**
 * Tags for Craft 3
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) 2018 Ether Creative
 */

namespace ether\tagManager\elements\actions;

/**
 * Class Delete
 *
 * @author  Ether Creative
 * @package ether\tagManager\elements\actions
 */
class Delete extends \craft\elements\actions\Delete
{

	public $confirmationMessage = 'Are you sure you want to delete this tag?';

	public $successMessage = 'Tag deleted.';

}