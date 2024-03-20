<?php

declare(strict_types=1);

namespace ApheleiaCli\Output;

class ConsoleOutput extends StreamOutput implements ConsoleOutputInterface
{
    /**
     * @var StreamOutput
     */
    protected $stderr;

    public function __construct(bool $quiet = false)
    {
        parent::__construct($this->outputStream(), $quiet);

        $this->stderr = new StreamOutput($this->errorStream());
    }

    public function getErrorOutput(): StreamOutput
    {
        return $this->stderr;
    }

    /**
     * @return resource
     */
    protected function errorStream()
    {
        return defined('STDERR') ? STDERR : (@fopen('php://stderr', 'w') ?: fopen('php://output', 'w'));
    }

    /**
     * @return resource
     */
    protected function outputStream()
    {
        return defined('STDOUT') ? STDOUT : (@fopen('php://stdout', 'w') ?: fopen('php://output', 'w'));
    }
}
