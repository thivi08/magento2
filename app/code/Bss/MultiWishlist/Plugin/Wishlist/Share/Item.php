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
namespace Bss\MultiWishlist\Plugin\Wishlist\Share;

class Item
{
    /**
     * @var \Bss\MultiWishlist\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Wishlist\Controller\Shared\WishlistProvider
     */
    protected $wishlistProvider;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * Item constructor.
     * @param \Bss\MultiWishlist\Helper\Data $helper
     * @param \Magento\Wishlist\Controller\Shared\WishlistProvider $wishlistProvider
     * @param \Magento\Framework\App\RequestInterface $request
     */
    public function __construct(
        \Bss\MultiWishlist\Helper\Data $helper,
        \Magento\Wishlist\Controller\Shared\WishlistProvider $wishlistProvider,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->helper = $helper;
        $this->wishlistProvider = $wishlistProvider;
        $this->request = $request;
    }

    /**
     * @return int
     */
    protected function getItemCollection()
    {
        $customerId = $this->wishlistProvider->getWishlist()->getCustomerId();
        $multiWishlistId = $this->request->getParam('mwishlist_id');
        $itemCollection = $this->helper->getWishlistItemCollectionShared($multiWishlistId, $customerId);
        return $itemCollection;
    }

    /**
     * @param \Magento\Wishlist\Block\Share\Wishlist $subject
     * @param $result
     * @return int|void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterHasWishlistItems(\Magento\Wishlist\Block\Share\Wishlist $subject, $result)
    {
        return count($this->getItemCollection());
    }

    /**
     * @param \Magento\Wishlist\Block\Share\Wishlist $subject
     * @param $result
     * @return int
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetWishlistItems(\Magento\Wishlist\Block\Share\Wishlist $subject, $result)
    {
        return $this->getItemCollection();
    }

    /**
     * Retrieve Page Header
     *
     * @return \Magento\Framework\Phrase
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetHeader(\Magento\Wishlist\Block\Share\Wishlist $subject, $result)
    {
        $multiWishlistId = $this->request->getParam('mwishlist_id');
        $wishlistName = $this->helper->getWishlistName($multiWishlistId);
        $result = $result . " (" . __($wishlistName) . ")";
        return $result;
    }
}
