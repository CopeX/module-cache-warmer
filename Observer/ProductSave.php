<?php
/**
 * Copyright © 2019 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 *
 * Magenest_CacheWarmer extension
 * NOTICE OF LICENSE
 *
 * @category Magenest
 * @package Magenest_CacheWarmer
 */

namespace Magenest\CacheWarmer\Observer;

use Magenest\CacheWarmer\Helper\Config;
use Magenest\CacheWarmer\Model\Queue;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\UrlRewrite\Model\ResourceModel\UrlRewriteCollectionFactory;
use Magenest\CacheWarmer\Logger\Logger;

class ProductSave implements ObserverInterface
{
    protected $urlCollection;
    protected $queue;
    protected $config;
    protected $logger;

    public function __construct(
        UrlRewriteCollectionFactory $urlRewriteCollectionFactory,
        Queue $queue,
        Config $config,
        Logger $logger
    )
    {
        $this->urlCollection = $urlRewriteCollectionFactory;
        $this->queue = $queue;
        $this->config = $config;
        $this->logger = $logger;
    }

    public function execute(Observer $observer)
    {
        if (!$this->config->isModuleEnabled()) {
            return;
        }
        if (!$this->config->isGenerateProductSaveEnabled() && !$this->config->isHitProductSaveEnabled()) {
            return;
        }
        try {
            $productId = $observer->getProduct()->getEntityId();
            $categoryIds = $observer->getProduct()->getCategoryIds();
            $categoryUrls = array();
            foreach ($categoryIds as $categoryId) {
                $categoryUrls = array_merge($categoryUrls, $this->urlCollection->create()->addFieldToFilter('entity_type', 'category')->addFieldToFilter('entity_id', $categoryId)->getData());
            }
            $productUrls = $this->urlCollection->create()->addFieldToFilter('entity_type', 'product')->addFieldToFilter('entity_id', $productId)->getData();
            $urlCollection = array_merge($categoryUrls, $productUrls);
            foreach ($urlCollection as $url) {
                $storeId = $url['store_id'] ?? null;
                $baseUrl = $this->queue->getBaseUrl($storeId);
                if ($this->config->isAddStoreCodeToUrlsEnabled($storeId)) {
                    $baseUrl = $baseUrl . $this->queue->getStore($storeId)->getCode() . '/';
                }
                $item = $baseUrl . $url['request_path'];
                if ($this->config->isGenerateProductSaveEnabled()) {
                    $this->queue->customEnqueue($item);
                }
                if ($this->config->isHitProductSaveEnabled()) {
                    $this->queue->customDequeue($item);
                }
            }
        } catch (\Exception $e) {
            $this->logger->info(__($e->getMessage()));
        }
    }
}