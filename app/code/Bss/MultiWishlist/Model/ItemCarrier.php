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
namespace Bss\MultiWishlist\Model;

use Magento\Catalog\Model\Product\Exception as ProductException;
use Magento\Checkout\Helper\Cart as CartHelper;
use Magento\Checkout\Model\Cart;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface as Logger;
use Magento\Framework\Message\ManagerInterface as MessageManager;
use Magento\Framework\UrlInterface;
use Magento\Wishlist\Helper\Data as WishlistHelper;
use Magento\Wishlist\Model\Wishlist;
/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ItemCarrier extends \Magento\Wishlist\Model\ItemCarrier
{

    /**
     * @param Wishlist $wishlist
     * @param $qtys
     * @param int $mwlId
     * @return string
     * @throws LocalizedException
     */
    public function moveAllToCartExtend(Wishlist $wishlist, $qtys, $mwlId = 0)
    {
        $isOwner = $wishlist->isOwner($this->customerSession->getCustomerId());
        $messages = [];
        $addedProducts = [];
        $notSalable = [];

        $cart = $this->cart;
        $collection = $wishlist->getItemCollection()->setVisibilityFilter()->
            addFieldToFilter('multi_wishlist_id', $mwlId);

        foreach ($collection as $item) {
            /** @var $item \Magento\Wishlist\Model\Item */
            try {
                $disableAddToCart = $item->getProduct()->getDisableAddToCart();
                $item->unsProduct();

                // Set qty
                $item = $this->setQtyItem($qtys, $item);
              
                $item->getProduct()->setDisableAddToCart($disableAddToCart);
                // Add to cart
                $addedProducts = $this->addProduct($item, $cart, $isOwner, $addedProducts);
            } catch (LocalizedException $e) {
                if ($e instanceof ProductException) {
                    $notSalable[] = $item;
                } else {
                    $messages[] = __('%1 for "%2".', trim($e->getMessage(), '.'), $item->getProduct()->getName());
                }

                $cartItem = $cart->getQuote()->getItemByProduct($item->getProduct());
                $this->deleteCartItem($cartItem);
            } catch (\Exception $e) {
                $this->logger->critical($e);
                $messages[] = __('We can\'t add this item to your shopping cart right now.');
            }
        }

        $indexUrl = $this->getIndexUrl($isOwner, $wishlist, $mwlId);
        $redirectUrl = $this->getRedirectUrl($indexUrl);
        $redirectUrl = $this->addMessageError($notSalable, $messages, $redirectUrl, $indexUrl);

        if ($addedProducts) {
            // save wishlist model for setting date of last update
            try {
                $wishlist->save();
            } catch (\Exception $e) {
                $this->messageManager->addError(__('We can\'t update the Wish List right now.'));
                $redirectUrl = $indexUrl;
            }

            $products = [];
            foreach ($addedProducts as $product) {
                /** @var $product \Magento\Catalog\Model\Product */
                $products[] = '"' . $product->getName() . '"';
            }

            $this->messageManager->addSuccess(
                __('%1 product(s) have been added to shopping cart: %2.', count($addedProducts), join(', ', $products))
            );

            // save cart and collect totals
            $cart->save()->getQuote()->collectTotals();
        }
        $this->helper->calculate();
        return $redirectUrl;
    }

    protected function getIndexUrl($isOwner, $wishlist, $mwlId)
    {
        if ($isOwner) {
            return $this->helper->getListUrl($wishlist->getId());
        } else {
            $param = ['code' => $wishlist->getSharingCode()];
            if ($mwlId) {
                $param['mwishlist_id'] = $mwlId;
            }
            return $this->urlBuilder->getUrl('wishlist/shared', $param);
        }
    }

    protected function getRedirectUrl($indexUrl)
    {
        if ($this->cartHelper->getShouldRedirectToCart()) {
            return $this->cartHelper->getCartUrl();
        } elseif ($this->redirector->getRefererUrl()) {
            return $this->redirector->getRefererUrl();
        } else {
            return $indexUrl;
        }
    }

    protected function addMessageError($notSalable, $messages, $redirectUrl, $indexUrl)
    {
        if ($notSalable) {
            $products = [];
            foreach ($notSalable as $item) {
                $products[] = '"' . $item->getProduct()->getName() . '"';
            }
            $messages[] = __(
                'We couldn\'t add the following product(s) to the shopping cart: %1.',
                join(', ', $products)
            );
        }
        if ($messages) {
            foreach ($messages as $message) {
                $this->messageManager->addError($message);
            }
            $redirectUrl = $indexUrl;
        }
        return $redirectUrl;
    }

    protected function deleteCartItem($cartItem)
    {
        if ($cartItem) {
            $this->cart->getQuote()->deleteItem($cartItem);
        }
    }

    protected function addProduct($item, $cart, $isOwner, $addedProducts)
    {
        if ($item->addToCart($cart, $isOwner)) {
            $addedProducts[] = $item->getProduct();
        }
        return $addedProducts;
    }

    protected function setQtyItem($qtys, $item)
    {
        if (isset($qtys[$item->getId()])) {
            $qty = $this->quantityProcessor->process($qtys[$item->getId()]);
            if ($qty) {
                $item->setQty($qty);
            }
        }
        return $item;
    }
}
