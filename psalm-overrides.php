<?php

class WP_CLI
{
    /**
	 * @param string $command
	 * @param callable|class-string $class
	 * @param array{before_invoke?: callable, after_invoke?: callable, shortdesc?: string, longdesc?: string, synopsis?: string, when?: string, is_deferred?: bool} $args
	 * @return bool
	 */
	public static function add_command( string $command, $class, array $args = [] )
    {
    }

    /**
     * @see https://github.com/wp-cli/wp-cli/commit/5297dff3cc6a9b34423e7a670c908cb06fd6f746
     *
     * This needs to be stubbed until a new WP-CLI release is tagged and php-stubs gets updated.
     *
     * @param integer $return_code
     * @return never
     */
    public static function halt( $return_code )
    {
    }
}
