<?php
/**
 * Copyright 2021 Channelize.io. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Channelize\LiveShopping\Controller\Products;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;

class Index extends Action
{
    
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $_productRepository;
    
    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $_urlBuilder;
    
    /**
     * @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable
     */
    private $_configurableProductTypeModel;
    
    /**
     * @var \Magento\GroupedProduct\Model\Product\Type\Grouped
     */
    private $_groupedProductTypeModel;
    
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $_storeManager;
    
    /**
     * @var \Magento\Bundle\Model\Option
     */
    private $_bundleProductOption;
    
    /**
     * @var \Magento\Bundle\Model\Product\Type
     */
    private $_bundleProductType;
    
    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    private $_helperPricing;
    
    /**
     * @param Context $context
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurable
     * @param \Magento\GroupedProduct\Model\Product\Type\Grouped $grouped
     * @param \Magento\Store\Model\StoreManagerInterface $storeManagerInterface
     * @param \Magento\Bundle\Model\Option $bundleProductOption
     * @param \Magento\Bundle\Model\Product\Type $bundleProductType
     * @param \Magento\Framework\Pricing\Helper\Data $helperPricing
     */
    public function __construct(
        Context $context,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurable,
        \Magento\GroupedProduct\Model\Product\Type\Grouped $grouped,
        \Magento\Store\Model\StoreManagerInterface $storeManagerInterface,
        \Magento\Bundle\Model\Option $bundleProductOption,
        \Magento\Bundle\Model\Product\Type $bundleProductType,
        \Magento\Framework\Pricing\Helper\Data $helperPricing
    ) {
        parent::__construct($context);
        $this->_productRepository = $productRepository;
        $this->_urlBuilder = $urlBuilder;
        $this->_configurableProductTypeModel = $configurable;
        $this->_groupedProductTypeModel = $grouped;
        $this->_storeManager = $storeManagerInterface;
        $this->_bundleProductOption = $bundleProductOption;
        $this->_bundleProductType = $bundleProductType;
        $this->_helperPricing = $helperPricing;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $response = [
            "success" => false,
            "products" => []
        ];
        try {
            $productIds = explode(',', $this->getRequest()->getParam("productIds"));
            if (!$productIds) {
                return $resultJson->setData($response);
            }
            
            // Get Products
            foreach ($productIds as $productId) {
                try {
                    $product = $this->_productRepository->getById($productId);
                } catch (\Exception $exception) {
                    continue;
                }
                
                // Check product is active or not
                if ($product->getStatus() != 1) {
                    continue;
                }
                
                // Get product details
                $data = $this->getProuductBasicDetails($product);
                
                // Add extra params in product response based on product type
                switch ($product->getTypeId()) {
                    case 'simple':
                    case 'virtual':
                        $response["products"][] = $data; //$this->getSimpleProductDetails($product, $data);
                        break;

                    case 'downloadable':
                        $response["products"][] = $this->getDownloadableProductDetails($product, $data);
                        break;

                    case 'configurable':
                        $response["products"][] = $this->getConfigurableProductDetails($product, $data);
                        break;

                    case 'grouped':
                        $response["products"][] = $this->getGroupedProductDetails($product, $data);
                        break;

                    case 'bundle':
                        $response["products"][] = $this->getBundleProductDetails($product, $data);
                        break;

                    default:
                        break;
                }
            }
            
            $response["success"] = true;
            return $resultJson->setData($response);
        } catch (\Exception $exception) {
            $response["message"] = __($exception->getMessage());
            return $resultJson->setData($response);
        }
    }
    
    /**
     * Get product basic details
     *
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $product
     * @return array
     */
    private function getProuductBasicDetails($product)
    {
        $productMediaGallery = $product->getMediaGalleryImages();
        $productImages = [];
        foreach ($productMediaGallery as $image) {
            if ($image->getMediaType() == "image") {
                $productImages[] = $image->getUrl();
            }
        }

        $extensionAttributes = $product->getExtensionAttributes();
        $data = [
            "id" => $product->getId(),
            "sku"=> $product->getSku(),
            "name" => $product->getName(),
            "product_type" => $product->getTypeId(),
            "curreny" => $this->_storeManager->getStore()->getCurrentCurrencyCode(),
            "price" => $product->getPriceInfo()->getPrice('regular_price')->getValue(),
            "final_price" => $product->getPriceInfo()->getPrice('final_price')->getValue(),
            "status" => $product->getStatus() == 1,
            "is_in_stock" => $extensionAttributes->getStockItem()->getIsInStock(),
            "qty" => $extensionAttributes->getStockItem()->getQty(),
            "short_description" => $product->getShortDescription(),
            "long_description" => $product->getDescription(),
            "images" => $productImages,
        ];
        
        return $data;
    }

    /**
     * Get download product details
     *
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $product
     * @param array $data
     * @return array
     */
    private function getDownloadableProductDetails($product, $data)
    {
        $extensionAttributes = $product->getExtensionAttributes();
        $downloadableProductLinks = $extensionAttributes->getDownloadableProductLinks();
        
        $modifiedDownloadableProductLinks = [];
        if ($downloadableProductLinks) {
            foreach ($downloadableProductLinks as $item) {
                $modifiedDownloadableProductLinks[] =
                [
                    "id" => $item->getId(),
                    "title" => $item->getTitle(),
                    "sort_order" => $item->getSortOrder(),
                    "price" => $this->_helperPricing->currency($item->getPrice(), false, false),
                    "sample_url" => $item->getSampleType() ?
                        $this->_urlBuilder->getUrl('downloadable/download/linkSample/link_id/' . $item->getId()) : "",
                    /*"sample_type" => $item->getSampleType(),
                    "sample_url" => $item->getSampleUrl(),
                    "sample_file" => $item->getSampleFile()*/
                ];
            }
        }
        
        $data["downloadable_items"] = $modifiedDownloadableProductLinks;
        return $data;
    }
    
    /**
     * Get configurable product details
     *
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $product
     * @param array $data
     * @return array
     */
    private function getConfigurableProductDetails($product, $data)
    {
        // Product attribute options
        $productAttributeOptions = $this->_configurableProductTypeModel->getConfigurableAttributesAsArray($product);
        $modifiedProductAttributeOptions = [];
        if ($productAttributeOptions) {
            foreach ($productAttributeOptions as $item) {
                $modifiedProductAttributeOptions[] = [
                    "attribute_id" => $item["attribute_id"],
                    "attribute_code" => $item["attribute_code"],
                    "position" => $item["position"],
                    "store_label" => $item["store_label"],
                    "options" => $item["values"]
                ];
            }
        }
        $data["product_attribute_options"] = $modifiedProductAttributeOptions;
        
        // Product variants
        $configurableOptions = $product->getTypeInstance()->getConfigurableOptions($product);
        $options = [];
        foreach ($configurableOptions as $attr) {
            foreach ($attr as $p) {
                $options[$p['sku']][$p['attribute_code']] = $p['value_index'];
            }
        }
        
        $productVariants = [];
        foreach ($options as $sku => $attributes) {
            try {
                $pr = $this->_productRepository->get($sku);
            } catch (\Exception $exception) {
                continue;
            }
            
            // Check product is active or not
            if ($pr->getStatus() != 1) {
                continue;
            }
            
            $productVariants[] = [
                "product" => $this->getProuductBasicDetails($pr),
                "attributes" => $attributes
            ];
        }
        
        $data["variants"] = $productVariants;
        return $data;
    }
    
    /**
     * Get grouped product details
     *
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $product
     * @param array $data
     * @return array
     */
    private function getGroupedProductDetails($product, $data)
    {
        
        $groupedProductChild = $this->_groupedProductTypeModel->getAssociatedProducts($product);
        
        $groupedProductChildModifiedData = [];
        foreach ($groupedProductChild as $item) {
            $groupedProductChildModifiedData[] = $item->getData();
        }

        $data["product_child"] = $groupedProductChildModifiedData;
        return $data;
    }
    
    /**
     * Get bundle product details
     *
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $product
     * @param array $data
     * @return array
     */
    private function getBundleProductDetails($product, $data)
    {
        
        $storeId = $this->_storeManager->getStore()->getId();
        $options = $this->_bundleProductOption
            ->getResourceCollection()
            ->setProductIdFilter($product->getId())
            ->setPositionOrder();
        $options->joinValues($storeId);
        
        $selections = $this->_bundleProductType->getSelectionsCollection(
            $this->_bundleProductType->getOptionsIds($product),
            $product
        );
        
        $data["options"] = $options->getData();
        $data["selections"] = $selections->getData();
        return $data;
    }
}
