<?php

namespace DoAgency\FreeSample\Plugin\Checkout\CustomerData;

use Magento\Quote\Model\Quote\Item;

class DefaultItem
{
    public function aroundGetItemData($subject, \Closure $proceed, Item $item)
    {
        $itemOptions = array();
        $freeSample = "";
        
        $data = $proceed($item);
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $product = $item->getProduct();
        
        $options = $data['options'];
        foreach($options as $option){
            $itemInfo = $option['value'][0];
            $itemQty = intval(substr($itemInfo, 0, 2))*100;
            $nameStartPosition = strpos($itemInfo, "x")+2;       
            $nameEndPosition = strpos($itemInfo, "<span")-5;
            $itemName = substr($itemInfo, $nameStartPosition, $nameEndPosition);
            
            $itemOptions[] = array("option_weight" => $itemQty."gr", "option_name" => $itemName);
        }
        
        $categories = $product->getCategoryIds();
        if(count($categories) > 0){
            $category = $objectManager->create('Magento\Catalog\Model\Category')->load($categories[0]);
            if(count($categories) == 1 && $category->getName() == "freesample"){
                $freeSample = $category->getName();
            }
        }
        
        $atts = [
            "product_bundle_options" => $itemOptions,
            "product_category" => $freeSample
        ];

        return array_merge($data, $atts);
    }
}