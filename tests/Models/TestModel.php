<?php

namespace Plank\BeforeAndAfterModelEvents\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Plank\BeforeAndAfterModelEvents\Concerns\BeforeAndAfterEvents;

/**
 * @property string $name;
 * @property ?string $email;
 * @property ?string $status;
 */
class TestModel extends Model
{
    use BeforeAndAfterEvents, SoftDeletes;

    protected $fillable = ['name', 'email', 'status'];

    protected $table = 'test_models';

    public function publish()
    {
        $this->status = 'published';
        $this->fireModelEvent('publishing');
        $this->save();
    }

    public function customAction()
    {
        $this->fireModelEvent('customAction');
    }

    public function firePublicModelEvent($event, $halt = true)
    {
        return $this->fireModelEvent($event, $halt);
    }
}
