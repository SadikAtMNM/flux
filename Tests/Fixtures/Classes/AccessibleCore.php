<?php

namespace FluidTYPO3\Flux\Tests\Fixtures\Classes;

use FluidTYPO3\Flux\Core;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;

class AccessibleCore extends Core
{
    protected static function getAbsolutePathForFilename(string $filename): string
    {
        return $filename;
    }

    public static function setObjectManager(?ObjectManagerInterface $objectManager): void
    {
        static::$objectManager = $objectManager;
    }

    public static function resetQueuedRegistrations(): void
    {
        static::$queuedContentTypeRegistrations = [];
        static::$extensions = [self::CONTROLLER_ALL => []];
    }

    public static function setRegisteredProviders(array $providers): void
    {
        static::$providers = $providers;
    }
}
