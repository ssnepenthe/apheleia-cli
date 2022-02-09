# toy-wp-cli

An alternate syntax for writing WP-CLI commands that doesn't rely on docblock command definitions. Loosely modeled after [symfony/console](https://github.com/symfony/console) and [mnapoli/silly](https://github.com/mnapoli/silly/).

## Usage

I think this is best explained through examples, so let's demonstrate some different ways we might implement the [`example hello` command from the WP-CLI handbook commands cookbook](https://make.wordpress.org/cli/handbook/guides/commands-cookbook/#annotating-with-phpdoc).

The intended primary approach is to write self contained command classes:

```php
class HelloCommand extends ToyWpCli\Command
{
	public function configure()
	{
		$this->setName('hello')
			->setDescription('Prints a greeting.')
			->addArgument(
				(new ToyWpCli\Argument('name'))
					->setDescription('The name of the person to greet.')
			)
			->addOption(
				(new ToyWpCli\Option('type'))
					->setDescription('Whether or not to greet the person with success or error.')
					->setDefault('success')
					->setOptions('success', 'error')
			)
			->setUsage("## EXAMPLES\n\n\twp example hello newman")
			->setWhen('after_wp_load');
	}

	public function handle($args, $assoc_args)
	{
		list($name) = $args;

		$type = $assoc_args['type'];
		WP_CLI::$type("Hello, $name!");
	}
}

$registry = new ToyWpCli\CommandRegistry();

$registry->namespace('example', 'Implements example command.', function($scopedRegistry) {
	$scopedRegistry->add(new HelloCommand());
});

$registry->initialize();
```

Alternatively, you might want to define a command on the fly:

```php
$registry = new ToyWpCli\CommandRegistry();

$registry->namespace('example', 'Implements example command.', function($scopedRegistry) {
	$command = (new ToyWpCli\Command())
		->setName('hello')
		->setDescription('Prints a greeting.')
		->addArgument(
			(new ToyWpCli\Argument('name'))
				->setDescription('The name of the person to greet.')
		)
		->addOption(
			(new ToyWpCli\Option('type'))
				->setDescription('Whether or not to greet the person with success or error.')
				->setDefault('success')
				->setOptions('success', 'error')
		)
		->setUsage("## EXAMPLES\n\n\twp example hello newman")
		->setWhen('after_wp_load')
		->setHandler(function($args, $assoc_args) {
			list($name) = $args;

			$type = $assoc_args['type'];
			WP_CLI::$type("Hello, $name!");
		});

	$scopedRegistry->add($command);
});

$registry->initialize();
```

You can also set a custom handler invocation strategy:

```php
class HelloCommand extends ToyWpCli\Command
{
	public function configure()
	{
		$this->setName('hello')
			->setDescription('Prints a greeting.')
			->addArgument(
				(new ToyWpCli\Argument('name'))
					->setDescription('The name of the person to greet.')
			)
			->addOption(
				(new ToyWpCli\Option('type'))
					->setDescription('Whether or not to greet the person with success or error.')
					->setDefault('success')
					->setOptions('success', 'error')
			)
			->setUsage("## EXAMPLES\n\n\twp example hello newman")
			->setWhen('after_wp_load');
	}

  // No more $args and $assoc_args - ask for command params by name.
	public function handle($name, $type)
	{
		WP_CLI::$type("Hello, $name!");
	}
}

// InvokerBackedInvocationStrategy calls command handlers using the php-di/invoker package.
$registry = new ToyWpCli\CommandRegistry(new ToyWpCli\InvokerBackedInvocationStrategy());

$registry->namespace('example', 'Implements example command.', function($scopedRegistry) {
	$scopedRegistry->add(new HelloCommand());
});

$registry->initialize();
```

There is also an alternate syntax:

```php
$registry = new ToyWpCli\CommandRegistry();

$registry->namespace('example', 'Implements example command.', function($scopedRegistry) {
	$scopedRegistry->command('hello <name> [--type=<type>]', function($args, $assoc_args) {
		list($name) = $args;

		$type = $assoc_args['type'];
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

$registry->initialize();
```

In my opinion, this syntax shines when used for simpler command definitions along with php-di/invoker:

```php
$registry = new ToyWpCli\CommandRegistry(new ToyWpCli\InvokerBackedInvocationStrategy());

$registry->command('example hello <name> [--type=<type>]', function($name, $type) {
	WP_CLI::$type("Hello, $name!");
})->defaults([
  '--type' => 'success',
])->options([
  '--type' => ['success', 'error'],
]);

$registry->initialize();
```
