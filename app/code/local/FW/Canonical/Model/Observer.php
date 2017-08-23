<?php
/**
 * @category    FW
 * @package     FW_Canonical
 * @copyright   Copyright (c) 2012 F+W Media, Inc. (http://www.fwmedia.com)
 * @author		J.P. Daniel <jp.daniel@fwmedia.com>
 */
class FW_Canonical_Model_Observer
{		
    /**
     * @deprecated All functionality is now in onAfterGenerateBlocks() method
     */	
	public function onCmsPageRender(Varien_Event_Observer $observer) { }
	
		
    /**
     * Add the canonical tag to the head block
     * @param Varien_Event_Observer $observer
     */	
	public function onAfterGenerateBlocks(Varien_Event_Observer $observer) 
	{
		$canonical = '';
		$robots = '';
		
		$class =  get_class($observer->getAction());
		switch ($class) {
			case 'Mage_CatalogSearch_AdvancedController':
			case 'Mage_CatalogSearch_ResultController':
			case 'Mage_CatalogSearch_TermController':
			case 'Mage_Customer_AccountController':
				$robots = 'noindex,follow';
				break;
		}
		
		$action = $observer->getAction()->getFullActionName();
		if ($action !== 'cms_index_noRoute')	// Never display canonical on 404 page
		{
			switch ($action) {
				case 'catalog_product_gallery':		// Product image gallery
					$robots = 'noindex,follow';
					break;
				case 'cms_index_index':		// Root of the site (Home Page)
					$canonical = Mage::getBaseUrl();		// Set canonical to root page
					break;
				case 'cms_page_view':
					$helper = Mage::Helper('cms/page');			// CMS Page Helper
					$page = Mage::getSingleton('cms/page');		// Current CMS Page
					if ($page->getIdentifier() === Mage::getStoreConfig($helper::XML_PATH_HOME_PAGE))	// Check if Page is the Home Page
					{	// CMS Page is the Home Page
						$canonical = Mage::getBaseUrl();		// Set canonical to root page
					} else {	// CMS Page is NOT the Home Page
						$canonical = $helper->getPageUrl($page->getPageId());		// Set canonical to page url
					}
					break;
				case 'review_product_list':		// Product Review List Page
				case 'review_product_view':		// Product Review Detail Page
					$canonical = Mage::registry('product')->getProductUrl();		// Set canonical to product url
					$robots = 'noindex,follow';
					break;
			}
			if ($canonical || $robots)
			{		// There is a canonical or robots tag that needs to be added
				$layout = Mage::app()->getFrontController()->getAction()->getLayout();	// Get the layout	
				$htmlHead = $layout->getBlock('head');									// Get head block
				if (empty($htmlHead)) return;											// head block doesn't exist
				
				if ($canonical) $htmlHead->addLinkRel('canonical', $canonical);	// Add the canonical tag to the head block
				if ($robots) $htmlHead->setRobots($robots);				// Change the robots meta tag in the head block
			}
		}
	}
}
