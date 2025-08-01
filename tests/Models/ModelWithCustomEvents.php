<?php

namespace Plank\BeforeAndAfterModelEvents\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Plank\BeforeAndAfterModelEvents\Concerns\BeforeAndAfterEvents;

/**
 * @property string $name;
 * @property ?string $email;
 * @property ?string $status;
 */
class ModelWithCustomEvents extends Model
{
    use BeforeAndAfterEvents;

    protected $table = 'test_models';

    protected $fillable = ['name', 'email', 'status'];

    public function activate()
    {
        if ($this->fireModelEvent('activating') === false) {
            return false;
        }

        $this->status = 'active';
        $this->save();

        return true;
    }

    public function deactivate()
    {
        $this->status = 'inactive';
        $this->fireModelEvent('deactivating');
        $this->save();
    }

    public function firePublicModelEvent($event, $halt = true)
    {
        return $this->fireModelEvent($event, $halt);
    }
}
