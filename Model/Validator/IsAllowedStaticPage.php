<?php
/*
 * Copyright Pure Mashiro. All rights reserved.
 * @author Mana Light
 */

declare(strict_types=1);

namespace PureMashiro\BundleJs\Model\Validator;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\LayoutInterface;
use PureMashiro\BundleJs\Helper\Config as ConfigHelper;

class IsAllowedStaticPage
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var ConfigHelper
     */
    private $configHelper;

    /**
     * @param RequestInterface $request
     * @param ConfigHelper $configHelper
     */
    public function __construct(
        RequestInterface $request,
        ConfigHelper     $configHelper
    ) {
        $this->request = $request;
        $this->configHelper = $configHelper;
    }

    /**
     * @param LayoutInterface $layout
     * @return bool
     */
    public function validate(LayoutInterface $layout): bool
    {
        $fullActionName = (string)$this->request->getFullActionName();
        if (!in_array($fullActionName, $this->configHelper->getAllowedStaticPages())) {
            return false;
        }

        return !$this->request->isAjax()
            && ($this->request->isGet() || $this->request->isHead())
            && $layout->isCacheable();
    }
}
