<?php

declare(strict_types=1);

namespace ApheleiaCli;

use InvalidArgumentException;

class Flag
{
    public const NAME_PATTERN = 'a-z\-_0-9';

    /**
     * @var string|null
     */
    protected $description;

    /**
     * @var string
     */
    protected $name;

    public function __construct(string $name)
    {
        if (! static::isValidName($name)) {
            throw new InvalidArgumentException(
                'Invalid flag name - must only contain lowercase letters, numbers, -, and _'
            );
        }

        $this->name = $name;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return array{type: 'flag', name: string, optional: bool, repeating: bool, description?: string}
     */
    public function getSynopsis(): array
    {
        $synopsis = [
            'type' => 'flag',
            'name' => $this->name,
            'optional' => true,
            'repeating' => false,
        ];

        if ($this->description) {
            $synopsis['description'] = $this->description;
        }

        return $synopsis;
    }

    public static function isValidName(string $name): bool
    {
        $pattern = sprintf('/[^%s]/', static::NAME_PATTERN);

        return preg_replace($pattern, '', $name) === $name;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }
}
