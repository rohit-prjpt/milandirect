<?php
/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE-ENTERPRISE.txt
 * 
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This package designed for Magento ENTERPRISE edition
 * aheadWorks does not guarantee correct work of this extension
 * on any other Magento edition except Magento ENTERPRISE edition.
 * aheadWorks does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Blog
 * @copyright  Copyright (c) 2010-2011 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE-ENTERPRISE.txt
 */
class AW_Blog_CatController extends Mage_Core_Controller_Front_Action {
	public function preDispatch() {
		parent::preDispatch();
        if(!Mage::helper('blog')->getEnabled())
            $this->_redirectUrl(Mage::helper('core/url')->getHomeUrl());
	}

	public function viewAction() {

		$identifier = $this->getRequest()->getParam('identifier', $this->getRequest()->getParam('id', false));

		if (!Mage::helper('blog/cat')->renderPage($this, $identifier)) {
			$this->_forward('NoRoute');
		}

	}

	public function noRouteAction($coreRoute = null) {
		$this->getResponse()->setHeader('HTTP/1.1','404 Not Found');
		$this->getResponse()->setHeader('Status','404 File not found');

		$pageId = Mage::getStoreConfig('web/default/cms_no_route');
		if (!Mage::helper('cms/page')->renderPage($this, $pageId)) {
			$this->_forward('defaultNoRoute');
		}
	}
}