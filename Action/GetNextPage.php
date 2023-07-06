<?php
/*
 *  Copyright Pure Mashiro. All rights reserved.
 *  @author Mana Light
 */

declare(strict_types=1);

namespace PureMashiro\BundleJs\Action;

use Magento\Framework\UrlInterface;
use PureMashiro\BundleJs\Helper\NextPage;

class GetNextPage
{
    /**
     * @var NextPage
     */
    private $nextPage;

    /**
     * @var UrlInterface
     */
    private $url;

    /**
     * @var PopulateBundleType
     */
    private $populateBundleType;

    /**
     * GetNextPage constructor.
     *
     * @param NextPage $nextPage
     * @param UrlInterface $url
     * @param PopulateBundleType $populateBundleType
     */
    public function __construct(
        NextPage $nextPage,
        UrlInterface $url,
        PopulateBundleType $populateBundleType
    ) {
        $this->nextPage = $nextPage;
        $this->url = $url;
        $this->populateBundleType = $populateBundleType;
    }

    /**
     * Execute.
     *
     * @param string $type
     * @param bool $critical
     * @return string|null
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function execute($type, $critical = false)
    {
        if ($critical && $type === 'product') {
            $this->populateBundleType->execute(true);
            $page = $this->nextPage->getCompletePage();
            return $this->url->getUrl($page);
        }

        $nextType = $this->nextPage->getNextType($type);
        if (empty($nextType)) {
            $this->nextPage->emptyShoppingCart();
            $this->populateBundleType->execute();
            $page = $this->nextPage->getPhase2Page();
            return $this->url->getUrl($page);
        }

        // @TODO: Need to implement for page with specific path as well
        $nextPage = $this->nextPage->getDefaultPage($nextType);

        return isset($nextPage) ? $this->url->getUrl($nextPage) : null;
    }
}
