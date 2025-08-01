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
        $this->addObservableEvents([
            'beforeCreating',
            'afterCreating',
            'beforeCreated',
            'afterCreated',
            'beforeSaving',
            'afterSaving',
            'beforeSaved',
            'afterSaved',
            'beforeUpdating',
            'afterUpdating',
            'beforeUpdated',
            'afterUpdated',
            'beforeDeleting',
            'afterDeleting',
            'beforeDeleted',
            'afterDeleted',
            'beforeRestoring',
            'afterRestoring',
            'beforeRestored',
            'afterRestored',
        ]);
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
