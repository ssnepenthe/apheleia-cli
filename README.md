# apheleia-cli

Apheleia CLI provides an alternate approach to writing WP-CLI commands. It eliminates the need for docblock command definitions and should allow you to take full advantage of the autocomplete features in your favorite editor.

The syntax for Apheleia commands is loosely modeled after the [symfony/console](https://github.com/symfony/console) package.

## Warning

This package is currently in development and is subject to breaking changes without notice until v1.0 has been tagged.

It is one in a series of [WordPress toys](https://github.com/ssnepenthe?tab=repositories&q=topic%3Atoy+topic%3Awordpress&type=&language=&sort=) I have been working on with the intention of exploring ways to modernize the feel of working with WordPress.

As the label suggests, it should be treated as a toy.

## Installation

```sh
composer require ssnepenthe/apheleia-cli
```

## Usage

I think this is best explained through examples, so let's demonstrate some different ways we might implement the [`example hello` command from the WP-CLI handbook commands cookbook](https://make.wordpress.org/cli/handbook/guides/commands-cookbook/#annotating-with-phpdoc).

The preferred approach is to create self-contained command classes.

Extend the `Command` class using the `configure` method to define the command signature and the `handle` method to define the command logic to be executed when the command is called:

```php
use ApheleiaCli\Argument;
use ApheleiaCli\Command;
use ApheleiaCli\Input\InputInterface;
use ApheleiaCli\Option;
use ApheleiaCli\Output\ConsoleOutputInterface;
use ApheleiaCli\Output\WpCliLoggerStandIn;
use ApheleiaCli\Status;

class HelloCommand extends Command
{
    public function configure(): void
    {
        $this->setName('example hello')
            ->setDescription('Prints a greeting.')
            ->addArgument(
                (new Argument('name'))
                    ->setDescription('The name of the person to greet.')
            )
            ->addOption(
                (new Option('type'))
                    ->setDescription('Whether or not to greet the person with success or error.')
                    ->setDefault('success')
                    ->setOptions('success', 'error')
            )
            ->setUsage("## EXAMPLES\n\n\twp example hello newman")
            ->setWhen('after_wp_load');
    }

    public function handle(InputInterface $input, ConsoleOutputInterface $output)
    {
        $logger = new WpCliLoggerStandIn($output);

        $name = $input->getArgument('name');
        $type = $input->getOption('type');

        $logger->{$type}("Hello, {$name}");

        if ('error' === $type) {
            return Status::FAILURE;
        }

        return Status::SUCCESS;
    }
}
```

There are a couple of things you should notice in the command handler:

1. Instead of an `$args` array and `$assoc_args` array, handlers receive an input object and output object.
2. Arguments are retrieved by name rather than position.
3. We use the `WpCliLoggerStandIn` class to print output rather than the various output methods on the `WP_CLI` class. This makes it easier to properly unit test our command since we can customize the output streams that are written to by `$output`.
4. The error method on our logger stand-in does not automatically halt execution like `WP_CLI::error()`.
5. Handlers should (optionally) return an integer (e.g. `Status::FAILURE`, `Status::SUCCESS`) to set the exit status code.

Next, we register our command using the `CommandRegistry`:

```php
use ApheleiaCli\CommandRegistry;

$registry = new CommandRegistry();

$registry->add(new HelloCommand());

$registry->initialize();
```

There is a significant difference between the command we have created and the original version: WP-CLI does not have any description text to display for the parent `example` command when you run `wp help example`.

There are a couple of different approaches we can take to fix this.

The first is to register a dedicated namespace alongside our `HelloCommand`:

```php
$registry->namespace('example', 'Implements example command.');
$registry->add(new HelloCommand());
```

The second is to take advantage of command groups.

First we need to remove the parent command portion of our command name:

```php
class HelloCommand extends Command
{
    public function configure(): void
    {
        $this->setName('hello')
            // etc...
            ;
    }

    // ...
}
```

And then we use the `group` method on our registry. The callback provided to the `group` method will receive a registry instance that has been scoped such that any commands added within the callback will automatically be registered as children of the `example` command:

```php
$registry->group('example', 'Implements example command.', function (CommandRegistry $registry) {
    $registry->add(new HelloCommand());
});
```

It is also possible to define a command without extending the `Command` class:

```php
$registry->add(
    (new Command())
        ->setName('hello')
        ->setDescription('Prints a greeting.')
        ->addArgument(
            (new Argument('name'))
                ->setDescription('The name of the person to greet.')
        )
        ->addOption(
            (new Option('type'))
                ->setDescription('Whether or not to greet the person with success or error.')
                ->setDefault('success')
                ->setOptions('success', 'error')
        )
        ->setUsage("## EXAMPLES\n\n\twp example hello newman")
        ->setWhen('after_wp_load')
        ->setHandler(function (InputInterface $input, ConsoleOutputInterface $output) {
            $logger = new WpCliLoggerStandIn($output);

            $name = $input->getArgument('name');
            $type = $input->getOption('type');

            $logger->{$type}("Hello, {$name}");

            if ('error' === $type) {
                return Status::FAILURE;
            }

            return Status::SUCCESS;
        })
);
```

## Advanced Usage - Handler Invoker

By default, command handlers receive an `InputInterface` object and a `ConsoleOutputInterface` object. Handler signatures can be modified, however, by overriding the command `handlerInvokerClass` property.

This package ships with two alternative handler invokers: the `LegacyHandlerInvoker` and the `PhpDiHandlerInvoker`.

The `LegacyHandlerInvoker` class can be used to mimic standard WP-CLI commands - handlers receive an `$args` array of arguments and an `$assocArgs` array of options.

```php
use ApheleiaCli\Invoker\LegacyHandlerInvoker;
use WP_CLI;

class HelloCommand extends Command
{
    protected $handlerInvokerClass = LegacyHandlerInvoker::class;

    public function handle(array $args, array $assocArgs)
    {
        [$name] = $args;
        $type = $assocArgs['type'];

        WP_CLI::{$type}("Hello, {$name}");
    }
}
```

The `PhpDiHandlerInvoker` class can be used to call command handlers with the php-di/invoker package.

Before it can be used, you must separately install the `php-di/invoker` package:

```sh
composer require php-di/invoker
```

Once configured, handlers can now ask for parameters by name or type:

```php
use ApheleiaCli\Invoker\PhpDiHandlerInvoker;

class HelloCommand extends Command
{
    protected $handlerInvokerClass = PhpDiHandlerInvoker::class;

    public function handle($name, $type, WpCliLoggerStandIn $logger)
    {
        $logger->{$type}("Hello, {$name}!");
    }
}
```
