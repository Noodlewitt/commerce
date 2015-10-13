<?php
namespace Craft;

/**
 * Class Commerce_DiscountsController
 *
 * @author    Pixel & Tonic, Inc. <support@pixelandtonic.com>
 * @copyright Copyright (c) 2015, Pixel & Tonic, Inc.
 * @license   http://craftcommerce.com/license Craft Commerce License Agreement
 * @see       http://craftcommerce.com
 * @package   craft.plugins.commerce.controllers
 * @since     1.0
 */
class Commerce_DiscountsController extends Commerce_BaseAdminController
{
    /**
     * @throws HttpException
     */
    public function actionIndex()
    {
        $discounts = craft()->commerce_discounts->getAll(['order' => 'name']);
        $this->renderTemplate('commerce/promotions/discounts/index',
            compact('discounts'));
    }

    /**
     * Create/Edit Discount
     *
     * @param array $variables
     *
     * @throws HttpException
     */
    public function actionEdit(array $variables = [])
    {
        if (empty($variables['discount'])) {
            if (!empty($variables['id'])) {
                $id = $variables['id'];
                $variables['discount'] = craft()->commerce_discounts->getById($id);

                if (!$variables['discount']->id) {
                    throw new HttpException(404);
                }
            } else {
                $variables['discount'] = new Commerce_DiscountModel();
            }
        }

        if (!empty($variables['id'])) {
            $variables['title'] = $variables['discount']->name;
        } else {
            $variables['title'] = Craft::t('Create a Discount');
        }

        //getting user groups map
        $groups = craft()->userGroups->getAllGroups();
        $variables['groups'] = \CHtml::listData($groups, 'id', 'name');

        //getting product types maps
        $types = craft()->commerce_productTypes->getAll();
        $variables['types'] = \CHtml::listData($types, 'id', 'name');

        $variables['products'] = null;
        $products = $productIds = [];
        if (empty($variables['id'])) {
            $productIds = explode('|', craft()->request->getParam('productIds'));
        } else {
            $productIds = $variables['discount']->getProductsIds();
        }
        foreach ($productIds as $productId) {
            $products[] = craft()->commerce_products->getById($productId);
        }
        $variables['products'] = $products;

        $this->renderTemplate('commerce/promotions/discounts/_edit', $variables);
    }

    /**
     * @throws HttpException
     */
    public function actionSave()
    {
        $this->requirePostRequest();

        $discount = new Commerce_DiscountModel();

        // Shared attributes
        $fields = [
            'id',
            'name',
            'description',
            'dateFrom',
            'dateTo',
            'enabled',
            'purchaseTotal',
            'purchaseQty',
            'baseDiscount',
            'perItemDiscount',
            'percentDiscount',
            'freeShipping',
            'excludeOnSale',
            'code',
            'perUserLimit',
            'totalUseLimit'
        ];
        foreach ($fields as $field) {
            $discount->$field = craft()->request->getPost($field);
        }

        $products = craft()->request->getPost('products', []);
        if (!$products) {
            $products = [];
        }
        $productTypes = craft()->request->getPost('productTypes', []);
        $groups = craft()->request->getPost('groups', []);

        // Save it
        if (craft()->commerce_discounts->save($discount, $groups, $productTypes,
            $products)
        ) {
            craft()->userSession->setNotice(Craft::t('Discount saved.'));
            $this->redirectToPostedUrl($discount);
        } else {
            craft()->userSession->setError(Craft::t('Couldn’t save discount.'));
        }

        // Send the model back to the template
        craft()->urlManager->setRouteVariables(['discount' => $discount]);
    }

    /**
     * @throws HttpException
     */
    public function actionDelete()
    {
        $this->requirePostRequest();
        $this->requireAjaxRequest();

        $id = craft()->request->getRequiredPost('id');

        craft()->commerce_discounts->deleteById($id);
        $this->returnJson(['success' => true]);
    }

}