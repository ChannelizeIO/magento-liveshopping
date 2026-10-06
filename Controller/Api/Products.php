<?php
/**
 * Copyright 2024 Channelize.io. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Channelize\LiveShopping\Controller\Api;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Psr\Log\LoggerInterface;

/**
 * Lists/searches the store's catalog for the Channelize Dashboard's
 * "Store Products" picker, mirroring the WooCommerce plugin's
 * GET /wp-json/api/products contract (same auth scheme, query params and
 * response envelope) so the Dashboard's existing generic product-fetch code
 * works against Magento unchanged.
 */
class Products extends Action
{
    private const DEFAULT_LIMIT = 25;
    private const MAX_LIMIT = 250;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var \Magento\Framework\Api\FilterBuilder
     */
    private $filterBuilder;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Catalog\Helper\Image
     */
    private $imageHelper;

    /**
     * @var \Channelize\LiveShopping\Helper\Config
     */
    private $config;

    /**
     * @var ResponseInterface
     */
    private $httpResponse;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param Context $context
     * @param ResponseInterface $response
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Framework\Api\FilterBuilder $filterBuilder
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Helper\Image $imageHelper
     * @param \Channelize\LiveShopping\Helper\Config $config
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        ResponseInterface $response,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\Api\FilterBuilder $filterBuilder,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Channelize\LiveShopping\Helper\Config $config,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->httpResponse = $response;
        $this->productRepository = $productRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->storeManager = $storeManager;
        $this->imageHelper = $imageHelper;
        $this->config = $config;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        $this->setCorsHeaders();

        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);

        // Browser CORS preflight - no auth required, nothing to return.
        if ($this->getRequest()->getMethod() === 'OPTIONS') {
            return $resultJson->setHttpResponseCode(200)->setData([]);
        }

        if (!$this->isAuthorized()) {
            return $resultJson->setHttpResponseCode(401)->setData([
                'statusCode' => 401,
                'message' => __('Authentication failed'),
                'error' => true,
            ]);
        }

        try {
            [$items, $totalCount] = $this->fetchProducts(
                (string)$this->getRequest()->getParam('title', ''),
                (string)$this->getRequest()->getParam('id', ''),
                (int)$this->getRequest()->getParam('limit', self::DEFAULT_LIMIT),
                (int)$this->getRequest()->getParam('skip', 0)
            );

            return $resultJson->setData([
                'statusCode' => 200,
                'success' => 'OK',
                'count' => $totalCount,
                'products' => array_values(array_map([$this, 'toProductData'], $items)),
            ]);
        } catch (\Exception $exception) {
            $this->logger->error('Channelize LiveShopping products API error: ' . $exception->getMessage(), [
                'exception' => $exception,
            ]);
            return $resultJson->setHttpResponseCode(500)->setData([
                'statusCode' => 500,
                'message' => __('Unable to fetch products.'),
                'error' => true,
            ]);
        }
    }

    /**
     * Validate the Authorization header against the module's stored private key.
     *
     * Same shared-secret scheme as the WooCommerce plugin's REST endpoint:
     * "Authorization: Basic base64(private_key)".
     *
     * @return bool
     */
    private function isAuthorized(): bool
    {
        $privateKey = $this->config->getPrivateKey();
        if (!$privateKey) {
            return false;
        }

        $expected = 'Basic ' . base64_encode($privateKey);
        $actual = $this->getRequest()->getHeader('Authorization');

        return is_string($actual) && hash_equals($expected, $actual);
    }

    /**
     * @param string $title
     * @param string $idList
     * @param int $limit
     * @param int $skip
     * @return array{0: \Magento\Catalog\Api\Data\ProductInterface[], 1: int}
     */
    private function fetchProducts(string $title, string $idList, int $limit, int $skip): array
    {
        $limit = $limit > 0 ? min($limit, self::MAX_LIMIT) : self::DEFAULT_LIMIT;
        $skip = max(0, $skip);

        $this->filterBuilder->setField('status')
            ->setConditionType('eq')
            ->setValue(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED);
        $this->searchCriteriaBuilder->addFilters([$this->filterBuilder->create()]);

        $this->filterBuilder->setField('visibility')
            ->setConditionType('neq')
            ->setValue(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_NOT_VISIBLE);
        $this->searchCriteriaBuilder->addFilters([$this->filterBuilder->create()]);

        if ($title !== '') {
            $this->filterBuilder->setField('name')
                ->setConditionType('like')
                ->setValue('%' . $title . '%');
            $this->searchCriteriaBuilder->addFilters([$this->filterBuilder->create()]);
        }

        if ($idList !== '') {
            $ids = array_values(array_filter(array_map('trim', explode(',', $idList)), 'strlen'));
            if ($ids) {
                $this->filterBuilder->setField('entity_id')
                    ->setConditionType('in')
                    ->setValue($ids);
                $this->searchCriteriaBuilder->addFilters([$this->filterBuilder->create()]);
            }
        }

        $this->searchCriteriaBuilder->setPageSize($limit);
        $this->searchCriteriaBuilder->setCurrentPage(intdiv($skip, $limit) + 1);

        $searchResults = $this->productRepository->getList($this->searchCriteriaBuilder->create());

        return [$searchResults->getItems(), (int)$searchResults->getTotalCount()];
    }

    /**
     * Map a product to the response shape the Dashboard's "Store Products"
     * picker reads: id, title, price, regular_price, sku, product_url,
     * image, description, currency.
     *
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @return array
     */
    private function toProductData($product): array
    {
        $price = null;
        $regularPrice = null;
        try {
            $price = $product->getPriceInfo()->getPrice('final_price')->getValue();
            $regularPrice = $product->getPriceInfo()->getPrice('regular_price')->getValue();
        } catch (\Exception $exception) {
            // Price info unavailable (e.g. product type without pricing) - leave null.
        }

        return [
            'id' => (int)$product->getId(),
            'title' => $product->getName(),
            'price' => $price !== null ? (float)$price : null,
            'regular_price' => $regularPrice !== null ? (float)$regularPrice : null,
            'sku' => $product->getSku(),
            'product_url' => method_exists($product, 'getProductUrl') ? $product->getProductUrl() : null,
            'image' => $this->imageHelper->init($product, 'product_thumbnail_image')->getUrl(),
            'description' => $product->getShortDescription(),
            'currency' => $this->storeManager->getStore()->getCurrentCurrencyCode(),
        ];
    }

    /**
     * Permissive CORS so the Dashboard (a different origin) can call this
     * endpoint directly from the browser, same as the WooCommerce plugin's
     * rest_pre_serve_request CORS filter.
     *
     * @return void
     */
    private function setCorsHeaders(): void
    {
        $this->httpResponse->setHeader('Access-Control-Allow-Origin', '*', true);
        $this->httpResponse->setHeader('Access-Control-Allow-Methods', 'GET, OPTIONS', true);
        $this->httpResponse->setHeader('Access-Control-Allow-Headers', 'Authorization, Content-Type', true);
    }
}
