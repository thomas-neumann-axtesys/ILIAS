<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class RunTimeTest extends TestCase
{
    private function markTestSkippedWhenNoRunningOnPhp(): void
    {
        if (!defined('HHVM_VERSION')) {
            return;
        }

        $this->markTestSkipped('Cannot run on HHVM');
    }

    public function testGetVersionReturnsPhpVersionWhenRunningPhp(): void
    {
        $this->markTestSkippedWhenNoRunningOnPhp();

        $this->assertSame(PHP_VERSION, ilRuntime::getInstance()->getVersion());
    }

    public function testGetNameReturnsPhpWhenRunningOnPhp(): void
    {
        $this->markTestSkippedWhenNoRunningOnPhp();

        $this->assertSame('PHP', ilRuntime::getInstance()->getName());
    }

    public function testBinaryCanBeRetrieved(): void
    {
        $this->assertNotEmpty(ilRuntime::getInstance()->getBinary());
    }
}
