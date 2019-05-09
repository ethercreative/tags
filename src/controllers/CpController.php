<?php
/**
 * Tags for Craft 3
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) 2018 Ether Creative
 */

namespace ether\tagManager\controllers;

use Craft;
use craft\base\Element;
use craft\elements\Tag;
use craft\errors\ElementNotFoundException;
use craft\errors\InvalidElementException;
use craft\errors\MissingComponentException;
use craft\helpers\DateTimeHelper;
use craft\helpers\Json;
use craft\helpers\UrlHelper;
use craft\web\Controller;
use craft\web\View;
use ether\tagManager\web\assets\TagEditAsset;
use ether\tagManager\web\assets\TagIndexAsset;
use Throwable;
use yii\base\Exception;
use yii\base\InvalidConfigException;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\web\ServerErrorHttpException;

/**
 * Class Controller
 *
 * @author  Ether Creative
 * @package ether\tagManager\controllers
 */
class CpController extends Controller
{

	/**
	 * @param string|null $groupHandle
	 *
	 * @return Response
	 * @throws InvalidConfigException
	 */
	public function actionIndex (string $groupHandle = null)
	{
		$groups = Craft::$app->tags->getAllTagGroups();

		$this->view->registerAssetBundle(TagIndexAsset::class);

		return $this->renderTemplate(
			'tag-manager/_index',
			compact('groupHandle', 'groups')
		);
	}

	/**
	 * @param string      $groupHandle
	 * @param int|null    $tagId
	 * @param string|null $siteHandle
	 *
	 * @return Response
	 * @throws NotFoundHttpException
	 */
	public function actionEdit (
		string $groupHandle,
		int $tagId = null,
		string $siteHandle = null
	) {
		$variables = [
			'tagId'        => $tagId,
			'fullPageForm' => true,
		];

		// Get Site
		if ($siteHandle !== null)
		{
			$variables['site'] =
				Craft::$app->getSites()->getSiteByHandle($siteHandle);

			if (!$variables['site'])
				throw new NotFoundHttpException(
					'Invalid site handle: ' . $siteHandle
				);
		}
		else
		{
			$variables['site'] = Craft::$app->sites->primarySite;
		}

		// Get Group
		$variables['group'] =
			Craft::$app->tags->getTagGroupByHandle($groupHandle);

		if (empty($variables['group']))
			throw new NotFoundHttpException('Tag Group not found');

		// Breadcrumbs
		$variables['crumbs'] = [
			[
				'label' => Craft::t('app', 'Tags'),
				'url'   => UrlHelper::url('tags')
			]
		];

		// Tag
		if ($tagId) {
			$variables['tag'] = Craft::$app->tags->getTagById(
				$tagId,
				$variables['site']->id
			);
			if (!$variables['tag'])
				throw new NotFoundHttpException('Tag not found');

			$variables['title'] = $variables['tag']->title;
			$variables['group'] = $variables['tag']->group;
		} else {
			$variables['tag'] = new Tag();
			$variables['tag']->siteId  = $variables['site']->id;
			$variables['tag']->groupId = $variables['group']->id;

			$variables['title'] = Craft::t('app', 'Create a new tag');
		}

		$variables['crumbs'][] = [
			'label' => $variables['group']->name,
			'url'   => UrlHelper::cpUrl('tags/' . $variables['group']->handle),
		];

		// Urls
		$variables['nextTagUrl'] = UrlHelper::url(
			'tags/' . $variables['group']->handle . '/new'
		);
		$variables['continueEditingUrl'] = 'tags/{group.handle}/{id}';

		if (Craft::$app->isMultiSite)
		{
			$variables['continueEditingUrl'] .= '/{site.handle}';
			$variables['nextTagUrl'] .= '/' . $variables['site']->handle;
		}

		$variables['saveShortcutRedirect'] = $variables['continueEditingUrl'];

		// JS
		$view = Craft::$app->getView();
		$view->registerAssetBundle(TagEditAsset::class);
		$tagIdJs = Json::encode($tagId);
		$settingsJs = Json::encode([
			'deleteModalRedirect' => Craft::$app->getSecurity()->hashData('tags'),
		]);
		$view->registerJs(
			'new Craft.TagEdit(' . $tagIdJs . ',' . $settingsJs . ');',
			View::POS_END
		);

		return $this->renderTemplate('tag-manager/_edit', $variables);
	}

