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
namespace Bss\MultiWishlist\Controller\Index;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\Action;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Controller\ResultFactory;
use Bss\MultiWishlist\Helper\Data as Helper;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Wishlist\Model\WishlistFactory;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AssignWishlist extends \Magento\Wishlist\Controller\Index\Add
{
    /**
     * Config key 'Display Wishlist Summary'
     */
    const XML_PATH_WISHLIST_LINK_USE_QTY = 'wishlist/wishlist_link/use_qty';

    /** @var  \Magento\Framework\View\Result\Page */

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var WishlistFactory
     */
    protected $coreWishlist;

    /**
     * @param \Magento\Framework\DataObjectFactory $dataObjectFactory
     */
    protected $dataObjectFactory;

    /**
     * AssignWishlist constructor.
     * @param Action\Context $context
     * @param Helper $helper
     * @param JsonFactory $resultJsonFactory
     * @param WishlistFactory $coreWishlist
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Wishlist\Controller\WishlistProviderInterface $wishlistProvider
     * @param ProductRepositoryInterface $productRepository
     * @param Validator $formKeyValidator
     * @param \Magento\Framework\DataObjectFactory $dataObjectFactory
     */
    public function __construct(
        Action\Context $context,
        Helper $helper,
        JsonFactory $resultJsonFactory,
        WishlistFactory $coreWishlist,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Wishlist\Controller\WishlistProviderInterface $wishlistProvider,
        ProductRepositoryInterface $productRepository,
        Validator $formKeyValidator,
        \Magento\Framework\DataObjectFactory $dataObjectFactory
    ) {
        $this->helper = $helper;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->coreWishlist = $coreWishlist;
        $this->dataObjectFactory = $dataObjectFactory;
        parent::__construct($context, $customerSession, $wishlistProvider, $productRepository, $formKeyValidator);
    }

    /**
     * Assign item to wishlist group.
     *
     * @return $this|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        $params = $this->getRequest()->getParams();
        if ($this->helper->isEnable()) {
            $var = $wishlist_ids = [];
            $wishlist_ids = isset($params['wishlist_id']) ? $params['wishlist_id'] : [0];
            $productId = isset($params['product']) ? (int)$params['product'] : null;
            $customerData = $this->_customerSession->getCustomer();

            if (!$productId || empty($wishlist_ids)) {
                $var["result"] = "error";
                 $var["message"] = '<div class="message-error error message"><div data-bind=\'html: message.text\'>' .
                     __('Please try again.') . '</div></div>';
                return $this->resultJsonFactory->create()->setData($var);
            }
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

            $session = $this->_customerSession;

            $wishlistName = [];
            $wishlist = $this->coreWishlist->create()->loadByCustomerId($customerData->getId(), true);
            try {
                foreach ($wishlist_ids as $wishlistId) {
                    try {
                        $product = $this->productRepository->getById($productId);
                    } catch (NoSuchEntityException $e) {
                        $product = null;
                    }
                    if (!$product || !$product->isVisibleInCatalog()) {
                        $this->messageManager->addErrorMessage(__('We can\'t specify a product.'));
                        $resultRedirect->setPath('*/');
                        return $resultRedirect;
                    }
                    $params['wishlist_id'] = $wishlistId;
                    $this->getRequest()->setPostValue($params);
                    $buyRequest = $this->dataObjectFactory->create()->addData($params);
                    $result = $wishlist->addNewItem($product, $buyRequest, false);
                    $this->saveWishlist($result, $wishlist);
                    $wishlistName[] = $this->helper->getWishlistName($wishlistId);
                    $this->_eventManager->dispatch(
                        'wishlist_add_product',
                        ['wishlist' => $wishlist, 'product' => $product, 'item' => $result]
                    );
                }
                $this->messageManager->addSuccessMessage(__(
                    "%1 has been added to wish list %2.",
                    $product->getName(),
                    implode(',', $wishlistName)
                ));
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addErrorMessage(
                __('We can\'t add the item to Wish List right now: %1.', $e->getMessage())
            );
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage(
                    $e,
                    __('We can\'t add the item to Wish List right now.')
                );
            }
            if ($session->getBeforeWishlistRequest()) {
                $session->unsBeforeWishlistRequest();
                $referer = $session->getBeforeWishlistUrl();
                $referer = $this->getRefererUrl($referer, $session);
                $resultRedirect->setPath('*', ['wishlist_id' => $wishlist->getId()]);
                return $resultRedirect;
            }
            $var = $this->getUrlWishlist($var);
            return $this->setData($var, $resultRedirect, $wishlist);
        } else {
            return parent::execute();
        }
    }

    /**
     * @param $result
     * @param $wishlist
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function saveWishlist($result, $wishlist)
    {
        if (is_string($result)) {
            throw new \Magento\Framework\Exception\LocalizedException(__($result));
        }
        $wishlist->save();
    }

    /**
     * @param $var
     * @param $resultRedirect
     * @param $wishlist
     * @return \Magento\Framework\Controller\Result\Json
     */
    protected function setData($var, $resultRedirect, $wishlist)
    {
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            return $this->resultJsonFactory->create()->setData($var);
        } else {
            $resultRedirect->setPath('*', ['wishlist_id' => $wishlist->getId()]);
            return $resultRedirect;
        }
    }

    /**
     * @param $var
     * @return mixed
     */
    protected function getUrlWishlist($var)
    {
        if ($this->helper->isRedirect()) {
            $var["url"] = $this->_url->getUrl("wishlist");
        }
        return $var;
    }

    /**
     * @param $referer
     * @param $session
     * @return string
     */
    protected function getRefererUrl($referer, $session)
    {
        if ($referer) {
            $session->setBeforeWishlistUrl(null);
        } else {
            $referer = $this->_redirect->getRefererUrl();
        }
        return $referer;
    }
}
