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
namespace Bss\MultiWishlist\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Customer\Model\Session as CustomerSession;
use Bss\MultiWishlist\Model\WishlistLabel;
use Bss\MultiWishlist\Model\ResourceModel\WishlistLabel\CollectionFactory;
use Magento\Wishlist\Model\WishlistFactory;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\App\ProductMetadataInterface;

class Data extends AbstractHelper
{
    const XML_PATH_ENABLED = 'bss_multiwishlist/general/enable';
    const XML_PATH_REMOVE_ITEM_ADDCART = 'bss_multiwishlist/general/remove_item_addcart';
    const XML_PATH_REDIRECT = 'bss_multiwishlist/general/redirect';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * @var WishlistLabel
     */
    protected $wishlistLabel;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var WishlistFactory
     */
    protected $coreWishlistFactory;

    /**
     * @var HttpContext
     */
    protected $httpContext;

    /**
     * @var ProductMetadataInterface
     */
    protected $productMetaData;

    /**
     * Data constructor.
     * @param Context $context
     * @param CustomerSession $customerSession
     * @param WishlistLabel $wishlistLabel
     * @param CollectionFactory $collectionFactory
     * @param WishlistFactory $coreWishlistFactory
     * @param HttpContext $httpContext
     * @param ProductMetadataInterface $productMetaData
     */
    public function __construct(
        Context $context,
        CustomerSession $customerSession,
        WishlistLabel $wishlistLabel,
        CollectionFactory $collectionFactory,
        WishlistFactory $coreWishlistFactory,
        HttpContext $httpContext,
        ProductMetadataInterface $productMetaData
    ) {
        parent::__construct($context);
        $this->customerSession = $customerSession;
        $this->_scopeConfig = $context->getScopeConfig();
        $this->wishlistLabel = $wishlistLabel;
        $this->collectionFactory = $collectionFactory;
        $this->coreWishlistFactory = $coreWishlistFactory;
        $this->_request = $context->getRequest();
        $this->httpContext = $httpContext;
        $this->productMetaData = $productMetaData;
    }

    /**
     * @return string
     */
    public function isEnable()
    {
        return $this->_scopeConfig->getValue(
            self::XML_PATH_ENABLED,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return string
     */
    public function isRedirect()
    {
        return $this->_scopeConfig->getValue(
            self::XML_PATH_REDIRECT,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return bool
     */
    public function isCustomerLoggedIn()
    {
        $isLoggedIn = $this->httpContext->getValue(\Magento\Customer\Model\Context::CONTEXT_AUTH);
        return $isLoggedIn;
    }

    /**
     * @return \Bss\MultiWishlist\Model\ResourceModel\WishlistLabel\Collection
     */
    public function getWishlistLabels()
    {
        $customer = $this->getCustomer();
        $collection = $this->collectionFactory->create();
        $collection = $collection->addFieldToFilter('customer_id', $customer->getId());
        return $collection;
    }

    /**
     * @return array
     */
    public function getLabelIds()
    {

        $wishlist = $this->getWishlistLabels();
        $multiWishlist = [];
        $multiWishlist[0] = 0;
        foreach ($wishlist as $item) {
            if (!in_array($item->getId(), $multiWishlist)) {
                $multiWishlist[] = $item->getId();
            }
        }
        return $multiWishlist;
    }

    /**
     * @param int $id
     * @return int|\Magento\Wishlist\Model\ResourceModel\Item\Collection
     */
    public function getWishlistItemsCollection($id)
    {
        $customer = $this->getCustomer();
        $page = $this->_request->getParam("p", 1);
        $limit = $this->_request->getParam("limit", 10);
        if ($customer->getId()) {
            $wishlist = $this->getWishlist($customer->getId());
            if ($this->compareVersion()) {
                return $wishlist->getItemCollection()->addFieldToFilter('multi_wishlist_id', $id);
            }
            return $wishlist->getItemCollection()
                    ->addFieldToFilter('multi_wishlist_id', $id)
                    ->setPageSize($limit)
                    ->setCurPage($page);
        }
        return 0;
    }

    /**
     * @param int $multiWishlistId
     * @param int $customerId
     * @return int|\Magento\Wishlist\Model\ResourceModel\Item\Collection
     */
    public function getWishlistItemCollectionShared($multiWishlistId, $customerId)
    {
        $page = $this->_request->getParam("p", 1);
        $limit = $this->_request->getParam("limit", 10);
        if ($customerId) {
            $wishlist = $this->getWishlist($customerId);
            if ($this->compareVersion()) {
                return $wishlist->getItemCollection()->addFieldToFilter('multi_wishlist_id', $multiWishlistId);
            }
            return $wishlist->getItemCollection()
                ->addFieldToFilter('multi_wishlist_id', $multiWishlistId)
                ->setPageSize($limit)
                ->setCurPage($page);
        }
        return 0;
    }

    /**
     * Compare version 2.3.1.
     * Magento 2.3.1 Add Pagination in Wish list account page.
     *
     * @return bool
     */
    public function compareVersion()
    {
        $version = $this->productMetaData->getVersion();
        return version_compare($version, '2.3.1', '<');
    }

    /**
     * @return \Bss\MultiWishlist\Model\ResourceModel\WishlistLabel\Collection
     */
    public function getWishlistCollection()
    {
        return $this->collectionFactory->create();
    }

    public function getWishlistName($id)
    {
        if ($id == 0) {
            return __('Main');
        }
        return $this->wishlistLabel->load($id)->getWishlistName();
    }

    /**
     * @return string
     */
    public function removeItemAfterAddCart()
    {
        return $this->_scopeConfig->getValue(
            self::XML_PATH_REMOVE_ITEM_ADDCART,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @param string $param
     * @return string
     */
    public function getParamUrl($param)
    {
        return $this->_request->getParam($param);
    }

    public function getCustomer()
    {
        return $this->customerSession->getCustomer();
    }


    public function getWishlist($customerId)
    {
        return $this->coreWishlistFactory->create()->loadByCustomerId($customerId, true);
    }
}
