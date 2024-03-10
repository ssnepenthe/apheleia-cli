<?php

declare(strict_types=1);

namespace ApheleiaCli\Output;

use InvalidArgumentException;

class StreamOutput implements OutputInterface
{
    protected $debug;

    protected $quiet;

    protected $stream;

    public function __construct($stream, bool $quiet = false)
    {
        if (! is_resource($stream) || 'stream' !== get_resource_type($stream)) {
            throw new InvalidArgumentException('First argument to StreamOutput::__construct() must be a stream');
        }

        $this->stream = $stream;
        $this->quiet = $quiet;
    }

    public function getStream()
    {
        return $this->stream;
    }

    public function write($message, bool $newline = false): void
    {
        if ($this->quiet) {
            return;
        }

        if ($newline) {
            $message .= PHP_EOL;
        }

        fwrite($this->stream, $message);
        fflush($this->stream);
    }

    public function writeln($message): void
    {
        $this->write($message, true);
    }
}
