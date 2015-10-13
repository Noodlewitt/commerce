<?php
namespace Craft;

/**
 * Class Commerce_TaxCategoriesController
 *
 * @author    Pixel & Tonic, Inc. <support@pixelandtonic.com>
 * @copyright Copyright (c) 2015, Pixel & Tonic, Inc.
 * @license   http://craftcommerce.com/license Craft Commerce License Agreement
 * @see       http://craftcommerce.com
 * @package   craft.plugins.commerce.controllers
 * @since     1.0
 */
class Commerce_TaxCategoriesController extends Commerce_BaseAdminController
{
    /**
     * @throws HttpException
     */
    public function actionIndex()
    {
        $taxCategories = craft()->commerce_taxCategories->getAll();
        $this->renderTemplate('commerce/settings/taxcategories/index', compact('taxCategories'));
    }

    /**
     * Create/Edit Tax Category
     *
     * @param array $variables
     *
     * @throws HttpException
     */
    public function actionEdit(array $variables = [])
    {
        if (empty($variables['taxCategory'])) {
            if (!empty($variables['id'])) {
                $id = $variables['id'];
                $variables['taxCategory'] = craft()->commerce_taxCategories->getById($id);

                if (!$variables['taxCategory']) {
                    throw new HttpException(404);
                }
            } else {
                $variables['taxCategory'] = new Commerce_TaxCategoryModel();
            };
        }

        if (!empty($variables['id'])) {
            $variables['title'] = $variables['taxCategory']->name;
        } else {
            $variables['title'] = Craft::t('Create a new tax category');
        }

        $this->renderTemplate('commerce/settings/taxcategories/_edit', $variables);
    }

    /**
     * @throws HttpException
     */
    public function actionSave()
    {
        $this->requirePostRequest();

        $taxCategory = new Commerce_TaxCategoryModel();

        // Shared attributes
        $taxCategory->id = craft()->request->getPost('taxCategoryId');
        $taxCategory->name = craft()->request->getPost('name');
        $taxCategory->handle = craft()->request->getPost('handle');
        $taxCategory->description = craft()->request->getPost('description');
        $taxCategory->default = craft()->request->getPost('default');

        // Save it
        if (craft()->commerce_taxCategories->save($taxCategory)) {
            craft()->userSession->setNotice(Craft::t('Tax category saved.'));
            $this->redirectToPostedUrl($taxCategory);
        } else {
            craft()->userSession->setError(Craft::t('Couldn’t save tax category.'));
        }

        // Send the tax category back to the template
        craft()->urlManager->setRouteVariables([
            'taxCategory' => $taxCategory
        ]);
    }

    /**
     * @throws HttpException
     */
    public function actionDelete()
    {
        $this->requirePostRequest();
        $this->requireAjaxRequest();

        $id = craft()->request->getRequiredPost('id');

        craft()->commerce_taxCategories->deleteById($id);
        $this->returnJson(['success' => true]);
    }

}