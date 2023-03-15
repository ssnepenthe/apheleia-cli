# apheleia-cli

Apheleia CLI provides an alternate approach to writing WP-CLI commands. It eliminates the need for docblock command definitions and should allow you to take full advantage of the autocomplete features in your favorite editor.

The syntax for Apheleia commands is loosely modeled after both [symfony/console](https://github.com/symfony/console) and [mnapoli/silly](https://github.com/mnapoli/silly/).

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
use ApheleiaCli\Option;
use WP_CLI;

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

    public function handle($args, $assocArgs)
    {
        [$name] = $args;

        $type = $assocArgs['type'];
        WP_CLI::$type("Hello, $name!");
    }
}
```

Commands are registered using the `CommandRegistry`.

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
        ->setHandler(function ($args, $assocArgs) {
            [$name] = $args;

            $type = $assocArgs['type'];
            WP_CLI::$type("Hello, $name!");
        })
);
```

Alternatively, commands can be defined using plain strings:

```php
$registry->command('example hello <name> [--type=<type>]', function ($args, $assocArgs) {
    [$name] = $args;
    $type = $assocArgs['type'] ?? 'success';

    if (! in_array($type, ['success', 'error'], true)) {
        $type = 'success';
    }

    WP_CLI::$type("Hello, $name!");
});
```

The above is a quick-and-dirty command definition. It is missing some important info such as descriptions, defaults, etc. A more thorough example might look like this:

```php
$registry->group('example', 'Implements example command.', function (CommandRegistry $registry) {
    $registry->command('hello <name> [--type=<type>]', function ($args, $assocArgs) {
        [$name] = $args;
        $type = $assocArgs['type'];

        WP_CLI::$type("Hello, $name!");
    })->descriptions('Prints a greeting.', [
        'name' => 'The name of the person to greet.',
        '--type' => 'Whether or not to greet the person with success or error.',
    ])->defaults([
        '--type' => 'success',
    ])->options([
        '--type' => ['success', 'error'],
    ])->usage(
        "## EXAMPLES\n\n\twp example hello newman"
    )->when('after_wp_load');
});
```

## Advanced Usage

By default, command handlers should be written more-or-less the same as they would if you were working directly with WP-CLI. That is to say they should always expect to receive a list of command arguments as the first parameter and an associative array of command options as the second:

```php
$command->setHandler(function (array $args, array $assocArgs) {
    // ...
});
```

However, commands can modify handler signatures by overriding their requiredInvocationStrategy property.

This package only ships with one alternative invocation strategy: the `InvokerBackedInvocationStrategy`. It uses the [`php-di/invoker`](https://github.com/php-di/invoker) package to call command handlers.

Before it can be used, you must install `php-di/invoker`:

```sh
composer require php-di/invoker
```

Then set the invocation strategy on your command (or a base command from which all of your commands extend):

```php
use ApheleiaCli\InvokerBackedInvocationStrategy;

class HelloCommand extends Command
{
    protected $requiredInvocationStrategy = InvokerBackedInvocationStrategy::class;

    // ...
}
```

With this in place, command handlers can now ask for command parameters by name:

```php
class HelloCommand extends Command
{
    // ...

    public function handle($name, $type)
    {
        WP_CLI::$type("Hello, $name!");
    }
}
```

You can take advantage of this to streamline your command definitions:

```php
$registry->command('example hello <name> [--type=<type>]', function ($name, $type = 'success') {
    if (! in_array($type, ['success', 'error'], true)) {
        $type = 'success';
    }

    WP_CLI::$type("Hello, $name!");
})->strategy(InvokerBackedInvocationStrategy::class);
```
