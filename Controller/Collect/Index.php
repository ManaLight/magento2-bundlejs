<?php
/*
 *  Copyright Pure Mashiro. All rights reserved.
 *  @author Mana Light
 */

declare(strict_types=1);

namespace PureMashiro\BundleJs\Controller\Collect;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Cache\Manager as CacheManager;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Framework\View\Result\PageFactory;
use PureMashiro\BundleJs\Model\AutoCollect;
use PureMashiro\BundleJs\Model\ManualCollect;
use PureMashiro\BundleJs\Helper\Config as ConfigHelper;

class Index extends Action
{
    /**
     * @var CookieManagerInterface
     */
    private $cookieManager;

    /**
     * @var CookieMetadataFactory
     */
    private $cookieMetadataFactory;

    /**
     * @var CacheInterface
     */
    private $cache;

    /**
     * @var PageFactory
     */
    private $pageFactory;

    /**
     * @var SessionManagerInterface
     */
    private $session;

    /**
     * @var ConfigHelper
     */
    private $configHelper;

    /**
     * @var CacheManager
     */
    private $cacheManager;

    private $metadata = null;

    /**
     * Index constructor.
     * @param Context $context
     * @param CookieManagerInterface $cookieManager
     * @param CookieMetadataFactory $cookieMetadataFactory
     * @param CacheInterface $cache
     * @param PageFactory $pageFactory
     * @param SessionManagerInterface $session
     * @param ConfigHelper $configHelper
     * @param CacheManager $cacheManager
     */
    public function __construct(
        Context $context,
        CookieManagerInterface $cookieManager,
        CookieMetadataFactory $cookieMetadataFactory,
        CacheInterface $cache,
        PageFactory $pageFactory,
        SessionManagerInterface $session,
        ConfigHelper $configHelper,
        CacheManager $cacheManager
    ) {
        parent::__construct($context);
        $this->cookieManager = $cookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->cache = $cache;
        $this->pageFactory = $pageFactory;
        $this->session = $session;
        $this->configHelper = $configHelper;
        $this->cacheManager = $cacheManager;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Page
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Stdlib\Cookie\CookieSizeLimitReachedException
     * @throws \Magento\Framework\Stdlib\Cookie\FailureToSendException
     */
    public function execute()
    {
        if (!$this->configHelper->canCollectBundleJs()) {
            return $this->redirectToHomepage();
        }

        $autoCollectParam = $this->getRequest()->getParam('auto_collect');
        if ($autoCollectParam) {
            return $this->executeAuto($autoCollectParam);
        }

        $manualCollectParam = $this->getRequest()->getParam('manual_collect');
        if ($manualCollectParam) {
            return $this->executeManual($manualCollectParam);
        }

        return $this->redirectToHomepage();
    }

    /**
     * @param $autoCollectParam
     * @return \Magento\Framework\Controller\Result\Redirect|\Magento\Framework\View\Result\Page
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Stdlib\Cookie\CookieSizeLimitReachedException
     * @throws \Magento\Framework\Stdlib\Cookie\FailureToSendException
     */
    public function executeAuto($autoCollectParam)
    {
        $autoCollectInCache = $this->cache->load(AutoCollect::IDENTIFIER);

        if ($autoCollectParam === AutoCollect::STATE_CANCELED) {
            $this->messageManager->addErrorMessage(__('Collecting bundle js has been cancelled.'));
            return $this->deleteCookieAndRedirectToHomepage(AutoCollect::IDENTIFIER, AutoCollect::LIFE_TIME);
        }

        if ($autoCollectParam === AutoCollect::PHASE_VALUE_2) {
            $this->saveCookie(AutoCollect::PHASE, 'phase_2', AutoCollect::LIFE_TIME);
            $this->cache->save('phase_2', AutoCollect::PHASE, [], AutoCollect::LIFE_TIME);
            $this->cacheManager->flush(['full_page']);
            return $this->pageFactory->create();
        }

        if ($autoCollectParam === AutoCollect::STATE_COMPLETE) {
            $this->deleteCookie(AutoCollect::PHASE, AutoCollect::LIFE_TIME);
            $this->cache->remove(AutoCollect::PHASE);
            $this->messageManager->addSuccessMessage(__('Collecting bundle js has been completed.'));
            return $this->deleteCookieAndRedirectToHomepage(AutoCollect::IDENTIFIER, AutoCollect::LIFE_TIME);
        }

        if ($autoCollectParam !== $autoCollectInCache) {
            $this->messageManager->addErrorMessage(__('Collecting bundle js is not allowed.'));
            return $this->deleteCookieAndRedirectToHomepage(AutoCollect::IDENTIFIER, AutoCollect::LIFE_TIME);
        }

        $this->cache->remove(AutoCollect::IDENTIFIER);
        $this->saveCookie(AutoCollect::IDENTIFIER, $autoCollectParam, AutoCollect::LIFE_TIME);
        $this->session->setData(AutoCollect::IDENTIFIER, $autoCollectParam);

        $this->saveCookie(AutoCollect::PHASE, 'phase_1', AutoCollect::LIFE_TIME);
        $this->cache->save('phase_1', AutoCollect::PHASE, [], AutoCollect::LIFE_TIME);

        return $this->pageFactory->create();
    }

    /**
     * @param $manualCollectParam
     * @return \Magento\Framework\Controller\Result\Redirect|\Magento\Framework\View\Result\Page
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Stdlib\Cookie\CookieSizeLimitReachedException
     * @throws \Magento\Framework\Stdlib\Cookie\FailureToSendException
     */
    public function executeManual($manualCollectParam)
    {
        $manualCollectInCache = $this->cache->load(ManualCollect::IDENTIFIER);

        if ($manualCollectParam === AutoCollect::STATE_CANCELED) {
            $this->messageManager->addErrorMessage(__('Collecting bundle js has been cancelled.'));
            return $this->deleteCookieAndRedirectToHomepage(ManualCollect::IDENTIFIER, ManualCollect::LIFE_TIME);
        }

        if ($manualCollectParam !== $manualCollectInCache) {
            $this->messageManager->addErrorMessage(__('Collecting bundle js is not allowed.'));
            return $this->deleteCookieAndRedirectToHomepage(ManualCollect::IDENTIFIER, ManualCollect::LIFE_TIME);
        }

        $this->cache->remove(ManualCollect::IDENTIFIER);
        $this->saveCookie(ManualCollect::IDENTIFIER, $manualCollectParam, ManualCollect::LIFE_TIME);
        $this->session->setData(ManualCollect::IDENTIFIER, $manualCollectParam);

        return $this->pageFactory->create();
    }

    /**
     * @param $key
     * @param $lifetime
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Stdlib\Cookie\FailureToSendException
     */
    public function deleteCookie($key, $lifetime)
    {
        $metadata = $this->getMetadata($lifetime);
        $this->cookieManager->deleteCookie($key, $metadata);
    }

    /**
     * @param $lifetime
     * @return \Magento\Framework\Stdlib\Cookie\PublicCookieMetadata|null
     */
    public function getMetadata($lifetime)
    {
        if ($this->metadata !== null) {
            return $this->metadata;
        }

        return $this->metadata = $this->cookieMetadataFactory
            ->createPublicCookieMetadata()
            ->setPath('/')
            ->setDuration($lifetime);
    }

    /**
     * @TODO: Adding same site or strict attribute
     *
     * @param $key
     * @param $param
     * @param $lifetime
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Stdlib\Cookie\CookieSizeLimitReachedException
     * @throws \Magento\Framework\Stdlib\Cookie\FailureToSendException
     */
    public function saveCookie($key, $param, $lifetime)
    {
        $metadata = $this->getMetadata($lifetime);
        $this->cookieManager->setPublicCookie($key, $param, $metadata);
    }

    /**
     * @param $key
     * @param $lifetime
     * @return \Magento\Framework\Controller\Result\Redirect
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Stdlib\Cookie\FailureToSendException
     */
    public function deleteCookieAndRedirectToHomepage($key, $lifetime)
    {
        $this->deleteCookie($key, $lifetime);
        $this->session->unsetData($key);
        return $this->redirectToHomepage();
    }

    /**
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function redirectToHomepage()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('');
    }
}
