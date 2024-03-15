<?php

declare(strict_types=1);

namespace ApheleiaCli\Output;

use ApheleiaCli\WpCli\WpCliConfig;
use ApheleiaCli\WpCli\WpCliConfigInterface;
use cli\Colors;
use InvalidArgumentException;
use Throwable;
use WP_Error;

// @todo LoggerInterface?
class WpCliLoggerStandIn
{
    /**
     * @var WpCliConfigInterface
     */
    protected $config;

    /**
     * @var ConsoleOutputInterface
     */
    protected $output;

    public function __construct(ConsoleOutputInterface $output, ?WpCliConfigInterface $config = null)
    {
        $this->output = $output;

        $this->config = $config instanceof WpCliConfigInterface ? $config : new WpCliConfig();
    }

    /**
     * @param string|WP_Error|Throwable $message
     */
    public function debug($message, ?string $group = null): void
    {
        static $startTime = null;

        if (null === $startTime) {
            $startTime = defined('WP_CLI_START_MICROTIME') ? WP_CLI_START_MICROTIME : microtime(true);
        }

        if (! $this->config->isDebug()) {
            return;
        }

        if (is_string($this->config->debugGroup()) && $group !== $this->config->debugGroup()) {
            return;
        }

        $label = 'Debug';

        if (is_string($group) && ! is_string($this->config->debugGroup())) {
            $label .= " ({$group})";
        }

        $time = round(microtime(true) - $startTime, 3);

        $this->writeToErrorOutputWithLabel($this->stringifyErrorMessage($message) . " ({$time}s)", $label, '%B');
    }

    /**
     * @param string|WP_Error|Throwable $message
     */
    public function error($message): void
    {
        $this->writeToErrorOutputWithLabel($this->stringifyErrorMessage($message), 'Error', '%R');
    }

    /**
     * @param array<int, string|WP_Error|Throwable> $messages
     */
    public function errorMultiLine(array $messages): void
    {
        if ([] === $messages) {
            return;
        }

        // None of the WP-CLI logger error_multi_line() methods handle WP_Error instances or Throwables...
        // But WP-CLI::error_multi_line() does so that is the behavior we will mimic here.
        $messages = array_map(
            fn ($message) => str_replace("\t", '    ', $this->stringifyErrorMessage($message)),
            $messages
        );
        $longest = max(array_map('strlen', $messages));
        $emptyLine = $this->colorize(str_repeat(' ', $longest + 2), '%w%1');

        $output = $this->output->getErrorOutput();

        $output->write("\n\t{$emptyLine}\n");

        foreach ($messages as $message) {
            $message = $this->colorize(' ' . str_pad($message, $longest) . ' ', '%w%1');
            $output->write("\t{$message}\n");
        }

        $output->write("\t{$emptyLine}\n\n");
    }

    public function info(string $message): void
    {
        $this->output->writeln($message);
    }

    public function success(string $message): void
    {
        $this->writeToOutputWithLabel($message, 'Success', '%G');
    }

    /**
     * @param string|WP_Error|Throwable $message
     */
    public function warning($message): void
    {
        $this->writeToErrorOutputWithLabel($this->stringifyErrorMessage($message), 'Warning', '%C');
    }

    private function colorize(string $string, string $color): string
    {
        if (! class_exists(Colors::class)) {
            return $string;
        }

        return Colors::colorize("{$color}{$string}%n", $this->config->inColor());
    }

    /**
     * @param mixed $message
     */
    private function stringifyErrorMessage($message): string
    {
        if (is_string($message)) {
            return $message;
        }

        $stringify = function ($data) {
            if (is_array($data) || is_object($data)) {
                return json_encode($data);
            }

            return "\"{$data}\"";
        };

        if ($message instanceof WP_Error) {
            foreach ($message->get_error_messages() as $errorMessage) {
                if ($message->get_error_data()) {
                    return $errorMessage . ' ' . $stringify($message->get_error_data());
                }

                return $errorMessage;
            }
        }

        if ($message instanceof Throwable) {
            return get_class($message) . ': ' . $message->getMessage();
        }

        throw new InvalidArgumentException(sprintf("Cannot stringify error type: '%s'", gettype($message)));
    }

    private function writeToErrorOutputWithLabel(string $message, string $label, string $color): void
    {
        $label = $this->colorize("{$label}:", $color);

        $this->output->getErrorOutput()->writeln("{$label} {$message}");
    }

    private function writeToOutputWithLabel(string $message, string $label, string $color): void
    {
        $label = $this->colorize("{$label}:", $color);

        $this->output->writeln("{$label} {$message}");
    }
}
