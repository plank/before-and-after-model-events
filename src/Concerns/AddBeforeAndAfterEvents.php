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
            'beforeCreated',
            'afterCreating',
            'afterCreated',
            'beforeSaving',
            'beforeSaved',
            'afterSaving',
            'afterSaved',
            'beforeUpdating',
            'beforeUpdated',
            'afterUpdating',
            'afterUpdated',
            'beforeDeleting',
            'beforeDeleted',
            'afterDeleting',
            'afterDeleted',
            'beforeRestoring',
            'beforeRestored',
            'afterRestoring',
            'afterRestored',
        ]);
    }

    protected function fireModelEvent($event, $halt = true)
    {
        if (method_exists($this, $fireBefore = 'fireBefore'.ucfirst($event))) {
            $this->$fireBefore();
        }

        parent::fireModelEvent($event, $halt);

        if (method_exists($this, $fireAfter = 'fireAfter'.ucfirst($event))) {
            $this->$fireAfter();
        }
    }

    public function fireBeforeCreating(): void
    {
        $this->fireModelEvent('beforeCreating');
    }

    public function fireBeforeCreated(): void
    {
        $this->fireModelEvent('beforeCreated');
    }

    public function fireAfterCreating(): void
    {
        $this->fireModelEvent('afterCreating');
    }

    public function fireAfterCreated(): void
    {
        $this->fireModelEvent('afterCreated');
    }

    public function fireBeforeSaving(): void
    {
        $this->fireModelEvent('beforeSaving');
    }

    public function fireBeforeSaved(): void
    {
        $this->fireModelEvent('beforeSaved');
    }

    public function fireAfterSaving(): void
    {
        $this->fireModelEvent('afterSaving');
    }

    public function fireAfterSaved(): void
    {
        $this->fireModelEvent('afterSaved');
    }

    public function fireBeforeUpdating(): void
    {
        $this->fireModelEvent('beforeUpdating');
    }

    public function fireBeforeUpdated(): void
    {
        $this->fireModelEvent('beforeUpdated');
    }

    public function fireAfterUpdating(): void
    {
        $this->fireModelEvent('afterUpdating');
    }

    public function fireAfterUpdated(): void
    {
        $this->fireModelEvent('afterUpdated');
    }

    public function fireBeforeDeleting(): void
    {
        $this->fireModelEvent('beforeDeleting');
    }

    public function fireBeforeDeleted(): void
    {
        $this->fireModelEvent('beforeDeleted');
    }

    public function fireAfterDeleting(): void
    {
        $this->fireModelEvent('afterDeleting');
    }

    public function fireAfterDeleted(): void
    {
        $this->fireModelEvent('afterDeleted');
    }

    public function fireBeforeRestoring(): void
    {
        $this->fireModelEvent('beforeRestoring');
    }

    public function fireBeforeRestored(): void
    {
        $this->fireModelEvent('beforeRestored');
    }

    public function fireAfterRestoring(): void
    {
        $this->fireModelEvent('afterRestoring');
    }

    public function fireAfterRestored(): void
    {
        $this->fireModelEvent('afterRestored');
    }

    public static function beforeCreating(callable $callback): void
    {
        static::registerModelEvent('beforeCreating', $callback);
    }

    public static function beforeCreated(callable $callback): void
    {
        static::registerModelEvent('beforeCreated', $callback);
    }

    public static function afterCreating(callable $callback): void
    {
        static::registerModelEvent('afterCreating', $callback);
    }

    public static function afterCreated(callable $callback): void
    {
        static::registerModelEvent('afterCreated', $callback);
    }

    public static function beforeSaving(callable $callback): void
    {
        static::registerModelEvent('beforeSaving', $callback);
    }

    public static function beforeSaved(callable $callback): void
    {
        static::registerModelEvent('beforeSaved', $callback);
    }

    public static function afterSaving(callable $callback): void
    {
        static::registerModelEvent('afterSaving', $callback);
    }

    public static function afterSaved(callable $callback): void
    {
        static::registerModelEvent('afterSaved', $callback);
    }

    public static function beforeUpdating(callable $callback): void
    {
        static::registerModelEvent('beforeUpdating', $callback);
    }

    public static function beforeUpdated(callable $callback): void
    {
        static::registerModelEvent('beforeUpdated', $callback);
    }

    public static function afterUpdating(callable $callback): void
    {
        static::registerModelEvent('afterUpdating', $callback);
    }

    public static function afterUpdated(callable $callback): void
    {
        static::registerModelEvent('afterUpdated', $callback);
    }

    public static function beforeDeleting(callable $callback): void
    {
        static::registerModelEvent('beforeDeleting', $callback);
    }

    public static function beforeDeleted(callable $callback): void
    {
        static::registerModelEvent('beforeDeleted', $callback);
    }

    public static function afterDeleting(callable $callback): void
    {
        static::registerModelEvent('afterDeleting', $callback);
    }

    public static function afterDeleted(callable $callback): void
    {
        static::registerModelEvent('afterDeleted', $callback);
    }

    public static function beforeRestoring(callable $callback): void
    {
        static::registerModelEvent('beforeRestoring', $callback);
    }

    public static function beforeRestored(callable $callback): void
    {
        static::registerModelEvent('beforeRestored', $callback);
    }

    public static function afterRestoring(callable $callback): void
    {
        static::registerModelEvent('afterRestoring', $callback);
    }

    public static function afterRestored(callable $callback): void
    {
        static::registerModelEvent('afterRestored', $callback);
    }
}
