<?php

namespace Plank\BeforeAndAfterModelEvents\Tests\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ModelSaved
{
    use Dispatchable, SerializesModels;

    public function __construct(public $model) {}
}
