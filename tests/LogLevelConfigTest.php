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

    public function testDefault(): void
    {
        $config = new DoctrineLogger($this->logger);
        self::assertSame(LogLevel::DEBUG, $config->getLevel(DoctrineLoggerInterface::LOG_BEGIN_TRANSACTION));
        self::assertSame(LogLevel::INFO, $config->getLevel(DoctrineLoggerInterface::LOG_CONNECT));
        self::assertSame(LogLevel::DEBUG, $config->getLevel(DoctrineLoggerInterface::LOG_COMMIT));
        self::assertSame(LogLevel::INFO, $config->getLevel(DoctrineLoggerInterface::LOG_DISCONNECT));
        self::assertSame(LogLevel::DEBUG, $config->getLevel(DoctrineLoggerInterface::LOG_EXECUTE));
        self::assertSame(LogLevel::DEBUG, $config->getLevel(DoctrineLoggerInterface::LOG_QUERY));
        self::assertSame(LogLevel::DEBUG, $config->getLevel(DoctrineLoggerInterface::LOG_ROLL_BACK));
        self::assertSame(LogLevel::DEBUG, $config->getLevel(DoctrineLoggerInterface::LOG_STATEMENT));
    }

    public function testWithOverrides(): void
    {
        $config = new DoctrineLogger($this->logger, [
            DoctrineLoggerInterface::LOG_CONNECT => LogLevel::DEBUG,
            DoctrineLoggerInterface::LOG_ROLL_BACK => LogLevel::WARNING,
            DoctrineLoggerInterface::LOG_DISCONNECT => LogLevel::DEBUG,
        ]);
        self::assertSame(LogLevel::DEBUG, $config->getLevel(DoctrineLoggerInterface::LOG_BEGIN_TRANSACTION));
        self::assertSame(LogLevel::DEBUG, $config->getLevel(DoctrineLoggerInterface::LOG_CONNECT));
        self::assertSame(LogLevel::DEBUG, $config->getLevel(DoctrineLoggerInterface::LOG_COMMIT));
        self::assertSame(LogLevel::DEBUG, $config->getLevel(DoctrineLoggerInterface::LOG_DISCONNECT));
        self::assertSame(LogLevel::DEBUG, $config->getLevel(DoctrineLoggerInterface::LOG_EXECUTE));
        self::assertSame(LogLevel::DEBUG, $config->getLevel(DoctrineLoggerInterface::LOG_QUERY));
        self::assertSame(LogLevel::WARNING, $config->getLevel(DoctrineLoggerInterface::LOG_ROLL_BACK));
        self::assertSame(LogLevel::DEBUG, $config->getLevel(DoctrineLoggerInterface::LOG_STATEMENT));
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
        $logger->log(DoctrineLoggerInterface::LOG_ROLL_BACK);
        $this->assertTrue($this->logger->hasDebug('Custom message'));
        $this->assertFalse($this->logger->hasDebug('Connecting with parameters'));
        $this->assertTrue($this->logger->hasWarning('Rolling back transaction'));
    }
}
