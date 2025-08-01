<?php

use Plank\BeforeAndAfterModelEvents\Tests\Models\ModelWithOwnEvents;
use Plank\BeforeAndAfterModelEvents\Tests\Models\TestModel;

it('handles soft deletes correctly', function () {
    $events = [];

    TestModel::beforeEvent('deleting', function () use (&$events) {
        $events[] = 'before_deleting';
    });

    TestModel::afterEvent('deleting', function () use (&$events) {
        $events[] = 'after_deleting';
    });

    $model = TestModel::create(['name' => 'Test', 'email' => 'test@example.com']);
    $model->delete(); // This will be a soft delete

    expect($events)->toContain('before_deleting')
        ->and($events)->toContain('after_deleting')
        ->and($model->trashed())->toBeTrue();
});

it('handles model restoration correctly', function () {
    $events = [];

    TestModel::beforeEvent('restoring', function () use (&$events) {
        $events[] = 'before_restoring';
    });

    TestModel::afterEvent('restoring', function () use (&$events) {
        $events[] = 'after_restoring';
    });

    $model = TestModel::create(['name' => 'Test', 'email' => 'test@example.com']);
    $model->delete(); // Soft delete first
    $model->restore();

    expect($events)->toContain('before_restoring')
        ->and($events)->toContain('after_restoring')
        ->and($model->trashed())->toBeFalse();
});

it('works with models that have custom event dispatchers', function () {
    $events = [];

    ModelWithOwnEvents::beforeEvent('processing', function () use (&$events) {
        $events[] = 'before_processing';
    });

    ModelWithOwnEvents::afterEvent('processing', function () use (&$events) {
        $events[] = 'after_processing';
    });

    $model = ModelWithOwnEvents::create(['name' => 'Test', 'email' => 'test@example.com']);
    $model->process();

    expect($events)->toContain('before_processing')
        ->and($events)->toContain('after_processing')
        ->and($model->status)->toBe('processed');
});

it('allows multiple listeners for the same event', function () {
    $events = [];

    TestModel::beforeEvent('saving', function () use (&$events) {
        $events[] = 'listener_1';
    });

    TestModel::beforeEvent('saving', function () use (&$events) {
        $events[] = 'listener_2';
    });

    $model = new TestModel(['name' => 'Test', 'email' => 'test@example.com']);
    $model->save();

    expect($events)->toContain('listener_1')
        ->and($events)->toContain('listener_2');
});

it('handles exceptions in event listeners', function () {
    TestModel::beforeEvent('creating', function () {
        throw new \Exception('Test exception');
    });

    $model = new TestModel(['name' => 'Test', 'email' => 'test@example.com']);

    expect(fn () => $model->save())->toThrow(\Exception::class, 'Test exception');
});

it('properly initializes observable events from dynamic registration', function () {
    $events = [];

    // Register events that should become observable
    TestModel::beforeEvent('newCustomEvent', function () use (&$events) {
        $events[] = 'before_new_custom';
    });

    TestModel::afterEvent('anotherCustomEvent', function () use (&$events) {
        $events[] = 'after_another_custom';
    });

    $model = new TestModel(['name' => 'Test']);
    $observableEvents = $model->getObservableEvents();

    // Check that the events and their before/after variants are observable
    expect($observableEvents)->toContain('newCustomEvent')
        ->and($observableEvents)->toContain('beforeNewCustomEvent')
        ->and($observableEvents)->toContain('afterNewCustomEvent')
        ->and($observableEvents)->toContain('anotherCustomEvent')
        ->and($observableEvents)->toContain('beforeAnotherCustomEvent')
        ->and($observableEvents)->toContain('afterAnotherCustomEvent');

    // Test that the events actually fire
    $model->firePublicModelEvent('newCustomEvent');
    $model->firePublicModelEvent('anotherCustomEvent');

    expect($events)->toContain('before_new_custom')
        ->and($events)->toContain('after_another_custom');
});

it('prevents duplicate event registration but allows multiple listeners', function () {
    $events = [];

    // Register multiple listeners for the same event
    // This should work (multiple listeners for same event)
    TestModel::beforeEvent('sameEvent', function () use (&$events) {
        $events[] = 'listener_1';
    });

    TestModel::beforeEvent('sameEvent', function () use (&$events) {
        $events[] = 'listener_2';
    });

    TestModel::afterEvent('sameEvent', function () use (&$events) {
        $events[] = 'after_listener';
    });

    $model = new TestModel(['name' => 'Test']);
    $model->firePublicModelEvent('sameEvent');

    // All listeners should fire
    expect($events)->toContain('listener_1')
        ->and($events)->toContain('listener_2')
        ->and($events)->toContain('after_listener');

    // Check that the event appears only once in observable events (not duplicated)
    $observableEvents = $model->getObservableEvents();
    $eventCount = array_count_values($observableEvents)['sameEvent'] ?? 0;
    expect($eventCount)->toBe(1);
});

it('handles case sensitivity in event names', function () {
    $events = [];

    TestModel::beforeEvent('customEvent', function () use (&$events) {
        $events[] = 'lower_case';
    });

    TestModel::beforeEvent('CustomEvent', function () use (&$events) {
        $events[] = 'pascal_case';
    });

    $model = new TestModel(['name' => 'Test']);

    // These should be treated as different events
    $model->firePublicModelEvent('customEvent');
    $model->firePublicModelEvent('CustomEvent');

    expect($events)->toContain('lower_case')
        ->and($events)->toContain('pascal_case');
});

it('handles special characters in event names', function () {
    $events = [];

    TestModel::beforeEvent('event-with-dashes', function () use (&$events) {
        $events[] = 'dashes';
    });

    TestModel::beforeEvent('event_with_underscores', function () use (&$events) {
        $events[] = 'underscores';
    });

    TestModel::beforeEvent('event.with.dots', function () use (&$events) {
        $events[] = 'dots';
    });

    $model = new TestModel(['name' => 'Test']);

    $model->firePublicModelEvent('event-with-dashes');
    $model->firePublicModelEvent('event_with_underscores');
    $model->firePublicModelEvent('event.with.dots');

    expect($events)->toContain('dashes')
        ->and($events)->toContain('underscores')
        ->and($events)->toContain('dots');
});

it('respects halt parameter correctly', function () {
    $events = [];

    TestModel::beforeEvent('creating', function () use (&$events) {
        $events[] = 'before_creating';
    });

    TestModel::creating(function () use (&$events) {
        $events[] = 'creating';

        return false;
    });

    TestModel::afterEvent('creating', function () use (&$events) {
        $events[] = 'after_creating';
    });

    $model = new TestModel(['name' => 'Test', 'email' => 'test@example.com']);

    // When the main event returns false, after event should not fire
    $result = $model->firePublicModelEvent('creating', true);

    expect($result)->toBeFalse()
        ->and($events)->toContain('before_creating')
        ->and($events)->toContain('creating')
        ->and($events)->not->toContain('after_creating');
});

it('persists event registration across multiple instances', function () {
    $events = [];

    TestModel::beforeEvent('persistentEvent', function () use (&$events) {
        $events[] = 'persistent';
    });

    $model1 = new TestModel(['name' => 'Test1']);
    $model2 = new TestModel(['name' => 'Test2']);

    $model1->firePublicModelEvent('persistentEvent');
    $model2->firePublicModelEvent('persistentEvent');

    expect($events)->toHaveCount(2)
        ->and($events[0])->toBe('persistent')
        ->and($events[1])->toBe('persistent');
});
