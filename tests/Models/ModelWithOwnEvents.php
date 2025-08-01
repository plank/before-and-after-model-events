<?php

namespace Plank\BeforeAndAfterModelEvents\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Plank\BeforeAndAfterModelEvents\Concerns\AddBeforeAndAfterEvents;

class ModelWithOwnEvents extends Model
{
    use AddBeforeAndAfterEvents, SoftDeletes;

    protected $fillable = ['name', 'email'];

    protected $table = 'test_models';

    public static $eventLog = [];

    protected static function boot()
    {
        parent::boot();

        // Standard Laravel events
        static::creating(function ($model) {
            static::$eventLog[] = 'model_creating';
        });

        static::created(function ($model) {
            static::$eventLog[] = 'model_created';
        });

        static::updating(function ($model) {
            static::$eventLog[] = 'model_updating';
        });

        static::updated(function ($model) {
            static::$eventLog[] = 'model_updated';
        });

        static::saving(function ($model) {
            static::$eventLog[] = 'model_saving';
        });

        static::saved(function ($model) {
            static::$eventLog[] = 'model_saved';
        });

        static::deleting(function ($model) {
            static::$eventLog[] = 'model_deleting';
        });

        static::deleted(function ($model) {
            static::$eventLog[] = 'model_deleted';
        });

        static::restoring(function ($model) {
            static::$eventLog[] = 'model_restoring';
        });

        static::restored(function ($model) {
            static::$eventLog[] = 'model_restored';
        });

        // Before/After events from trait
        static::beforeCreating(function ($model) {
            static::$eventLog[] = 'trait_beforeCreating';
        });

        static::afterCreating(function ($model) {
            static::$eventLog[] = 'trait_afterCreating';
        });

        static::beforeCreated(function ($model) {
            static::$eventLog[] = 'trait_beforeCreated';
        });

        static::afterCreated(function ($model) {
            static::$eventLog[] = 'trait_afterCreated';
        });

        static::beforeUpdating(function ($model) {
            static::$eventLog[] = 'trait_beforeUpdating';
        });

        static::afterUpdating(function ($model) {
            static::$eventLog[] = 'trait_afterUpdating';
        });

        static::beforeUpdated(function ($model) {
            static::$eventLog[] = 'trait_beforeUpdated';
        });

        static::afterUpdated(function ($model) {
            static::$eventLog[] = 'trait_afterUpdated';
        });

        static::beforeSaving(function ($model) {
            static::$eventLog[] = 'trait_beforeSaving';
        });

        static::afterSaving(function ($model) {
            static::$eventLog[] = 'trait_afterSaving';
        });

        static::beforeSaved(function ($model) {
            static::$eventLog[] = 'trait_beforeSaved';
        });

        static::afterSaved(function ($model) {
            static::$eventLog[] = 'trait_afterSaved';
        });

        static::beforeDeleting(function ($model) {
            static::$eventLog[] = 'trait_beforeDeleting';
        });

        static::afterDeleting(function ($model) {
            static::$eventLog[] = 'trait_afterDeleting';
        });

        static::beforeDeleted(function ($model) {
            static::$eventLog[] = 'trait_beforeDeleted';
        });

        static::afterDeleted(function ($model) {
            static::$eventLog[] = 'trait_afterDeleted';
        });

        static::beforeRestoring(function ($model) {
            static::$eventLog[] = 'trait_beforeRestoring';
        });

        static::afterRestoring(function ($model) {
            static::$eventLog[] = 'trait_afterRestoring';
        });

        static::beforeRestored(function ($model) {
            static::$eventLog[] = 'trait_beforeRestored';
        });

        static::afterRestored(function ($model) {
            static::$eventLog[] = 'trait_afterRestored';
        });
    }

    public static function clearEventLog()
    {
        static::$eventLog = [];
    }
}
