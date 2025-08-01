<?php

use Plank\BeforeAndAfterModelEvents\Tests\Models\ModelWithCustomEvents;

beforeEach(function () {
    ModelWithCustomEvents::clearEventLog();
});

it('adds before and after events for custom events defined in model property', function () {
    $events = [];

    // Test that we can register listeners for custom before/after events
    ModelWithCustomEvents::beforePublishing(function ($model) use (&$events) {
        $events[] = 'beforePublishing';
    });

    ModelWithCustomEvents::afterPublishing(function ($model) use (&$events) {
        $events[] = 'afterPublishing';
    });

    ModelWithCustomEvents::beforePublished(function ($model) use (&$events) {
        $events[] = 'beforePublished';
    });

    ModelWithCustomEvents::afterPublished(function ($model) use (&$events) {
        $events[] = 'afterPublished';
    });

    $model = ModelWithCustomEvents::create(['name' => 'Test Post']);
    $model->publish();

    expect($events)->toBe([
        'beforePublishing',
        'afterPublishing',
        'beforePublished',
        'afterPublished',
    ]);

    // Verify the custom events also fired
    expect(ModelWithCustomEvents::$eventLog)->toContain('publishing');
    expect(ModelWithCustomEvents::$eventLog)->toContain('published');
});

it('fires custom events in correct order with before and after wrappers', function () {
    $events = [];

    ModelWithCustomEvents::beforePublishing(function ($model) use (&$events) {
        $events[] = 'beforePublishing';
    });

    ModelWithCustomEvents::publishing(function ($model) use (&$events) {
        $events[] = 'publishing';
    });

    ModelWithCustomEvents::afterPublishing(function ($model) use (&$events) {
        $events[] = 'afterPublishing';
    });

    $model = ModelWithCustomEvents::create(['name' => 'Test Post']);

    // Clear events from creation
    $events = [];
    ModelWithCustomEvents::clearEventLog();

    $model->fireEvent('publishing');

    expect($events)->toBe([
        'beforePublishing',
        'publishing',
        'afterPublishing',
    ]);
});

it('can prevent custom events by returning false from before events', function () {
    $events = [];

    ModelWithCustomEvents::beforePublishing(function ($model) use (&$events) {
        $events[] = 'beforePublishing';

        return false; // Prevent the publishing
    });

    ModelWithCustomEvents::publishing(fn ($model) => $events[] = 'publishing');
    ModelWithCustomEvents::afterPublishing(fn ($model) => $events[] = 'afterPublishing');

    $model = ModelWithCustomEvents::create(['name' => 'Test Post']);

    // Clear events from creation
    $events = [];
    ModelWithCustomEvents::clearEventLog();

    $result = $model->fireEvent('publishing');

    expect($result)->toBeFalse();
    expect($events)->toBe(['beforePublishing']);
    expect(ModelWithCustomEvents::$eventLog)->not->toContain('publishing');
});

it('supports multiple custom events with their own before/after events', function () {
    $events = [];

    // Register listeners for archiving events
    ModelWithCustomEvents::beforeArchiving(function ($model) use (&$events) {
        $events[] = 'beforeArchiving';
    });

    ModelWithCustomEvents::afterArchiving(function ($model) use (&$events) {
        $events[] = 'afterArchiving';
    });

    // Register listeners for publishing events
    ModelWithCustomEvents::beforePublishing(function ($model) use (&$events) {
        $events[] = 'beforePublishing';
    });

    ModelWithCustomEvents::afterPublishing(function ($model) use (&$events) {
        $events[] = 'afterPublishing';
    });

    $model = ModelWithCustomEvents::create(['name' => 'Test Post']);

    // Clear events from creation
    $events = [];
    ModelWithCustomEvents::clearEventLog();

    $model->archive(); // This will fire archiving event and save

    expect($events)->toContain('beforeArchiving');
    expect($events)->toContain('afterArchiving');
    expect(ModelWithCustomEvents::$eventLog)->toContain('archiving');
});

it('allows custom events to modify model attributes in before events', function () {
    ModelWithCustomEvents::beforePublishing(function ($model) {
        $model->name = 'Modified during beforePublishing';
    });

    $model = ModelWithCustomEvents::create(['name' => 'Original Name']);
    ModelWithCustomEvents::clearEventLog();

    $model->fireEvent('publishing');

    expect($model->name)->toBe('Modified during beforePublishing');
});

it('works with custom events alongside standard model events', function () {
    $events = [];

    // Mix of standard and custom events
    ModelWithCustomEvents::beforeSaving(function ($model) use (&$events) {
        $events[] = 'beforeSaving';
    });

    ModelWithCustomEvents::beforePublishing(function ($model) use (&$events) {
        $events[] = 'beforePublishing';
    });

    ModelWithCustomEvents::afterSaved(function ($model) use (&$events) {
        $events[] = 'afterSaved';
    });

    ModelWithCustomEvents::afterPublished(function ($model) use (&$events) {
        $events[] = 'afterPublished';
    });

    $model = ModelWithCustomEvents::create(['name' => 'Test Post']);

    // Clear events from creation
    $events = [];
    ModelWithCustomEvents::clearEventLog();

    $model->publish(); // This fires publishing, then saves, then fires published

    expect($events)->toContain('beforePublishing');
    expect($events)->toContain('beforeSaving');
    expect($events)->toContain('afterSaved');
    expect($events)->toContain('afterPublished');
});

it('throws exception for invalid custom event method calls', function () {
    expect(fn () => ModelWithCustomEvents::beforeInvalidEvent('not-callable'))
        ->toThrow(InvalidArgumentException::class, 'Method beforeInvalidEvent expects a single callable parameter.');
});

it('handles camelCase and PascalCase event names correctly', function () {
    // Test that both camelCase and PascalCase work
    $events = [];

    ModelWithCustomEvents::beforePublishing(function ($model) use (&$events) {
        $events[] = 'beforePublishing';
    });

    ModelWithCustomEvents::beforePublished(function ($model) use (&$events) {
        $events[] = 'beforePublished';
    });

    // These should work due to our __callStatic magic method
    ModelWithCustomEvents::beforeArchiving(function ($model) use (&$events) {
        $events[] = 'beforeArchiving';
    });

    $model = ModelWithCustomEvents::create(['name' => 'Test']);

    // Clear events from creation
    $events = [];

    $model->fireEvent('publishing');
    $model->fireEvent('archiving');

    expect($events)->toBe([
        'beforePublishing',
        'beforeArchiving',
    ]);
});

it('falls back to parent for non-event method calls', function () {
    // This should not throw an exception and should fall back to Model's __callStatic
    expect(fn () => ModelWithCustomEvents::nonExistentMethod())
        ->toThrow(BadMethodCallException::class);
});
