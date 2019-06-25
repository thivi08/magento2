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

class Wishlist implements ObserverInterface
{
    /**
     * @param \Magento\Framework\App\Request\Http $request
     */
    protected $request;

    /**
     * @var \Bss\MultiWishlist\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * Wish ist constructor.
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Bss\MultiWishlist\Helper\Data $helper
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     */
    public function __construct(
        \Magento\Framework\App\Request\Http $request,
        \Bss\MultiWishlist\Helper\Data $helper,
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        $this->request = $request;
        $this->helper = $helper;
        $this->messageManager = $messageManager;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        if ($this->helper->isEnable()) {
            $params = $this->request->getParams();
            $items = $observer->getItems();
            $wishlist_id = isset($params['wishlist_id']) ? $params['wishlist_id'] : 0;
            if (is_array($wishlist_id)) {
                $wishlistId = isset($wishlist_id[0]) ? $wishlist_id[0] : 0;
            } else {
                $wishlistId = $wishlist_id;
            }
            foreach ($items as $item) {
                $this->setMultiWishlistId($item, $wishlistId);
            }
        }
    }

    /**
     * @param $item
     * @param $wishlistId
     */
    protected function setMultiWishlistId($item, $wishlistId)
    {
        try {
            $item->setMultiWishlistId($wishlistId);
            $item->save();
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }
    }
}
