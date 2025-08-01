<?php

namespace Plank\BeforeAndAfterModelEvents\Concerns;

use Illuminate\Database\Eloquent\Model;

/**
 * @mixin Model
 */
trait AddBeforeAndAfterEvents
{
    public function initializeAddBeforeAndAfterEvents()
    {
        $defaultEvents = [
            'creating',
            'created',
            'saving',
            'saved',
            'updating',
            'updated',
            'deleting',
            'deleted',
            'restoring',
            'restored',
        ];

        // Get custom events from model property
        $customEvents = $this->beforeAndAfterEvents ?? [];

        // Combine default and custom events
        $allEvents = array_merge($defaultEvents, $customEvents);

        // Generate before/after events for all
        $beforeAndAfterEvents = [];
        foreach ($allEvents as $event) {
            $beforeAndAfterEvents[] = 'before'.ucfirst($event);
            $beforeAndAfterEvents[] = 'after'.ucfirst($event);
        }

        $this->addObservableEvents($beforeAndAfterEvents);
    }

    protected function fireModelEvent($event, $halt = true)
    {
        // Fire before event
        $beforeEvent = 'before'.ucfirst($event);
        if (in_array($beforeEvent, $this->getObservableEvents())) {
            if (parent::fireModelEvent($beforeEvent, $halt) === false) {
                return false;
            }
        }

        // Fire the original event
        $result = parent::fireModelEvent($event, $halt);

        // Fire after event (only if original event didn't return false)
        if ($result !== false) {
            $afterEvent = 'after'.ucfirst($event);
            if (in_array($afterEvent, $this->getObservableEvents())) {
                parent::fireModelEvent($afterEvent, false);
            }
        }

        return $result;
    }

    public static function __callStatic($method, $parameters)
    {
        // Check if this is a before/after event method
        if (preg_match('/^(before|after)([A-Z].*)$/', $method, $matches)) {
            $eventType = $matches[1]; // 'before' or 'after'
            $eventName = $matches[2]; // e.g., 'Creating', 'Publishing'
            $fullEventName = $eventType.$eventName;

            // Validate that we have a callback parameter
            if (count($parameters) !== 1 || ! is_callable($parameters[0])) {
                throw new \InvalidArgumentException("Method {$method} expects a single callable parameter.");
            }

            static::registerModelEvent($fullEventName, $parameters[0]);

            return;
        }

        // Check if this is a standard event registration method that should be supported
        if (count($parameters) === 1 && is_callable($parameters[0])) {
            // This might be a custom event like 'publishing', 'published', etc.
            static::registerModelEvent($method, $parameters[0]);

            return;
        }

        // Fall back to parent if method doesn't match our patterns
        return parent::__callStatic($method, $parameters);
    }

    // Keep the most common ones as explicit methods for better IDE support
    public static function beforeCreating(callable $callback): void
    {
        static::registerModelEvent('beforeCreating', $callback);
    }

    public static function afterCreating(callable $callback): void
    {
        static::registerModelEvent('afterCreating', $callback);
    }

    public static function beforeCreated(callable $callback): void
    {
        static::registerModelEvent('beforeCreated', $callback);
    }

    public static function afterCreated(callable $callback): void
    {
        static::registerModelEvent('afterCreated', $callback);
    }

    public static function beforeSaving(callable $callback): void
    {
        static::registerModelEvent('beforeSaving', $callback);
    }

    public static function afterSaving(callable $callback): void
    {
        static::registerModelEvent('afterSaving', $callback);
    }

    public static function beforeSaved(callable $callback): void
    {
        static::registerModelEvent('beforeSaved', $callback);
    }

    public static function afterSaved(callable $callback): void
    {
        static::registerModelEvent('afterSaved', $callback);
    }

    public static function beforeUpdating(callable $callback): void
    {
        static::registerModelEvent('beforeUpdating', $callback);
    }

    public static function afterUpdating(callable $callback): void
    {
        static::registerModelEvent('afterUpdating', $callback);
    }

    public static function beforeUpdated(callable $callback): void
    {
        static::registerModelEvent('beforeUpdated', $callback);
    }

    public static function afterUpdated(callable $callback): void
    {
        static::registerModelEvent('afterUpdated', $callback);
    }

    public static function beforeDeleting(callable $callback): void
    {
        static::registerModelEvent('beforeDeleting', $callback);
    }

    public static function afterDeleting(callable $callback): void
    {
        static::registerModelEvent('afterDeleting', $callback);
    }

    public static function beforeDeleted(callable $callback): void
    {
        static::registerModelEvent('beforeDeleted', $callback);
    }

    public static function afterDeleted(callable $callback): void
    {
        static::registerModelEvent('afterDeleted', $callback);
    }

    public static function beforeRestoring(callable $callback): void
    {
        static::registerModelEvent('beforeRestoring', $callback);
    }

    public static function afterRestoring(callable $callback): void
    {
        static::registerModelEvent('afterRestoring', $callback);
    }

    public static function beforeRestored(callable $callback): void
    {
        static::registerModelEvent('beforeRestored', $callback);
    }

    public static function afterRestored(callable $callback): void
    {
        static::registerModelEvent('afterRestored', $callback);
    }
}
