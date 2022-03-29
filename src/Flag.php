<?php

declare(strict_types=1);

namespace ToyWpCli;

class Flag
{
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

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }
}
