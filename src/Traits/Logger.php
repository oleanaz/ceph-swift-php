<?php
namespace Liushuangxi\Ceph\Traits;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

/**
 * Trait for logger
 */
trait Logger
{
    /**
     * Logger object
     *
     * @var \Psr\Log\LoggerInterface|null
     */
    protected $logger;

    /**
     * Log error message
     *
     * @param string $message
     * @param array  $context
     */
    protected function error(string $message, array $context) {
        $this->log(
            LogLevel::ERROR,
            $message,
            $this->extractErrorContext($context)
        );
    }

    /**
     * Log message
     *
     * @param string $level
     * @param string $message
     * @param array  $context
     */
    protected function log(string $level, string $message, array $context = [])
    {
        try {
            if ($this->logger instanceof LoggerInterface && !empty($message)) {
                $this->logger->log($level, $message, $context);
            }
        } catch (\Exception $e) {
            // Если логгер выдал ошибку и не смог записать лог - выкинем в виде warning
            trigger_error($e->getMessage(), E_USER_WARNING);
        }
    }

    /**
     * Extract error data from context
     *
     * @param array $context
     * @return array
     */
    protected function extractErrorContext(array $context): array
    {
        $exception = $context['exception'] ?? null;

        if ($exception instanceof \Throwable) {
            $result = [
                'message' => $exception->getMessage(),
                'code'    => $exception->getCode(),
                'file'    => $exception->getFile(),
                'line'    => $exception->getLine(),
                'trace'   => $exception->getTraceAsString(),
            ];

            $context = array_merge($context, $result);

            unset($context['exception']);
        }

        return $context;
    }
}
