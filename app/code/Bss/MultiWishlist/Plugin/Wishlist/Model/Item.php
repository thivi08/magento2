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
namespace Bss\MultiWishlist\Plugin\Wishlist\Model;

class Item
{
    /**
     * @var \Bss\MultiWishlist\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * Item constructor.
     * @param \Bss\MultiWishlist\Helper\Data $helper
     * @param \Magento\Framework\App\Request\Http $request
     */
    public function __construct(
        \Bss\MultiWishlist\Helper\Data $helper,
        \Magento\Framework\App\Request\Http $request
    ) {
        $this->helper = $helper;
        $this->request = $request;
    }

    /**
     * @param \Magento\Wishlist\Model\Item $item
     * @param $cart
     * @param bool $delete
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeAddToCart(\Magento\Wishlist\Model\Item $item, $cart, $delete = false)
    {
        if ($this->helper->isEnable()) {
            $delete = $this->helper->removeItemAfterAddCart();
            return [$cart, $delete];
        }
    }

    /**
     * Around represent product
     *
     * @param \Magento\Wishlist\Model\Item $item
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterRepresentProduct(\Magento\Wishlist\Model\Item $subject, $result)
    {
        if ($this->helper->isEnable()) {
            $params = $this->request->getParams();

            $itemProduct = $subject->getProduct();
            $itemProductPrduct = $itemProduct->getId();
            $wishlist_id = isset($params['wishlist_id']) ? $params['wishlist_id'] : 0;
            if (is_array($wishlist_id)) {
                $wishlistId = isset($wishlist_id[0]) ? $wishlist_id[0] : 0;
            } else {
                $wishlistId = $wishlist_id;
            }

            if ($result && $subject->getMultiWishlistId() != $wishlistId) {
                $result = false;
            }
        }

        return $result;
    }
}
