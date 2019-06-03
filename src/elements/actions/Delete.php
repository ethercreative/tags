<?php
/**
 * Tags for Craft 3
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) 2018 Ether Creative
 */

namespace ether\tagManager\elements\actions;

use Craft;
use craft\base\ElementAction;
use craft\elements\db\ElementQueryInterface;
use craft\helpers\Json;
use ether\tagManager\elements\Tag;
use ether\tagManager\web\assets\DeleteTagAsset;
use Throwable;
use yii\base\InvalidConfigException;
use yii\db\Exception;

/**
 * Class Delete
 *
 * @author  Ether Creative
 * @package ether\tagManager\elements\actions
 */
class Delete extends ElementAction
{

	// Properties
	// =========================================================================

	/**
	 * @var int|null The tag ID that the deleted tag will be replaced with
	 */
	public $replaceWith;

	// Public
	// =========================================================================

	public static function isDestructive (): bool
	{
		return true;
	}

	public function getTriggerLabel (): string
	{
		return Craft::t('app', 'Delete…');
	}

	/**
	 * @return null|string|void
	 * @throws \yii\base\Exception
	 * @throws InvalidConfigException
	 */
	public function getTriggerHtml ()
	{
		$type = Json::encode(static::class);
		$redirect = Json::encode(Craft::$app->security->hashData('tags'));

		$js = <<<JS
/* global Craft */
!function () {
	new Craft.ElementActionTrigger({
		type: $type,
		batch: true,
		validateSelection: function () {
			return true;
		},
		activate: function (selectedItems) {
			Craft.elementIndex.setIndexBusy();
			const ids = Craft.elementIndex.getSelectedElementIds();
			Craft.postActionRequest(
				'tag-manager/cp/tag-summary',
				{ tagId: ids },
				function(response, textStatus) {
					Craft.elementIndex.setIndexAvailable();
					
					if (textStatus !== 'success')
						return;
					
					const modal = new Craft.DeleteTagModal(ids, {
						contentSummary: response,
						onSubmit: function() {
							Craft.elementIndex.submitAction(
								$type,
								Garnish.getPostData(modal.\$container)
							)
							modal.hide();
							
							return false;
						},
						redirect: $redirect,
					})
				}
			);
		}
	})
}();
JS;

		Craft::$app->view->registerAssetBundle(DeleteTagAsset::class);
		Craft::$app->view->registerJs($js);
	}

	/**
	 * @param ElementQueryInterface $query
	 *
	 * @return bool
	 * @throws Exception
	 * @throws Throwable
	 */
	public function performAction (ElementQueryInterface $query): bool
	{
		/** @var Tag $tags */
		$tags = $query->all();

		if (is_array($this->replaceWith) && isset($this->replaceWith[0]))
			$this->replaceWith = $this->replaceWith[0];

		if (!empty($this->replaceWith))
		{
			$replaceWith = Craft::$app->getElements()->getElementById(
				$this->replaceWith,
				Tag::class,
				null
			);

			if (!$replaceWith)
				throw new Exception(
					'No tag exists with the ID: ' . $this->replaceWith
				);
		}
		else
		{
			$replaceWith = null;
		}

		foreach ($tags as $tag)
		{
			$tag->replaceWith = $replaceWith;
			Craft::$app->elements->deleteElement($tag);
		}

		$this->setMessage(
			Craft::t('app', 'Tags deleted.')
		);

		return true;
	}

}