<?php
/*
 * Copyright Pure Mashiro. All rights reserved.
 * @author Mana Light
 */

declare(strict_types=1);

namespace PureMashiro\BundleJs\Controller\Result;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\DataObject;
use Magento\Framework\View\Result\Layout;
use PureMashiro\BundleJs\Helper\Config as ConfigHelper;
use PureMashiro\BundleJs\Helper\DeferJsReplacer as HelperDeferJsReplacer;
use PureMashiro\BundleJs\Model\DeferJsReplacer;
use PureMashiro\BundleJs\Model\Validator\IsAllowedStaticPage;

class DeferInternalJsPlugin
{
    /**
     * @var ConfigHelper
     */
    private $configHelper;

    /**
     * @var DeferJsReplacer
     */
    private $deferJsReplacer;

    /**
     * @var DataObject
     */
    private $dataObject;

    /**
     * @var HelperDeferJsReplacer
     */
    private $helperDeferJs;

    /**
     * @var IsAllowedStaticPage
     */
    private $isAllowedStaticPage;

    /**
     * Construct.
     *
     * @param ConfigHelper $configHelper
     * @param DeferJsReplacer $deferJsReplacer
     * @param DataObject $dataObject
     * @param HelperDeferJsReplacer $helperDeferJs
     * @param IsAllowedStaticPage $isAllowedStaticPage
     */
    public function __construct(
        ConfigHelper          $configHelper,
        DeferJsReplacer       $deferJsReplacer,
        DataObject            $dataObject,
        HelperDeferJsReplacer $helperDeferJs,
        IsAllowedStaticPage   $isAllowedStaticPage
    ) {
        $this->configHelper = $configHelper;
        $this->deferJsReplacer = $deferJsReplacer;
        $this->dataObject = $dataObject;
        $this->helperDeferJs = $helperDeferJs;
        $this->isAllowedStaticPage = $isAllowedStaticPage;
    }

    /**
     * Set After Render Result.
     *
     * @param Layout $subject
     * @param Layout $result
     * @param ResponseInterface $httpResponse
     * @return Layout
     */
    public function afterRenderResult(Layout $subject, Layout $result, ResponseInterface $httpResponse)
    {
        if (!$this->configHelper->canDeferBundleJs()) {
            return $result;
        }

        if ($this->isAllowedStaticPage->validate($subject->getLayout())) {
            $content = (string)$httpResponse->getContent();
            $this->dataObject->setHelperDeferJsReplacer($this->helperDeferJs);
            $dom = $this->deferJsReplacer->replaceHtml($content, $this->dataObject);
            $httpResponse->setContent(is_string($dom) ? $dom : $dom->save());
        }

        return $result;
    }
}