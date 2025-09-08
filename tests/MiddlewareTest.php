<?php

declare(strict_types=1);

namespace HarmonicDigital\DoctrineDbal\Logging\Tests;

use Doctrine\DBAL\Driver as DbalDriver;
use Doctrine\DBAL\Driver\Connection as DbalConnection;
use Doctrine\DBAL\ParameterType;
use HarmonicDigital\DoctrineDbal\Logging\Connection;
use HarmonicDigital\DoctrineDbal\Logging\DoctrineLogger;
use HarmonicDigital\DoctrineDbal\Logging\DoctrineLoggerInterface;
use HarmonicDigital\DoctrineDbal\Logging\Driver;
use HarmonicDigital\DoctrineDbal\Logging\Middleware;
use HarmonicDigital\DoctrineDbal\Logging\Statement;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;
use Psr\Log\Test\TestLogger;

/**
 * @internal
 */
#[CoversClass(Middleware::class)]
#[CoversClass(Connection::class)]
#[CoversClass(Statement::class)]
#[CoversClass(Driver::class)]
#[CoversClass(DoctrineLogger::class)]
class MiddlewareTest extends TestCase
{
    private DbalDriver $driver;
    private Middleware $middleware;
    private TestLogger $logger;

    public function setUp(): void
    {
        $connection = $this->createMock(DbalConnection::class);

        $this->driver = $this->createMock(DbalDriver::class);
        $this->driver->method('connect')
            ->willReturn($connection)
        ;

        $this->logger = new TestLogger();

        $this->middleware = new Middleware($this->logger);
    }

    public function testConnectAndDisconnect(): void
    {
        $this->middleware->wrap($this->driver)->connect([
            'user' => 'admin',
            'password' => 'Passw0rd!',
        ]);

        self::assertTrue($this->logger->hasInfo([
            'message' => 'Connecting with parameters {params}',
            'context' => [
                'params' => [
                    'user' => 'admin',
                    'password' => '<redacted>',
                ],
            ],
        ]));
    }

    public function testQuery(): void
    {
        $connection = $this->middleware->wrap($this->driver)->connect([]);
        $connection->query('SELECT 1');

        self::assertTrue($this->logger->hasDebug([
            'message' => 'Executing query: {sql}',
            'context' => ['sql' => 'SELECT 1'],
        ]));
    }

    public function testExec(): void
    {
        $connection = $this->middleware->wrap($this->driver)->connect([]);
        $connection->exec('DROP DATABASE doctrine');

        self::assertTrue($this->logger->hasDebug([
            'message' => 'Executing statement: {sql}',
            'context' => ['sql' => 'DROP DATABASE doctrine'],
        ]));
    }

    public function testBeginCommitRollback(): void
    {
        $connection = $this->middleware->wrap($this->driver)->connect([]);
        $connection->beginTransaction();
        $connection->commit();
        $connection->rollBack();

        self::assertTrue($this->logger->hasDebug('Beginning transaction'));
        self::assertTrue($this->logger->hasDebug('Committing transaction'));
        self::assertTrue($this->logger->hasDebug('Rolling back transaction'));
    }

    public function testBeginCommitRollbackWithOverrides(): void
    {
        $middleware = new Middleware(new DoctrineLogger($this->logger, [
            DoctrineLoggerInterface::LOG_ROLL_BACK => LogLevel::WARNING,
        ], [
            DoctrineLoggerInterface::LOG_COMMIT => 'Custom message',
        ]));
        $connection = $middleware->wrap($this->driver)->connect([]);
        $connection->beginTransaction();
        $connection->commit();
        $connection->rollBack();

        self::assertTrue($this->logger->hasDebug('Beginning transaction'));
        self::assertTrue($this->logger->hasDebug('Custom message'));
        self::assertTrue($this->logger->hasWarning('Rolling back transaction'));
    }

    public function testExecuteStatementWithParameters(): void
    {
        $connection = $this->middleware->wrap($this->driver)->connect([]);
        $statement = $connection->prepare('SELECT ?, ?');
        $statement->bindValue(1, 42, ParameterType::INTEGER);

        $statement->execute();

        self::assertTrue($this->logger->hasDebug([
            'message' => 'Executing statement: {sql} (parameters: {params}, types: {types})',
            'context' => [
                'sql' => 'SELECT ?, ?',
                'params' => [1 => 42],
                'types' => [1 => ParameterType::INTEGER],
            ],
        ]));
    }

    public function testExecuteStatementWithNamedParameters(): void
    {
        $connection = $this->middleware->wrap($this->driver)->connect([]);
        $statement = $connection->prepare('SELECT :value');
        $statement->bindValue('value', 'Test', ParameterType::STRING);

        $statement->execute();

        self::assertTrue($this->logger->hasDebug([
            'message' => 'Executing statement: {sql} (parameters: {params}, types: {types})',
            'context' => [
                'sql' => 'SELECT :value',
                'params' => ['value' => 'Test'],
                'types' => ['value' => ParameterType::STRING],
            ],
        ]));
    }
}
