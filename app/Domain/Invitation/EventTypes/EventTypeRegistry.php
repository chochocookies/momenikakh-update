<?php

declare(strict_types=1);

namespace App\Domain\Invitation\EventTypes;

use App\Domain\Invitation\Enums\EventType;
use LogicException;

final class EventTypeRegistry
{
    /** @var array<string,EventTypeDefinition> */
    private array $definitions = [];

    /** @param array<string,array<string,mixed>> $config */
    public function __construct(array $config)
    {
        foreach (EventType::cases() as $type) {
            if (! isset($config[$type->value])) {
                throw new LogicException("config/event-types.php has no entry for [{$type->value}].");
            }
            $this->definitions[$type->value] = EventTypeDefinition::fromArray($type, $config[$type->value]);
        }
        $extra = array_diff(array_keys($config), EventType::values());
        if ($extra !== []) {
            throw new LogicException('config/event-types.php has unknown keys: '.implode(', ', $extra));
        }
    }

    /** @param array<string,array<string,mixed>> $config */
    public static function fromConfig(array $config): self
    {
        return new self($config);
    }

    public function for(EventType|string $type): EventTypeDefinition
    {
        $key = $type instanceof EventType ? $type->value : $type;

        return $this->definitions[$key] ?? throw new LogicException("Unknown event type [{$key}].");
    }

    /** @return array<string,EventTypeDefinition> */
    public function all(): array
    {
        return $this->definitions;
    }

    /** @return list<string> */
    public function keys(): array
    {
        return array_keys($this->definitions);
    }
}
