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
namespace Bss\MultiWishlist\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class UpdateItemOptions implements ObserverInterface
{
    /**
     * @param \Magento\Wishlist\Model\ItemFactory $itemtFactory
     */
    protected $itemtFactory;

    /**
     * @var \Bss\MultiWishlist\Helper\Data
     */
    protected $helper;

    /**
     * UpdateItemOptions constructor.
     * @param \Magento\Wishlist\Model\ItemFactory $itemtFactory
     * @param \Bss\MultiWishlist\Helper\Data $helper
     */
    public function __construct(
        \Magento\Wishlist\Model\ItemFactory $itemtFactory,
        \Bss\MultiWishlist\Helper\Data $helper
    ) {
        $this->itemtFactory = $itemtFactory;
        $this->helper = $helper;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        if ($this->helper->isEnable()) {
            $params = $observer->getRequest()->getParams();
            $wishlistId = $this->itemtFactory->create()->load($params['id'])->getMultiWishlistId();
            $params['wishlist_id'] = $wishlistId;
            $observer->getRequest()->setPostValue($params);
        }
    }
}
