<?php
/**
 * Copyright © Gigadesign. All rights reserved.
 */
declare(strict_types=1);

namespace Gigadesign\Layout\View\Layout\Reader;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ScopeResolverInterface;
use Magento\Framework\Data\Argument\InterpreterInterface;
use Magento\Framework\View\Layout;
use Magento\Framework\View\Layout\Reader\Block as BaseBlock;
use Magento\Framework\View\Layout\Reader\Visibility\Condition;

/**
 * Block structure reader
 */
class Block extends BaseBlock
{
    /**
     * @var ScopeResolverInterface
     */
    protected $scopeResolver;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Constructor
     *
     * @param Layout\ScheduledStructure\Helper $helper
     * @param Layout\Argument\Parser $argumentParser
     * @param Layout\ReaderPool $readerPool
     * @param InterpreterInterface $argumentInterpreter
     * @param Condition $conditionReader
     * @param ScopeConfigInterface $scopeConfig
     * @param ScopeResolverInterface $scopeResolver
     * @param string|null $scopeType
     */
    public function __construct(
        Layout\ScheduledStructure\Helper $helper,
        Layout\Argument\Parser $argumentParser,
        Layout\ReaderPool $readerPool,
        InterpreterInterface $argumentInterpreter,
        Condition $conditionReader,
        ScopeConfigInterface $scopeConfig,
        ScopeResolverInterface $scopeResolver,
        $scopeType = null
    ) {
        parent::__construct(
            $helper,
            $argumentParser,
            $readerPool,
            $argumentInterpreter,
            $conditionReader,
            $scopeType
        );

        $this->scopeConfig = $scopeConfig;
        $this->scopeResolver = $scopeResolver;
    }

    protected function scheduleReference(
        Layout\ScheduledStructure $scheduledStructure,
        Layout\Element $currentElement
    ) {
        $elementName = $currentElement->getAttribute('name');
        $elementRemove = filter_var($currentElement->getAttribute('remove'), FILTER_VALIDATE_BOOLEAN);

        if ($elementRemove) {
            $configPath = (string)$currentElement->getAttribute(Layout\ConfigCondition::NAME);

            if (empty($configPath) ||
                $this->scopeConfig->isSetFlag($configPath, $this->scopeType, $this->scopeResolver->getScope())) {
                $scheduledStructure->setElementToRemoveList($elementName);
                return;
            }
        } elseif ($currentElement->getAttribute('remove')) {
            $scheduledStructure->unsetElementFromListToRemove($elementName);
        }

        $data = $scheduledStructure->getStructureElementData($elementName, []);
        $data['attributes'] = $this->mergeBlockAttributes($data, $currentElement);
        $this->updateScheduledData($currentElement, $data);
        $this->evaluateArguments($currentElement, $data);
        $scheduledStructure->setStructureElementData($elementName, $data);
    }
}