<?php

namespace Plank\BeforeAndAfterModelEvents\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Plank\BeforeAndAfterModelEvents\Concerns\AddBeforeAndAfterEvents;

class TestModel extends Model
{
    use AddBeforeAndAfterEvents, SoftDeletes;

    protected $fillable = ['name', 'email'];

    protected $table = 'test_models';

    // Public wrapper for testing fireModelEvent
    public function fireEvent($event, $halt = true)
    {
        return $this->fireModelEvent($event, $halt);
    }
}
