<?php

declare(strict_types=1);

namespace HarmonicDigital\DoctrineDbal\Logging;

use Doctrine\DBAL\Driver\Connection as ConnectionInterface;
use Doctrine\DBAL\Driver\Middleware\AbstractConnectionMiddleware;
use Doctrine\DBAL\Driver\Result;
use Doctrine\DBAL\Driver\Statement as DriverStatement;

final class Connection extends AbstractConnectionMiddleware
{
    /** @internal This connection can be only instantiated by its driver. */
    public function __construct(
        ConnectionInterface $connection,
        private readonly DoctrineLoggerInterface $logger,
    ) {
        parent::__construct($connection);
    }

    public function __destruct()
    {
        $this->logger->log(
            DoctrineLoggerInterface::LOG_DISCONNECT,
        );
    }

    #[\Override]
    public function prepare(string $sql): DriverStatement
    {
        return new Statement(
            parent::prepare($sql),
            $this->logger,
            $sql,
        );
    }

    #[\Override]
    public function query(string $sql): Result
    {
        $this->logger->log(
            DoctrineLoggerInterface::LOG_QUERY,
            ['sql' => $sql],
        );

        return parent::query($sql);
    }

    #[\Override]
    public function exec(string $sql): int|string
    {
        $this->logger->log(
            DoctrineLoggerInterface::LOG_EXECUTE,
            ['sql' => $sql],
        );

        return parent::exec($sql);
    }

    #[\Override]
    public function beginTransaction(): void
    {
        $this->logger->log(
            DoctrineLoggerInterface::LOG_BEGIN_TRANSACTION,
        );

        parent::beginTransaction();
    }

    #[\Override]
    public function commit(): void
    {
        $this->logger->log(
            DoctrineLoggerInterface::LOG_COMMIT,
        );

        parent::commit();
    }

    #[\Override]
    public function rollBack(): void
    {
        $this->logger->log(
            DoctrineLoggerInterface::LOG_ROLL_BACK,
        );

        parent::rollBack();
    }
}
