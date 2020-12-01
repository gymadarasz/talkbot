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

use Exception;
use RuntimeException;
use Throwable;

/**
 * Logger
 *
 * @category  PHP
 * @package   Madsoft\Library
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) All rights reserved.
 * @link      this
 */
class Logger
{
    const LOG_FILE = __DIR__ . '/log/app.log';
    const CH_ERROR = 'error';
    const CH_WARNING = 'warning';
    const CH_INFO = 'info';
    const CH_DEBUG = 'debug';
    const CH_TEST = 'test';
    const CH_FAIL = 'fail';
    const CH_EXCEPTION = 'exception';
    
    /**
     * Variable channels
     *
     * @var string[] $channels
     */
    protected array $channels = [];
    
    protected bool $collect = false;
    
    /**
     * Variable $collection
     *
     * @var string[]
     */
    protected array $collection = [];

    /**
     * Method setChannels
     *
     * @param string[] $channels channels
     *
     * @return self
     */
    public function setChannels(array $channels): self
    {
        $this->channels = $channels;
        return $this;
    }
    
    /**
     * Method getChannels
     *
     * @return string[]
     */
    public function getChannels(): array
    {
        return $this->channels;
    }
    
    /**
     * Method setCollect
     *
     * @param bool $collect collect
     *
     * @return self
     */
    public function setCollect(bool $collect): self
    {
        $this->collect = $collect;
        return $this;
    }
    
    /**
     * Method isCollect
     *
     * @return bool
     */
    public function isCollect(): bool
    {
        return $this->collect;
    }
    
    /**
     * Method getCollection
     *
     * @return string[]
     */
    public function getCollection(): array
    {
        return $this->collection;
    }
    
    /**
     * Method setCollection
     *
     * @param string[] $collection collection
     *
     * @return self
     */
    public function setCollection(array $collection = []): self
    {
        $this->collection = $collection;
        return $this;
    }
    
    /**
     * Method clearCollection
     *
     * @return self
     */
    public function clearCollection(): self
    {
        return $this->setCollection([]);
    }

    /**
     * Method error
     *
     * @param string $msg msg
     *
     * @return void
     */
    public function error(string $msg): void
    {
        $this->log(Logger::CH_ERROR, $msg);
    }

    /**
     * Method warning
     *
     * @param string $msg msg
     *
     * @return void
     */
    public function warning(string $msg): void
    {
        $this->log(Logger::CH_WARNING, $msg);
    }

    /**
     * Method info
     *
     * @param string $msg msg
     *
     * @return void
     */
    public function info(string $msg): void
    {
        $this->log(Logger::CH_INFO, $msg);
    }

    /**
     * Method debug
     *
     * @param string $msg msg
     *
     * @return void
     */
    public function debug(string $msg): void
    {
        $this->log(Logger::CH_DEBUG, $msg);
    }

    /**
     * Method test
     *
     * @param string $msg msg
     *
     * @return void
     */
    public function test(string $msg): void
    {
        $this->log(Logger::CH_TEST, $msg);
    }

    /**
     * Method fail
     *
     * @param string $msg msg
     *
     * @return void
     */
    public function fail(string $msg): void
    {
        $this->log(Logger::CH_FAIL, $msg);
    }
    
    /**
     * Method exception
     *
     * @param Exception $exception  exception
     * @param string    $infoPrefix infoPrefix
     *
     * @return void
     */
    public function exception(
        Exception $exception,
        string $infoPrefix = "Exception occured: "
    ): void {
        $this->log(
            Logger::CH_EXCEPTION,
            $infoPrefix . $this->exceptionToString($exception)
        );
    }
    
    /**
     * Method exceptionToString
     *
     * @param Throwable $exception exception
     *
     * @return string
     */
    public function exceptionToString(Throwable $exception): string
    {
        $class = get_class($exception);
        $code = $exception->getCode();
        $file = $exception->getFile();
        $line = $exception->getLine();
        $msg = $exception->getMessage();
        $trace = $exception->getTraceAsString();
        $prev =$exception->getPrevious();
        $string = "$class (code: $code) at $file:$line\n"
                . "Message: '$msg'\n"
                . "Trace:\n$trace";
        if ($prev) {
            $prevString = $this->exceptionToString($prev);
            $string .= "\nPrevious reason:\n$prevString";
        }
        return $string;
    }
    
    /**
     * Method doLog
     *
     * @param string $channel channel
     * @param string $msg     msg
     *
     * @return void
     * @throws RuntimeException
     */
    protected function log(string $channel, string $msg): void
    {
        if (!$this->channels || in_array($channel, $this->channels, true)) {
            $fullmsg = "[" . date("Y-m-d H:i:s") . "] [$channel] $msg";
            if ($this->collect) {
                $this->collection[] = $fullmsg;
                return;
            }
            if (false === file_put_contents(
                $this::LOG_FILE,
                "$fullmsg\n",
                FILE_APPEND
            )
            ) {
                throw new RuntimeException(
                    "Log file error, (" . $this::LOG_FILE .
                        ") message is not logged: $fullmsg"
                );
            }
        }
    }
}
