<?php declare(strict_types = 1);

/**
 * PHP version 7.4
 *
 * @category  PHP
 * @package   Madsoft\Library
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) All rights reserved.
 * @link      this
 */

namespace Madsoft\Library;

use ErrorException;
use Throwable;

/**
 * ErrorHandler
 *
 * @category  PHP
 * @package   Madsoft\Library
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) All rights reserved.
 * @link      this
 */
class ErrorHandler
{
    protected Logger $logger;
    
    /**
     * Method __construct
     *
     * @param Logger $logger logger
     */
    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
        
        ini_set('display_errors', '1');
        ini_set('display_startup_errors', '1');
        error_reporting(E_ALL | E_STRICT);
        
        set_error_handler([$this, 'handleError']);
        set_exception_handler([$this, 'handleException']);
    }
    
    /**
     * Method handleException
     *
     * @param Throwable $errexc errexc
     *
     * @return void
     * @throws ErrorException
     */
    public function handleException(Throwable $errexc): void
    {
        $this->logger->exception($errexc);
        $this->logger->error(
            "Unhandled '" . get_class($errexc). ", message: "
                . $errexc->getMessage() . "' (code: " . $errexc->getCode()
            . ") see more in log at the previous exception."
        );
    }
    
    /**
     * Method handleError
     *
     * @param int     $errno      errno
     * @param string  $errstr     errstr
     * @param string  $errfile    errfile
     * @param int     $errline    errline
     * @param mixed[] $errcontext errcontext
     *
     * @return bool
     * @throws ErrorException
     */
    public function handleError(
        int $errno,
        string $errstr,
        string $errfile,
        int $errline,
        array $errcontext
    ): bool {
        $this->logger->error(
            "$errstr ($errno) at $errfile:$errline\nContext:\n"
            . $this->dumpErrorContext($errcontext)
        );
        throw new ErrorException(
            "An error happened with the following message: "
                . "'$errstr' (errno: $errno) "
                . "see more about the context in log at the previous error.",
            $errno
        );
    }
    
    /**
     * Method dumpErrorContext
     *
     * @param array<string|mixed> $errcontext errcontext
     * @param string              $output     output
     * @param string              $prefix     prefix
     * @param int                 $deep       deep
     *
     * @return string
     */
    protected function dumpErrorContext(
        array $errcontext,
        string $output = '',
        string $prefix = '',
        int $deep = 1
    ): string {
        if ($deep <= 0) {
            return "[too deep]\n";
        }
        foreach ($errcontext as $key => $value) {
            $output = $this->dumpOutputConcat(
                $output,
                $value,
                $prefix,
                $key,
                $deep
            );
        }
        return $output;
    }
    
    /**
     * Method dumpOutputConcat
     *
     * @param string     $output output
     * @param mixed      $value  value
     * @param string     $prefix prefix
     * @param int|string $key    key
     * @param int        $deep   deep
     *
     * @return string
     */
    protected function dumpOutputConcat(
        string $output,
        $value,
        string $prefix,
        $key,
        int $deep
    ): string {
        if ($this->isStringKind($value)) {
            $output .= ($prefix ? "$prefix." : '') . "$key: $value\n";
            return $output;
        }
        if (is_array($value) || is_object($value)) {
            $output .= ($prefix ? "$prefix." : '')
                        . "$key: " . $this->dumpErrorContext(
                            (array)$value,
                            $output,
                            ($prefix ? "$prefix." : '') . $key,
                            $deep-1
                        );
            return $output;
        }
        $output .= ($prefix ? "$prefix." : '')
                    . "$key: (" . gettype($value) . ")";
            
        return $output;
    }
    
    /**
     * Method isStringKind
     *
     * @param mixed $value value
     *
     * @return bool
     */
    protected function isStringKind($value): bool
    {
        return is_scalar($value) || is_null($value)
            || (is_object($value) && method_exists($value, '__toString'));
    }
}
