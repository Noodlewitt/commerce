<?php

namespace Craft;

class Commerce_RecentOrdersWidget extends BaseWidget
{
    // Public Methods
    // =========================================================================

    /**
     * @inheritDoc IComponentType::getName()
     *
     * @return string
     */
    public function getName()
    {
        return Craft::t('Commerce Recent Orders');
    }

    /**
     * @inheritDoc IWidget::getBodyHtml()
     *
     * @return string|false
     */
    public function getBodyHtml()
    {
        craft()->templates->includeCssResource('commerce/widgets.css');
        craft()->templates->includeJsResource('commerce/js/CommerceRecentOrdersWidget.js');

        $orders = $this->_getOrders();

        return craft()->templates->render('commerce/_components/widgets/RecentOrders/body', array(
            'orders' => $orders
        ));
    }

    /**
     * @inheritDoc ISavableComponentType::getSettingsHtml()
     *
     * @return string
     */
    public function getSettingsHtml()
    {
        return craft()->templates->render('commerce/_components/widgets/RecentOrders/settings');
    }

    // Protected Methods
    // =========================================================================

    /**
     * Returns the recent entries, based on the widget settings and user permissions.
     *
     * @return array
     */
    private function _getOrders()
    {
        $criteria = craft()->elements->getCriteria('Commerce_Order');
        $criteria->limit = $this->getSettings()->limit;
        $criteria->order = 'elements.dateCreated desc';

        return $criteria->find();
    }

    /**
     * @inheritDoc BaseSavableComponentType::defineSettings()
     *
     * @return array
     */
    protected function defineSettings()
    {
        return array(
            'limit'   => array(AttributeType::Number, 'default' => 10),
        );
    }
}
