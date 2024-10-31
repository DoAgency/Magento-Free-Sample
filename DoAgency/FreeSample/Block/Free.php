<?php

namespace DoAgency\FreeSample\Block;

use Magento\Store\Model\ScopeInterface;

class Free extends \Magento\Framework\View\Element\Template
{
    protected $_categoryFactory;
    
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        array $data = []
    ) {
        $this->_categoryFactory = $categoryFactory;
        $this->_scopeConfig = $scopeConfig;        $this->_productCollectionFactory = $productCollectionFactory;        parent::__construct($context, $data);
    }

    public function getCategory($categoryId) 
    {
        $category = $this->_categoryFactory->create();
        $category->load($categoryId);
        return $category;
    }
    
    public function getCategoryProducts($categoryId) 
    {
        $products = $this->getCategory($categoryId)->getProductCollection();
        $products->addAttributeToSelect('*');
        return $products;
    }        public function getProductCollection($category_id_array)    {        $collection = $this->_productCollectionFactory->create();        $collection->addAttributeToSelect('*');        $collection->addCategoriesFilter(['in' => $category_id_array]);//         $collection->addAttributeToFilter('visibility', \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH);        $collection->addAttributeToFilter('status',\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED);        $collection->setPageSize(6); // fetching only 6 products        return $collection;    }
    
    public function getImport() 
    {
        $import = $this->_scopeConfig->getValue('configuration/limit/free_import', ScopeInterface::SCOPE_STORE);
        return $import;
    }
    
    public function getMax() 
    {
        $max = $this->_scopeConfig->getValue('configuration/limit/free_max', ScopeInterface::SCOPE_STORE);
        return $max;
    }
}