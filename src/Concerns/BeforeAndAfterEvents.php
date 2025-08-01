<?php

namespace Plank\BeforeAndAfterModelEvents\Concerns;

use Illuminate\Database\Eloquent\Model;

/**
 * @mixin Model
 */
trait BeforeAndAfterEvents
{
    /**
     * Static registry of custom events that have been registered
     */
    protected static $dynamicBeforeAfterEvents = [];

    public function initializeBeforeAndAfterEvents()
    {
        // Get dynamically registered events for this model class
        $events = static::$dynamicBeforeAfterEvents[static::class] ?? [];

        // Generate before/after events for all, plus the base events
        $eventsToAdd = [];
        foreach ($events as $event) {
            $eventsToAdd[] = $event; // The base event itself
            $eventsToAdd[] = 'before'.ucfirst($event);
            $eventsToAdd[] = 'after'.ucfirst($event);
        }

        $this->addObservableEvents($eventsToAdd);
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

    /**
     * Register a before event listener for any event
     *
     * @param  string  $event  The event name (e.g., 'creating', 'publishing', 'customEvent')
     * @param  callable  $callback  The callback to execute
     */
    public static function beforeEvent(string $event, callable $callback): void
    {
        // Register the base event for dynamic registration
        static::registerDynamicEvent($event);

        $eventName = 'before'.ucfirst($event);
        static::registerModelEvent($eventName, $callback);
    }

    /**
     * Register an after event listener for any event
     *
     * @param  string  $event  The event name (e.g., 'creating', 'publishing', 'customEvent')
     * @param  callable  $callback  The callback to execute
     */
    public static function afterEvent(string $event, callable $callback): void
    {
        // Register the base event for dynamic registration
        static::registerDynamicEvent($event);

        $eventName = 'after'.ucfirst($event);
        static::registerModelEvent($eventName, $callback);
    }

    /**
     * Register a dynamic event for this model class
     */
    protected static function registerDynamicEvent(string $event): void
    {
        if (! isset(static::$dynamicBeforeAfterEvents[static::class])) {
            static::$dynamicBeforeAfterEvents[static::class] = [];
        }

        if (! in_array($event, static::$dynamicBeforeAfterEvents[static::class])) {
            static::$dynamicBeforeAfterEvents[static::class][] = $event;
        }
    }
}
