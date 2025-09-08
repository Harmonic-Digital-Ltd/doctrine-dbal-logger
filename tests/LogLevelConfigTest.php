<?php

declare(strict_types=1);

namespace HarmonicDigital\DoctrineDbal\Logging\Tests;

use HarmonicDigital\DoctrineDbal\Logging\DoctrineLogger;
use HarmonicDigital\DoctrineDbal\Logging\DoctrineLoggerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;
use Psr\Log\Test\TestLogger;

/**
 * @internal
 */
#[CoversClass(DoctrineLoggerInterface::class)]
final class LogLevelConfigTest extends TestCase
{
    private TestLogger $logger;

    protected function setUp(): void
    {
        $this->logger = new TestLogger();
    }

    public function testWithCustomMessages(): void
    {
        $logger = new DoctrineLogger($this->logger, [
            DoctrineLoggerInterface::LOG_CONNECT => LogLevel::DEBUG,
            DoctrineLoggerInterface::LOG_ROLL_BACK => LogLevel::WARNING,
            DoctrineLoggerInterface::LOG_DISCONNECT => LogLevel::DEBUG,
        ], [
            DoctrineLoggerInterface::LOG_CONNECT => 'Custom message',
        ]);

        $logger->log(DoctrineLoggerInterface::LOG_CONNECT);
        $this->assertTrue($this->logger->hasDebug('Custom message'));
        $logger->log(DoctrineLoggerInterface::LOG_ROLL_BACK);
        $this->assertFalse($this->logger->hasDebug('Connecting with parameters'));
        $this->assertTrue($this->logger->hasWarning('Rolling back transaction'));
    }
}
