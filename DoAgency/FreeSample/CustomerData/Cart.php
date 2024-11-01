<?php

namespace DoAgency\FreeSample\CustomerData;

use Magento\Customer\CustomerData\SectionSourceInterface;

/**
 * Cart source
 */
class Cart extends \Magento\Checkout\CustomerData\Cart
{   

    /**
     * {@inheritdoc}
     */
    public function getSectionData()
    {
        $totals = $this->getQuote()->getTotals();
        return [
            'summary_count' => $this->getSummaryCount(),
            'subtotal' => isset($totals['subtotal'])
                ? $this->checkoutHelper->formatPrice($totals['subtotal']->getValue())
                : 0,
            'possible_onepage_checkout' => $this->isPossibleOnepageCheckout(),
            'items' => $this->getRecentItems(),
            'extra_actions' => $this->layout->createBlock('Magento\Catalog\Block\ShortcutButtons')->toHtml(),
            'isGuestCheckoutAllowed' => $this->isGuestCheckoutAllowed(),
        ];
    }

    /**
     * Get active quote
     *
     * @return \Magento\Quote\Model\Quote
     */
    protected function getQuote()
    {
        if (null === $this->quote) {
            $this->quote = $this->checkoutSession->getQuote();
        }
        return $this->quote;
    }

    /**
     * Get shopping cart items qty based on configuration (summary qty or items qty)
     *
     * @return int|float
     */
    protected function getSummaryCount()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $freeSample = 0;
        
        if (!$this->summeryCount) {
            $this->summeryCount = $this->checkoutCart->getSummaryQty() ?: 0;
        }
        
        //se nel carrello sono presenti prodotti "Free Sample", li sottraggo dal summeryCount
        foreach ($this->getAllQuoteItems() as $item) {
            $product = $item->getProduct();
            $categories = $product->getCategoryIds();
            if(count($categories) == 1){
                $category = $objectManager->create('Magento\Catalog\Model\Category')->load($categories[0]);
                if($category->getName() == "freesample"){
                    $freeSample++; 
                }
            }
        }

        if($freeSample){
            $freeSample = $this->summeryCount - $freeSample; 
            return $freeSample;
        }
        else
            return $this->summeryCount;
    }

    /**
     * Check if one page checkout is available
     *
     * @return bool
     */
    protected function isPossibleOnepageCheckout()
    {
        return $this->checkoutHelper->canOnepageCheckout() && !$this->getQuote()->getHasError();
    }

    /**
     * Get array of last added items
     *
     * @return \Magento\Quote\Model\Quote\Item[]
     */
    protected function getRecentItems()
    {
        $items = [];
        if (!$this->getSummaryCount()) {
            return $items;
        }

        foreach (array_reverse($this->getAllQuoteItems()) as $item) {

            if (!$item->getProduct()->isVisibleInSiteVisibility()) {
                $productId = $item->getProduct()->getId();
                $products = $this->catalogUrl->getRewriteByProductStore([$productId => $item->getStoreId()]);
                if (!isset($products[$productId])) {
                    continue;
                }
                $urlDataObject = new \Magento\Framework\DataObject($products[$productId]);
                $item->getProduct()->setUrlDataObject($urlDataObject);
            }
            $items[] = $this->itemPoolInterface->getItemData($item);
        }
        return $items;
    }

    /**
     * Return customer quote items
     *
     * @return \Magento\Quote\Model\Quote\Item[]
     */
    protected function getAllQuoteItems()
    {
        if ($this->getCustomQuote()) {
            return $this->getCustomQuote()->getAllItems();
        }
        return $this->getQuote()->getAllVisibleItems();
    }

    /**
     * Check if guest checkout is allowed
     *
     * @return bool
     */
    public function isGuestCheckoutAllowed()
    {
        return $this->checkoutHelper->isAllowedGuestCheckout($this->checkoutSession->getQuote());
    }
}