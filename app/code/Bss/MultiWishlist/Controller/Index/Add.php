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

use Magento\Framework\App\Action\Action;
use Bss\MultiWishlist\Helper\Data as Helper;
use Bss\MultiWishlist\Model\WishlistLabel;
use Magento\Wishlist\Model\Item as WishlistItem;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Controller\Result\JsonFactory;

class Add extends Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Framework\App\Action\Context
     */
    protected $context;

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @var WishlistLabel
     */
    protected $wishlistLabel;

    /**
     * @var WishlistItem
     */
    protected $wishlistItem;

    /**
     * @var \Magento\Wishlist\Controller\WishlistProviderInterface
     */
    protected $wishlistProvider;

    /**
     * @var Validator
     */
    protected $formKeyValidator;

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Magento\Framework\View\LayoutFactory
     */
    protected $layoutFactory;

    /**
     * Edit constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Wishlist\Controller\WishlistProviderInterface $wishlistProvider
     * @param Helper $helper
     * @param WishlistLabel $wishlistLabel
     * @param WishlistItem $wishlistItem
     * @param Validator $formKeyValidator
     * @param JsonFactory $resultJsonFactory
     * @param \Magento\Framework\View\LayoutFactory $layoutFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Wishlist\Controller\WishlistProviderInterface $wishlistProvider,
        Helper $helper,
        WishlistLabel $wishlistLabel,
        WishlistItem $wishlistItem,
        Validator $formKeyValidator,
        JsonFactory $resultJsonFactory,
        \Magento\Framework\View\LayoutFactory $layoutFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->context = $context;
        $this->wishlistProvider = $wishlistProvider;
        $this->helper = $helper;
        $this->wishlistLabel = $wishlistLabel;
        $this->wishlistItem = $wishlistItem;
        $this->formKeyValidator = $formKeyValidator;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->layoutFactory = $layoutFactory;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Json|\Magento\Framework\Controller\ResultInterface
     * @throws \Exception
     */
    public function execute()
    {
        $mWishlistId = $this->getRequest()->getParam('mWishlistId');
        $productId = $this->getRequest()->getParam('new_product');

//        $data = $this->getRequest()->getPost();
//        $var = $data['new_product'];

        //$test  = document.getElementById('new_product').value;

        $model = $this->wishlistLabel;
        try {
            $model->load($mWishlistId);
            $model->setproductId($productId);
            //$model->save();
            $var["wishlistId"] = $mWishlistId;
            $var["result"] = "success";
            $var["productId"] = $productId;
            //$var["test"] = $test;
            $this->messageManager->addSuccessMessage(__('The wishlist has renamed successfully.'));
            $this->messageManager->addSuccessMessage(__($var["wishlistId"]));
            $this->messageManager->addSuccessMessage(__($var["productId"]));
            //$this->messageManager->addSuccessMessage(__($var["test"]));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('Please try again.'));
        }
        return $this->_redirect('wishlist', ['wishlist_id' => $mWishlistId]);
    }

}

