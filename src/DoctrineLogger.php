<?php

declare(strict_types=1);

namespace HarmonicDigital\DoctrineDbal\Logging;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

final readonly class DoctrineLogger implements DoctrineLoggerInterface
{
    private const DEFAULT_LEVELS = [
        self::LOG_BEGIN_TRANSACTION => LogLevel::DEBUG,
        self::LOG_CONNECT => LogLevel::INFO,
        self::LOG_COMMIT => LogLevel::DEBUG,
        self::LOG_DISCONNECT => LogLevel::INFO,
        self::LOG_EXECUTE => LogLevel::DEBUG,
        self::LOG_QUERY => LogLevel::DEBUG,
        self::LOG_ROLL_BACK => LogLevel::DEBUG,
        self::LOG_STATEMENT => LogLevel::DEBUG,
    ];

    private const DEFAULT_MESSAGES = [
        self::LOG_BEGIN_TRANSACTION => 'Beginning transaction',
        self::LOG_COMMIT => 'Committing transaction',
        self::LOG_CONNECT => 'Connecting with parameters {params}',
        self::LOG_DISCONNECT => 'Disconnecting',
        self::LOG_EXECUTE => 'Executing statement: {sql}',
        self::LOG_QUERY => 'Executing query: {sql}',
        self::LOG_ROLL_BACK => 'Rolling back transaction',
        self::LOG_STATEMENT => 'Executing statement: {sql} (parameters: {params}, types: {types})',
    ];

    /**
     * @param array<self::LOG_*, LogLevel::*> $logLevels   any log levels you want to override
     * @param array<self::LOG_*, string>      $logMessages any messages you want to override
     */
    public function __construct(
        private LoggerInterface $logger,
        private array $logLevels = [],
        private array $logMessages = [],
    ) {}

    #[\Override]
    public function log(string $log, array $context = []): void
    {
        $this->logger->log(
            $this->logLevels[$log] ?? self::DEFAULT_LEVELS[$log],
            $this->logMessages[$log] ?? self::DEFAULT_MESSAGES[$log],
            $context,
        );
    }
}
