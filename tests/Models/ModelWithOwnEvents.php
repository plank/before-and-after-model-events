<?php

namespace Plank\BeforeAndAfterModelEvents\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Plank\BeforeAndAfterModelEvents\Concerns\BeforeAndAfterEvents;

/**
 * @property string $name;
 * @property ?string $email;
 * @property ?string $status;
 */
class ModelWithOwnEvents extends Model
{
    use BeforeAndAfterEvents;

    protected $table = 'test_models';

    protected $fillable = ['name', 'email', 'status'];

    protected $dispatchesEvents = [
        'saved' => \Plank\BeforeAndAfterModelEvents\Tests\Events\ModelSaved::class,
    ];

    public function process()
    {
        $this->status = 'processed';
        $this->fireModelEvent('processing');
        $this->save();
    }

    public function firePublicModelEvent($event, $halt = true)
    {
        return $this->fireModelEvent($event, $halt);
    }
}
