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

namespace Magenest\CacheWarmer\Helper;



use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Config
{
    const GENERAL = 'magenest_cachewarmer/general/';
    const PERFORMANCE = 'magenest_cachewarmer/performance_settings/';

    private ScopeConfigInterface $scopeConfig;

    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    public function isModuleEnabled($storeId = null)
    {
        return $this->scopeConfig->getValue(self::GENERAL . 'enabled', ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function isGenerateProductSaveEnabled($storeId = null)
    {
        return $this->scopeConfig->getValue(self::GENERAL . 'auto_update', ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function isHitProductSaveEnabled($storeId = null)
    {
        return $this->scopeConfig->getValue(self::GENERAL . 'auto_hit', ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function isCacheFlushEnabled($storeId = null)
    {
        return $this->scopeConfig->getValue(self::GENERAL . 'auto_flush', ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function getAdditionalUrls($storeId = null)
    {
        return $this->scopeConfig->getValue(self::GENERAL . 'custom_urls', ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function isScheduleEnabled($storeId = null)
    {
        return $this->scopeConfig->getValue(self::PERFORMANCE . 'enable_schedule', ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function getMaxRequests($storeId = null)
    {
        return $this->scopeConfig->getValue(self::PERFORMANCE . 'max_requests', ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function getScheduledBatchSize($storeId = null)
    {
        return $this->scopeConfig->getValue(self::PERFORMANCE . 'scheduled_batch_size', ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function isAddStoreCodeToUrlsEnabled($storeId = null)
    {
        return $this->scopeConfig->getValue('web/url/use_store', ScopeInterface::SCOPE_STORE, $storeId);
    }
}