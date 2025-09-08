
# Doctrine DBAL Logger Middleware [![CI](https://github.com/Harmonic-Digital-Ltd/doctrine-dbal-logger/actions/workflows/php.yml/badge.svg)](https://github.com/Harmonic-Digital-Ltd/doctrine-dbal-logger/actions/workflows/php.yml)

A more flexible logging middleware for Doctrine DBAL that provides database operation logging with configurable log levels and messages.

## Installation

```bash
composer require harmonicdigital/doctrine-dbal-logger
```

## Basic Usage

The middleware can be used as a drop-in replacement for the built-in DBAL logging middleware.

```php
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use HarmonicDigital\DoctrineDbal\Logging\Middleware;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

// Create your PSR-3 logger
$logger = new Logger('doctrine');
$logger->pushHandler(new StreamHandler('doctrine.log', Logger::DEBUG));

// Create the middleware
$middleware = new Middleware($logger);

// Configure DBAL with the middleware
$configuration = new Configuration();
$configuration->setMiddlewares([$middleware]);

// Create your connection
$connection = DriverManager::getConnection([
    'driver' => 'pdo_sqlite',
    'path' => ':memory:',
], $configuration);
```

## Custom Log levels

Sometimes, you may want to log certain database operations at a different levels to the defaults. E.g. you may want connection to be a `debug` level, but rollback to to be a warning.

Instead of passing a `Logger` instance to the middleware, you can pass a `DoctrineLogger` instance, with customised log levels. Additionally, you may want to change the log message for a certain action.

You pass the overriding levels and messages to the `DoctrineLogger` constructor.

### Example

```php
use HarmonicDigital\DoctrineDbal\Logging\DoctrineLogger;
use HarmonicDigital\DoctrineDbal\Logging\DoctrineLoggerInterface;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;


// Create your PSR-3 logger
$logger = new Logger('doctrine');
$logger->pushHandler(new StreamHandler('doctrine.log', Logger::DEBUG));
$middleware = new Middleware(new DoctrineLogger(
    $logger,
    [
        // Overriding log levels
        DoctrineLoggerInterface::LOG_CONNECT => Logger::DEBUG,
        DoctrineLoggerInterface::LOG_ROLL_BACK => Logger::WARNING,
    ],
    [
        // Overriding log messages
        DoctrineLoggerInterface::LOG_ROLL_BACK => 'Rolling back transaction',
    ],
));
```