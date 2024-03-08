<?php

declare(strict_types=1);
/*
 * Plugin Name: Apheleia CLI Test Plugin
 * Plugin URI:
 * Description:
 * Version: 0.1.0
 * Author: ssnepenthe
 * Author URI: https://github.com/ssnepenthe
 * License: MIT
 */

use ApheleiaCli\Argument;
use ApheleiaCli\Command;
use ApheleiaCli\CommandRegistry;
use ApheleiaCli\Flag;
use ApheleiaCli\Invoker\PhpDiHandlerInvoker;
use ApheleiaCli\Option;

if (! (defined('WP_CLI') && \WP_CLI)) {
    return;
}

$registry = new CommandRegistry();

$registry->group('apheleia', 'Apheleia CLI Test Plugin', function (CommandRegistry $registry) {
    $registry->add(
        (new Command())
            ->setName('inline')
            ->setDescription('Description for inline command')
            ->setUsage('Just do it')
            ->setWhen('after_wp_load')
            ->addArgument(
                (new Argument('arg'))
                    ->setDescription('Description for argument "arg"')
                    ->setOptions('arg-default', 'arg-override')
            )
            ->addFlag(
                (new Flag('flag'))
                    ->setDescription('Description for flag "flag"')
            )
            ->addOption(
                (new Option('option'))
                    ->setDefault('opt-default')
                    ->setDescription('Description for option "option"')
                    ->setOptional(false)
                    ->setOptions('opt-default', 'opt-override')
            )
            ->setBeforeInvokeCallback(function () {
                WP_CLI::log('Before inline');
            })
            ->setAfterInvokeCallback(function () {
                WP_CLI::log('After inline');
            })
            ->setHandler(function ($args, $assocArgs) {
                WP_CLI::log('Hi from the inline handler');
                var_dump('ARGS', $args, 'ASSOC ARGS', $assocArgs);
            })
    );

    $registry->add(
        new class () extends Command {
            public function afterInvoke()
            {
                WP_CLI::log('After class');
            }

            public function beforeInvoke()
            {
                WP_CLI::log('Before class');
            }

            public function configure(): void
            {
                $this
                    ->setName('class')
                    ->setDescription('Description for class command')
                    ->setUsage('Also just do it')
                    ->setWhen('after_wp_load')
                    ->addArgument(
                        (new Argument('arg'))
                            ->setOptional(true)
                            ->setRepeating(true)
                    )
                    ->addOption(
                        // Should be optional by default.
                        (new Option('option'))
                    );
            }

            public function handle($args, $assocArgs)
            {
                WP_CLI::log('Hi from the class handler');
                var_dump('ARGS', $args, 'ASSOC ARGS', $assocArgs);
            }
        }
    );

    $registry->add(
        (new Command())
            ->setName('invoker')
            ->addArgument(new Argument('one'))
            ->addArgument(new Argument('two'))
            ->setHandlerInvokerClass(PhpDiHandlerInvoker::class)
            ->setHandler(function ($one, $two) {
                WP_CLI::log("ONE: {$one}");
                WP_CLI::log("TWO: {$two}");
            })
    );
});

$registry->initialize();
