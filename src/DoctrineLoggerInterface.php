<?php

declare(strict_types=1);

namespace HarmonicDigital\DoctrineDbal\Logging;

interface DoctrineLoggerInterface
{
    public const LOG_BEGIN_TRANSACTION = 'begin_transaction';
    public const LOG_COMMIT = 'commit_transaction';
    public const LOG_CONNECT = 'connect';
    public const LOG_DISCONNECT = 'disconnect';
    public const LOG_EXECUTE = 'execute';
    public const LOG_QUERY = 'query';
    public const LOG_ROLL_BACK = 'roll_back_transaction';
    public const LOG_STATEMENT = 'statement';

    /** @param self::LOG_* $log */
    public function log(string $log, array $context = []): void;
}
