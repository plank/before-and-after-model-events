<?php

use Plank\BeforeAndAfterModelEvents\Tests\Models\ModelWithCustomEvents;
use Plank\BeforeAndAfterModelEvents\Tests\Models\TestModel;

it('fires before and after events for creating', function () {
    $events = [];

    TestModel::beforeEvent('creating', function () use (&$events) {
        $events[] = 'before_creating';
    });

    TestModel::afterEvent('creating', function () use (&$events) {
        $events[] = 'after_creating';
    });

    $model = new TestModel(['name' => 'Test', 'email' => 'test@example.com']);
    $model->save();

    expect($events)->toContain('before_creating')
        ->and($events)->toContain('after_creating');
});

it('fires before and after events for created', function () {
    $events = [];

    TestModel::beforeEvent('created', function () use (&$events) {
        $events[] = 'before_created';
    });

    TestModel::afterEvent('created', function () use (&$events) {
        $events[] = 'after_created';
    });

    $model = new TestModel(['name' => 'Test', 'email' => 'test@example.com']);
    $model->save();

    expect($events)->toContain('before_created')
        ->and($events)->toContain('after_created');
});

it('fires before and after events for updating', function () {
    $events = [];

    TestModel::beforeEvent('updating', function () use (&$events) {
        $events[] = 'before_updating';
    });

    TestModel::afterEvent('updating', function () use (&$events) {
        $events[] = 'after_updating';
    });

    $model = TestModel::create(['name' => 'Test', 'email' => 'test@example.com']);
    $model->name = 'Updated Name';
    $model->save();

    expect($events)->toContain('before_updating')
        ->and($events)->toContain('after_updating');
});

it('fires before and after events for deleting', function () {
    $events = [];

    TestModel::beforeEvent('deleting', function () use (&$events) {
        $events[] = 'before_deleting';
    });

    TestModel::afterEvent('deleting', function () use (&$events) {
        $events[] = 'after_deleting';
    });

    $model = TestModel::create(['name' => 'Test', 'email' => 'test@example.com']);
    $model->delete();

    expect($events)->toContain('before_deleting')
        ->and($events)->toContain('after_deleting');
});

it('can prevent event execution when before event returns false', function () {
    $events = [];

    TestModel::beforeEvent('creating', function () use (&$events) {
        $events[] = 'before_creating';

        return false;
    });

    TestModel::creating(function () use (&$events) {
        $events[] = 'creating';
    });

    $model = new TestModel(['name' => 'Test', 'email' => 'test@example.com']);
    $result = $model->save();

    expect($result)->toBeFalse()
        ->and($events)->toContain('before_creating')
        ->and($events)->not->toContain('creating');
});

it('fires custom events with before and after', function () {
    $events = [];

    ModelWithCustomEvents::beforeEvent('activating', function () use (&$events) {
        $events[] = 'before_activating';
    });

    ModelWithCustomEvents::afterEvent('activating', function () use (&$events) {
        $events[] = 'after_activating';
    });

    $model = ModelWithCustomEvents::create(['name' => 'Test', 'status' => 'inactive']);
    $model->activate();

    expect($events)->toContain('before_activating')
        ->and($events)->toContain('after_activating')
        ->and($model->status)->toBe('active');
});

it('can prevent custom event execution', function () {
    $events = [];

    ModelWithCustomEvents::beforeEvent('activating', function () use (&$events) {
        $events[] = 'before_activating';

        return false;
    });

    ModelWithCustomEvents::afterEvent('activating', function () use (&$events) {
        $events[] = 'after_activating';
    });

    $model = ModelWithCustomEvents::create(['name' => 'Test', 'status' => 'inactive']);
    $oldStatus = $model->status;

    // This should fail silently due to before event returning false
    $model->activate();

    expect($events)->toContain('before_activating')
        ->and($events)->not->toContain('after_activating')
        ->and($model->refresh()->status)->toBe($oldStatus); // Status should not change
});

it('fires events in correct order', function () {
    $events = [];

    TestModel::beforeEvent('creating', function () use (&$events) {
        $events[] = 'before_creating';
    });

    TestModel::creating(function () use (&$events) {
        $events[] = 'creating';
    });

    TestModel::afterEvent('creating', function () use (&$events) {
        $events[] = 'after_creating';
    });

    $model = new TestModel(['name' => 'Test', 'email' => 'test@example.com']);
    $model->save();

    $beforeIndex = array_search('before_creating', $events);
    $duringIndex = array_search('creating', $events);
    $afterIndex = array_search('after_creating', $events);

    expect($beforeIndex)->toBeLessThan($duringIndex)
        ->and($duringIndex)->toBeLessThan($afterIndex);
});

it('maintains separate event registries for different models', function () {
    $events1 = [];
    $events2 = [];

    TestModel::beforeEvent('testEvent', function () use (&$events1) {
        $events1[] = 'test_model_event';
    });

    ModelWithCustomEvents::beforeEvent('testEvent', function () use (&$events2) {
        $events2[] = 'custom_model_event';
    });

    $model1 = new TestModel(['name' => 'Test1']);
    $model2 = new ModelWithCustomEvents(['name' => 'Test2']);

    $model1->firePublicModelEvent('testEvent');
    $model2->firePublicModelEvent('testEvent');

    expect($events1)->toContain('test_model_event')
        ->and($events1)->not->toContain('custom_model_event')
        ->and($events2)->toContain('custom_model_event')
        ->and($events2)->not->toContain('test_model_event');
});

it('properly registers dynamic events', function () {
    $events = [];

    // Register listeners for a completely new event
    TestModel::beforeEvent('customPublish', function () use (&$events) {
        $events[] = 'before_custom_publish';
    });

    TestModel::afterEvent('customPublish', function () use (&$events) {
        $events[] = 'after_custom_publish';
    });

    $model = new TestModel(['name' => 'Test']);
    $model->firePublicModelEvent('customPublish');

    expect($events)->toContain('before_custom_publish')
        ->and($events)->toContain('after_custom_publish');
});

it('adds observable events correctly', function () {
    // Register some events
    TestModel::beforeEvent('newEvent', function () {});
    TestModel::afterEvent('anotherEvent', function () {});

    $model = new TestModel(['name' => 'Test']);
    $observableEvents = $model->getObservableEvents();

    expect($observableEvents)->toContain('newEvent')
        ->and($observableEvents)->toContain('beforeNewEvent')
        ->and($observableEvents)->toContain('afterNewEvent')
        ->and($observableEvents)->toContain('anotherEvent')
        ->and($observableEvents)->toContain('beforeAnotherEvent')
        ->and($observableEvents)->toContain('afterAnotherEvent');
});
