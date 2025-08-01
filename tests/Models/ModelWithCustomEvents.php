<?php

namespace Plank\BeforeAndAfterModelEvents\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Plank\BeforeAndAfterModelEvents\Concerns\AddBeforeAndAfterEvents;

class ModelWithCustomEvents extends Model
{
    use AddBeforeAndAfterEvents;

    protected $fillable = ['name', 'status'];

    protected $table = 'test_models';

    // Define custom events that should get before/after wrapping
    protected $beforeAndAfterEvents = ['publishing', 'published', 'archiving'];

    public static $eventLog = [];

    protected static function boot()
    {
        parent::boot();

        // Define the custom events that other packages might fire
        static::publishing(function ($model) {
            static::$eventLog[] = 'publishing';
        });

        static::published(function ($model) {
            static::$eventLog[] = 'published';
        });

        static::archiving(function ($model) {
            static::$eventLog[] = 'archiving';
        });
    }

    public static function clearEventLog()
    {
        static::$eventLog = [];
    }

    // Method to simulate a package firing custom events
    public function publish()
    {
        $this->fireModelEvent('publishing');
        $this->status = 'published';
        $this->save();
        $this->fireModelEvent('published');
    }

    public function archive()
    {
        $this->fireModelEvent('archiving');
        $this->status = 'archived';
        $this->save();
    }

    // Public wrapper for testing fireModelEvent
    public function fireEvent($event, $halt = true)
    {
        return $this->fireModelEvent($event, $halt);
    }
}
