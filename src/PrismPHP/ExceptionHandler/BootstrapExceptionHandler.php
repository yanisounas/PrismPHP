<?php
declare(strict_types=1);

namespace PrismPHP\ExceptionHandler;

use Psr\Log\LoggerInterface;
use Throwable;

class BootstrapExceptionHandler
{
    public function __construct(private readonly LoggerInterface $logger, private readonly bool $debug = false) {}

    public function register(): void
    {
        set_exception_handler([$this, 'handle']);
        set_error_handler([$this, 'handleError']);
        register_shutdown_function([$this, 'shutdown']);
    }

    public function handle(Throwable $e): void
    {
        $debug = isset($_ENV['APP_DEBUG']) ?
            filter_var($_ENV['APP_DEBUG'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? $this->debug:
            $this->debug;

        $this->logger->error($e->getMessage(), ['exception' => $e]);

        $output = $debug ? $this->_detailedError($e) : $this->_genericError();
        $this->_output($output);
    }

    public function handleError(int $errno, string $errStr, string $errFile, int $errLine): bool
    {
        $this->handle(new \ErrorException($errStr, 0, $errno, $errFile, $errLine));
        return true;
    }

    public function shutdown(): void
    {
        if (($error = error_get_last()) === null) return;

        $this->handle(new \ErrorException(
            $error['message'],
            0,
            $error['type'],
            $error['file'],
            $error['line']
        ));
    }

    private function _output(string $message): void
    {
        if (php_sapi_name() === 'cli')
        {
            fwrite(STDERR, $message);
        }
        else
        {
            http_response_code(500);
            header('Content-Type: text/plain;charset=utf-8');
            echo nl2br($message);
        }
    }

    private function _detailedError(Throwable $e): string
    {
        return sprintf(
            "Exception: %s\nMessage: %s\nFile: %s\nLine: %s\nTrace:\n%s\n",
            get_class($e),
            $e->getMessage(),
            $e->getFile(),
            $e->getLine(),
            $e->getTraceAsString()
        );
    }

    private function _genericError(): string
    {
        return "An error occurred. Please try again later.";
    }
}