	/**
	 * @return null|Response
	 * @throws NotFoundHttpException
	 * @throws ServerErrorHttpException
	 * @throws Throwable
	 * @throws ElementNotFoundException
	 * @throws MissingComponentException
	 * @throws Exception
	 * @throws BadRequestHttpException
	 */
	public function actionSave ()
	{
		$this->requirePostRequest();
		$request = Craft::$app->request;

		$tagId = $request->getBodyParam('tagId');
		$siteId = $request->getBodyParam('siteId');

		// Get Tag
		if ($tagId)
		{
			$tag = Craft::$app->tags->getTagById($tagId, $siteId);

			if (!$tag)
				throw new NotFoundHttpException('Tag not found');
		}
		else
		{
			$tag = new Tag();
			$tag->groupId = Craft::$app->request->getRequiredBodyParam('groupId');

			if ($siteId)
				$tag->siteId = $siteId;
		}

		// Duplicate?
		if ((bool) $request->getBodyParam('duplicate'))
		{
			try {
				$tag = Craft::$app->elements->duplicateElement($tag);
			} catch (InvalidElementException $e) {
				/** @var Tag $clone */
				$clone = $e->element;

				if ($request->getAcceptsJson())
					return $this->asJson([
						'success' => false,
						'errors'  => $clone->getErrors(),
					]);

				Craft::$app->session->setError(
					Craft::t('app', 'Couldn’t duplicate tag.')
				);

				$tag->addErrors($clone->getErrors());
				Craft::$app->urlManager->setRouteParams([
					'tag' => $tag,
				]);

				return null;
			} catch (Throwable $e) {
				throw new ServerErrorHttpException(
					Craft::t('app', 'An error occurred when duplicating the tag.'),
					0,
					$e
				);
			}
		}

		// Populate
		$tag->title = $request->getBodyParam('title', $tag->title);
		$tag->slug = $request->getBodyParam('slug', $tag->slug);
		$tag->setFieldValuesFromRequest(
			$request->getParam('fieldsLocation', 'fields')
		);

		// Save
		if (!Craft::$app->elements->saveElement($tag))
		{
			if ($request->getAcceptsJson())
				return $this->asJson([
					'errors' => $tag->getErrors(),
				]);

			Craft::$app->getSession()->setError(
				Craft::t('app', 'Couldn’t save tag.')
			);

			// Send the entry back to the template
			Craft::$app->getUrlManager()->setRouteParams([
				'tag' => $tag
			]);

			return null;
		}

		if ($request->getAcceptsJson())
		{
			$return = [];

			$return['success'] = true;
			$return['id']      = $tag->id;
			$return['title']   = $tag->title;

			if ($request->getIsCpRequest())
				$return['cpEditUrl'] = $tag->getCpEditUrl();

			$return['dateCreated'] =
				DateTimeHelper::toIso8601($tag->dateCreated);
			$return['dateUpdated'] =
				DateTimeHelper::toIso8601($tag->dateUpdated);

			return $this->asJson($return);
		}

		Craft::$app->getSession()->setNotice(
			Craft::t('app', 'Tag saved.')
		);

		return $this->redirectToPostedUrl($tag);
	}

	/**
	 * @return null|Response
	 * @throws NotFoundHttpException
	 * @throws Throwable
	 * @throws BadRequestHttpException
	 */
	public function actionDelete ()
	{
		$this->requirePostRequest();
		$request = Craft::$app->request;

		$tagId  = $request->getBodyParam('tagId');
		$siteId = $request->getBodyParam('siteId');

		$tag = Craft::$app->tags->getTagById($tagId, $siteId);

		if (!$tag)
			throw new NotFoundHttpException('Tag not found');

		if (!Craft::$app->elements->deleteElement($tag))
		{
			if ($request->getAcceptsJson())
				return $this->asJson(['success' => false]);

			Craft::$app->session->setError(
				Craft::t('app', 'Couldn’t delete tag.')
			);

			// Send the entry back to the template
			Craft::$app->getUrlManager()->setRouteParams([
				'tag' => $tag
			]);

			return null;
		}

		if ($request->getAcceptsJson())
			return $this->asJson(['success' => true]);

		Craft::$app->session->setNotice(
			Craft::t('app', 'Entry tag.')
		);

		return $this->redirectToPostedUrl($tag);
	}

	/**
	 * @return Response
	 * @throws BadRequestHttpException
	 */
	public function actionTagSummary ()
	{
		$this->requirePostRequest();
		$this->requireLogin();

		$tagIds = Craft::$app->request->getRequiredBodyParam('tagId');
		$summary = [];

		$relationCount = Element::find()->relatedTo($tagIds)->count();

		if ($relationCount)
			$summary[] =
				$relationCount === 1
					? Craft::t('app', '1 use')
					: Craft::t('app', '{c} uses', ['c' => $relationCount]);

		return $this->asJson($summary);
	}

}