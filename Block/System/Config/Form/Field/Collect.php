<?php

declare(strict_types=1);

namespace PureMashiro\BundleJs\Block\System\Config\Form\Field;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\View\Helper\SecureHtmlRenderer;

class Collect extends \Magento\Config\Block\System\Config\Form\Field
{
    public const XML_PATH_BUNDLE_COLLECT = 'bundlejs/collect';

    /**
     * @var string
     */
    protected $_template = 'PureMashiro_BundleJs::collect-button.phtml';

    /**
     * @return string
     */
    public function getCollectUrl()
    {
        return $this->getUrl(self::XML_PATH_BUNDLE_COLLECT, ['mode' => $this->getMode()]);
    }

    /**
     * Return element html
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        return $this->_toHtml();
    }
}
