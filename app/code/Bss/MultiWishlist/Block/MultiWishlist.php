<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_MultiWishlist
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\MultiWishlist\Block;

use Bss\MultiWishlist\Helper\Data as Helper;
use Magento\Framework\View\Element\Template;
use Bss\MultiWishlist\Model\WishlistLabel as Model;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Data\Form\FormKey;

class MultiWishlist extends Template
{
    public function getTitle()
    {
        return __('My feed List');
    }

    /**
     * @var Helper
     */
    protected $helper;

    /**getWishlistItemsCollection
     * @var Model
     */
    protected $model;

    /**
     * @var FormKey
     */
    protected $formKey;

    /**
     * MultiWishlist constructor.
     * @param Context $context
     * @param Helper $helper
     * @param Model $model
     * @param FormKey $formKey
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        Helper $helper,
        Model $model,
        FormKey $formKey,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->helper = $helper;
        $this->formKey = $formKey;
        $this->model = $model;
    }

    /**
     * @return Helper
     */
    public function getHelper()
    {
        return $this->helper;
    }

    /**
     * @return \Bss\MultiWishlist\Model\ResourceModel\WishlistLabel\Collection
     */
    public function getMyWishlist()
    {
        return $this->helper->getWishlistLabels();
    }

    /**
     * @return bool
     */
    public function isCustomerLoggedIn()
    {
        return $this->helper->isCustomerLoggedIn();
    }

    /**
     * @return bool
     */
    public function isRedirect()
    {
        return $this->helper->isRedirect();
    }

    /**
     * @return string
     */
    public function getUrlWishlist()
    {
        return $this->getUrl("wishlist");
    }

    /**
     * @return string
     */
    public function getUrlPopup()
    {
        return $this->getUrl("multiwishlist/index/popup");
    }

    /**
     * @return string
     */
    public function getFormKey()
    {
        return $this->formKey->getFormKey();
    }


}
