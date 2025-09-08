<?php

declare(strict_types=1);

namespace HarmonicDigital\DoctrineDbal\Logging;

use Doctrine\DBAL\Driver as DriverInterface;
use Doctrine\DBAL\Driver\Middleware as MiddlewareInterface;
use Psr\Log\LoggerInterface;

/** @psalm-api */
final readonly class Middleware implements MiddlewareInterface
{
    private DoctrineLoggerInterface $logger;

    public function __construct(
        DoctrineLoggerInterface|LoggerInterface $logger,
    ) {
        $this->logger = $logger instanceof LoggerInterface ? new DoctrineLogger($logger) : $logger;
    }

    #[\Override]
    public function wrap(DriverInterface $driver): DriverInterface
    {
        return new Driver($driver, $this->logger);
    }
}
