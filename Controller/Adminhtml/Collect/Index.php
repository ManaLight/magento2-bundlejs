<?php
/*
 *  Copyright Pure Mashiro. All rights reserved.
 *  @author Mana Light
 */

declare(strict_types=1);

namespace PureMashiro\BundleJs\Controller\Adminhtml\Collect;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\Url;
use PureMashiro\BundleJs\Model\AutoCollect;
use PureMashiro\BundleJs\Model\ManualCollect;

class Index extends Action
{
    /**
     * @var CacheInterface
     */
    private $cache;

    /**
     * @var Url
     */
    private $url;

    /**
     * Index constructor.
     * @param Context $context
     * @param CacheInterface $cache
     * @param Url $url
     */
    public function __construct(
        Context $context,
        CacheInterface $cache,
        Url $url
    ) {
        parent::__construct($context);
        $this->cache = $cache;
        $this->url = $url;
    }

    /**
     * Execute.
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        $mode = $this->getRequest()->getParam('mode');
        $param = $this->generateRandomString();
        $url = $this->getUrl($this->_redirect->getRefererUrl());

        if ($mode === AutoCollect::MODE) {
            $this->cache->save($param, AutoCollect::IDENTIFIER, [], AutoCollect::LIFE_TIME);
            $url = $this->url->getUrl('bundlejs/collect', ['auto_collect' => $param]);
        } elseif ($mode === ManualCollect::MODE) {
            $this->cache->save($param, ManualCollect::IDENTIFIER, [], ManualCollect::LIFE_TIME);
            $url = $this->url->getUrl('bundlejs/collect', ['manual_collect' => $param]);
        }

        $this->getResponse()->setRedirect($url);
    }

    /**
     * Generate Random String.
     *
     * @param int $length
     * @return string
     */
    public function generateRandomString($length = 10)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';

        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }

        return $randomString;
    }
}
