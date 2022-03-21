<?php

namespace Tests\ReineRougeContactForm7;

use PHPUnit\Framework\TestCase;
use ReineRougeContactForm7\Settings;

class SettingsTest extends TestCase
{
    /**
     * @test
     * @group Settings
     */
    public function tabIsUnique(): void
    {
        $settings = new Settings();

        self::assertCount(1, $settings::getTabs());
    }
}
