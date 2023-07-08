<?php
/*
 *  Copyright Pure Mashiro. All rights reserved.
 *  @author Mana Light
 */

declare(strict_types=1);

namespace PureMashiro\BundleJs\Action;

use Magento\Framework\App\RequestInterface;
use PureMashiro\BundleJs\Helper\Config as ConfigHelper;
use PureMashiro\BundleJs\Model\BundleByType;
use PureMashiro\BundleJs\Model\TypeMapper;

class GetBundleTypes
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var TypeMapper
     */
    private $typeMapper;

    /**
     * @var ConfigHelper
     */
    private $configHelper;

    /**
     * GetBundleTypes constructor.
     * @param RequestInterface $request
     * @param TypeMapper $typeMapper
     * @param ConfigHelper $configHelper
     */
    public function __construct(
        RequestInterface $request,
        TypeMapper       $typeMapper,
        ConfigHelper     $configHelper
    ) {
        $this->request = $request;
        $this->typeMapper = $typeMapper;
        $this->configHelper = $configHelper;
    }

    /**
     * Execute.
     *
     * @return array
     */
    public function execute()
    {
        $fullActionName = $this->request->getFullActionName();
        $pathInfo = $this->request->getOriginalPathInfo();
        $type = $this->typeMapper->map($fullActionName, $pathInfo);

        $critical = $this->configHelper->canDeferBundleJs();
        if ($critical && $type && in_array($type, BundleByType::CRITICAL_TYPES)) {
            $criticalBundles = ['critical_' . BundleByType::TYPE_COMMON, 'critical_' . $type];
            $nonCriticalBundles = ['noncritical_' . BundleByType::TYPE_COMMON, 'noncritical_' . $type];
            return array_merge($criticalBundles, $nonCriticalBundles);
        }

        return empty($type) ? [BundleByType::TYPE_COMMON] : [BundleByType::TYPE_COMMON, $type];
    }
}
