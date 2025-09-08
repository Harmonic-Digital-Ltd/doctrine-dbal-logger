<?php

declare(strict_types=1);

namespace HarmonicDigital\DoctrineDbal\Logging;

use Doctrine\DBAL\Driver as DriverInterface;
use Doctrine\DBAL\Driver\Middleware\AbstractDriverMiddleware;

final class Driver extends AbstractDriverMiddleware
{
    /** @internal This driver can be only instantiated by its middleware. */
    public function __construct(
        DriverInterface $driver,
        private readonly DoctrineLoggerInterface $logger,
    ) {
        parent::__construct($driver);
    }

    #[\Override]
    public function connect(
        #[\SensitiveParameter]
        array $params,
    ): Connection {
        $this->logger->log(
            DoctrineLoggerInterface::LOG_CONNECT,
            ['params' => $this->maskPassword($params)],
        );

        return new Connection(
            parent::connect($params),
            $this->logger,
        );
    }

    /**
     * @param array<string,mixed> $params Connection parameters
     *
     * @return array<string,mixed>
     */
    private function maskPassword(
        #[\SensitiveParameter]
        array $params,
    ): array {
        if (isset($params['password'])) {
            $params['password'] = '<redacted>';
        }

        return $params;
    }
}
