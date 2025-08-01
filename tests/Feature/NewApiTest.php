<?php

use Plank\BeforeAndAfterModelEvents\Tests\Models\TestModel;

beforeEach(function () {
    $this->events = [];
});

it('supports new beforeEvent and afterEvent API for standard events', function () {
    TestModel::beforeEvent('creating', function ($model) {
        $this->events[] = 'beforeCreating';
    });

    TestModel::afterEvent('created', function ($model) {
        $this->events[] = 'afterCreated';
    });

    TestModel::create(['name' => 'Test User']);

    expect($this->events)->toBe(['beforeCreating', 'afterCreated']);
});

it('supports new API for custom events without needing property definition', function () {
    TestModel::beforeEvent('publishing', function ($model) {
        $this->events[] = 'beforePublishing';
    });

    TestModel::afterEvent('publishing', function ($model) {
        $this->events[] = 'afterPublishing';
    });

    TestModel::publishing(function ($model) {
        $this->events[] = 'publishing';
    });

    $model = TestModel::create(['name' => 'Test User']);
    $this->events = []; // Clear creation events

    $model->fireEvent('publishing');

    expect($this->events)->toBe(['beforePublishing', 'publishing', 'afterPublishing']);
});

it('new API works with any custom event name', function () {
    TestModel::beforeEvent('customBusinessEvent', function ($model) {
        $this->events[] = 'beforeCustomBusinessEvent';
    });

    TestModel::afterEvent('customBusinessEvent', function ($model) {
        $this->events[] = 'afterCustomBusinessEvent';
    });

    TestModel::customBusinessEvent(function ($model) {
        $this->events[] = 'customBusinessEvent';
    });

    $model = TestModel::create(['name' => 'Test User']);
    $this->events = []; // Clear creation events

    $model->fireEvent('customBusinessEvent');

    expect($this->events)->toBe(['beforeCustomBusinessEvent', 'customBusinessEvent', 'afterCustomBusinessEvent']);
});

it('new API can prevent events by returning false', function () {
    TestModel::beforeEvent('customEvent', function ($model) {
        $this->events[] = 'beforeCustomEvent';

        return false; // Prevent the event
    });

    TestModel::customEvent(function ($model) {
        $this->events[] = 'customEvent';
    });

    TestModel::afterEvent('customEvent', function ($model) {
        $this->events[] = 'afterCustomEvent';
    });

    $model = TestModel::create(['name' => 'Test User']);
    $this->events = []; // Clear creation events

    $result = $model->fireEvent('customEvent');

    expect($result)->toBeFalse();
    expect($this->events)->toBe(['beforeCustomEvent']);
});

it('new API works alongside backward compatible magic methods', function () {
    // Mix new API and old magic methods
    TestModel::beforeEvent('publishing', function ($model) {
        $this->events[] = 'beforePublishing-newAPI';
    });

    TestModel::beforeCreating(function ($model) {
        $this->events[] = 'beforeCreating-magic';
    });

    TestModel::create(['name' => 'Test User']);

    expect($this->events)->toContain('beforeCreating-magic');

    // Test custom event
    $model = TestModel::first();
    $this->events = [];
    $model->fireEvent('publishing');

    expect($this->events)->toContain('beforePublishing-newAPI');
});

it('new API handles camelCase and PascalCase correctly', function () {
    TestModel::beforeEvent('someComplexEvent', function ($model) {
        $this->events[] = 'beforeSomeComplexEvent';
    });

    TestModel::afterEvent('anotherEvent', function ($model) {
        $this->events[] = 'afterAnotherEvent';
    });

    $model = TestModel::create(['name' => 'Test User']);
    $this->events = []; // Clear creation events

    $model->fireEvent('someComplexEvent');
    $model->fireEvent('anotherEvent');

    expect($this->events)->toBe(['beforeSomeComplexEvent', 'afterAnotherEvent']);
});
