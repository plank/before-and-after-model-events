<?php

use Plank\BeforeAndAfterModelEvents\Tests\Models\ModelWithOwnEvents;

beforeEach(function () {
    ModelWithOwnEvents::clearEventLog();
});

it('fires both trait and model events in correct order during create', function () {
    ModelWithOwnEvents::create(['name' => 'Test User']);

    expect(ModelWithOwnEvents::$eventLog)->toBe([
        'trait_beforeSaving',
        'model_saving',
        'trait_afterSaving',
        'trait_beforeCreating',
        'model_creating',
        'trait_afterCreating',
        'trait_beforeCreated',
        'model_created',
        'trait_afterCreated',
        'trait_beforeSaved',
        'model_saved',
        'trait_afterSaved',
    ]);
});

it('fires both trait and model events in correct order during update', function () {
    $model = ModelWithOwnEvents::create(['name' => 'Test User']);
    ModelWithOwnEvents::clearEventLog();

    $model->update(['name' => 'Updated User']);

    expect(ModelWithOwnEvents::$eventLog)->toBe([
        'trait_beforeSaving',
        'model_saving',
        'trait_afterSaving',
        'trait_beforeUpdating',
        'model_updating',
        'trait_afterUpdating',
        'trait_beforeUpdated',
        'model_updated',
        'trait_afterUpdated',
        'trait_beforeSaved',
        'model_saved',
        'trait_afterSaved',
    ]);
});

it('fires both trait and model events in correct order during delete', function () {
    $model = ModelWithOwnEvents::create(['name' => 'Test User']);
    ModelWithOwnEvents::clearEventLog();

    $model->delete();

    expect(ModelWithOwnEvents::$eventLog)->toBe([
        'trait_beforeDeleting',
        'model_deleting',
        'trait_afterDeleting',
        'trait_beforeDeleted',
        'model_deleted',
        'trait_afterDeleted',
    ]);
});

it('fires both trait and model events in correct order during restore', function () {
    $model = ModelWithOwnEvents::create(['name' => 'Test User']);
    $model->delete();
    ModelWithOwnEvents::clearEventLog();

    $model->restore();

    // Restore internally calls save() which triggers saving/updating events too
    expect(ModelWithOwnEvents::$eventLog)->toBe([
        'trait_beforeRestoring',
        'model_restoring',
        'trait_afterRestoring',
        'trait_beforeSaving',
        'model_saving',
        'trait_afterSaving',
        'trait_beforeUpdating',
        'model_updating',
        'trait_afterUpdating',
        'trait_beforeUpdated',
        'model_updated',
        'trait_afterUpdated',
        'trait_beforeSaved',
        'model_saved',
        'trait_afterSaved',
        'trait_beforeRestored',
        'model_restored',
        'trait_afterRestored',
    ]);
});

it('fires both trait and model events in correct order during force delete', function () {
    $model = ModelWithOwnEvents::create(['name' => 'Test User']);
    ModelWithOwnEvents::clearEventLog();

    $model->forceDelete();

    expect(ModelWithOwnEvents::$eventLog)->toBe([
        'trait_beforeDeleting',
        'model_deleting',
        'trait_afterDeleting',
        'trait_beforeDeleted',
        'model_deleted',
        'trait_afterDeleted',
    ]);
});

it('can prevent operations when model event returns false', function () {
    // Add a listener that prevents creation
    ModelWithOwnEvents::creating(function ($model) {
        ModelWithOwnEvents::$eventLog[] = 'preventing_creation';

        return false;
    });

    $model = new ModelWithOwnEvents(['name' => 'Test User']);
    $result = $model->save();

    expect($result)->toBeFalse();
    expect(ModelWithOwnEvents::count())->toBe(0);

    // Should still fire before events but not after events
    expect(ModelWithOwnEvents::$eventLog)->toContain('trait_beforeSaving');
    expect(ModelWithOwnEvents::$eventLog)->toContain('model_saving');
    expect(ModelWithOwnEvents::$eventLog)->toContain('trait_beforeCreating');
    expect(ModelWithOwnEvents::$eventLog)->toContain('preventing_creation');

    // After events should not fire when operation is prevented
    expect(ModelWithOwnEvents::$eventLog)->not->toContain('trait_afterCreating');
    expect(ModelWithOwnEvents::$eventLog)->not->toContain('trait_afterCreated');
    expect(ModelWithOwnEvents::$eventLog)->not->toContain('model_created');
});

it('can prevent operations when trait before event returns false', function () {
    // Add a trait listener that prevents creation
    ModelWithOwnEvents::beforeCreating(function ($model) {
        ModelWithOwnEvents::$eventLog[] = 'trait_preventing_creation';

        return false;
    });

    $model = new ModelWithOwnEvents(['name' => 'Test User']);
    $result = $model->save();

    expect($result)->toBeFalse();
    expect(ModelWithOwnEvents::count())->toBe(0);

    // Should fire before events up to the preventing one
    expect(ModelWithOwnEvents::$eventLog)->toContain('trait_beforeSaving');
    expect(ModelWithOwnEvents::$eventLog)->toContain('model_saving');
    expect(ModelWithOwnEvents::$eventLog)->toContain('trait_preventing_creation');

    // Should not fire the standard creating event or any after events
    expect(ModelWithOwnEvents::$eventLog)->not->toContain('model_creating');
    expect(ModelWithOwnEvents::$eventLog)->not->toContain('trait_afterCreating');
    expect(ModelWithOwnEvents::$eventLog)->not->toContain('model_created');
});

it('can prevent updates when trait before event returns false', function () {
    $model = ModelWithOwnEvents::create(['name' => 'Test User']);
    $originalName = $model->name;
    ModelWithOwnEvents::clearEventLog();

    // Add a trait listener that prevents updating
    ModelWithOwnEvents::beforeUpdating(function ($model) {
        ModelWithOwnEvents::$eventLog[] = 'trait_preventing_update';

        return false;
    });

    $result = $model->update(['name' => 'Updated User']);

    expect($result)->toBeFalse();
    expect($model->fresh()->name)->toBe($originalName);

    // Should fire before events up to the preventing one
    expect(ModelWithOwnEvents::$eventLog)->toContain('trait_beforeSaving');
    expect(ModelWithOwnEvents::$eventLog)->toContain('model_saving');
    expect(ModelWithOwnEvents::$eventLog)->toContain('trait_preventing_update');

    // Should not fire the standard updating event or any after events
    expect(ModelWithOwnEvents::$eventLog)->not->toContain('model_updating');
    expect(ModelWithOwnEvents::$eventLog)->not->toContain('trait_afterUpdating');
});

it('can prevent deletion when trait before event returns false', function () {
    $model = ModelWithOwnEvents::create(['name' => 'Test User']);
    ModelWithOwnEvents::clearEventLog();

    // Add a trait listener that prevents deletion
    ModelWithOwnEvents::beforeDeleting(function ($model) {
        ModelWithOwnEvents::$eventLog[] = 'trait_preventing_deletion';

        return false;
    });

    $result = $model->delete();

    expect($result)->toBeFalse();
    expect($model->exists)->toBeTrue();

    // Should fire the model's boot listener and the additional preventing listener
    expect(ModelWithOwnEvents::$eventLog)->toBe([
        'trait_beforeDeleting',
        'trait_preventing_deletion',
    ]);
});

it('allows models to modify attributes in before events', function () {
    // Add a listener that modifies attributes
    ModelWithOwnEvents::beforeCreating(function ($model) {
        $model->email = 'modified@example.com';
        ModelWithOwnEvents::$eventLog[] = 'trait_modified_email';
    });

    ModelWithOwnEvents::creating(function ($model) {
        $model->name = 'Modified Name';
        ModelWithOwnEvents::$eventLog[] = 'model_modified_name';
    });

    $model = ModelWithOwnEvents::create(['name' => 'Original Name']);

    expect($model->name)->toBe('Modified Name');
    expect($model->email)->toBe('modified@example.com');
    expect(ModelWithOwnEvents::$eventLog)->toContain('trait_modified_email');
    expect(ModelWithOwnEvents::$eventLog)->toContain('model_modified_name');
});

it('handles multiple listeners for the same event type correctly', function () {
    // Add multiple listeners for beforeCreating
    ModelWithOwnEvents::beforeCreating(function ($model) {
        ModelWithOwnEvents::$eventLog[] = 'trait_beforeCreating_1';
    });

    ModelWithOwnEvents::beforeCreating(function ($model) {
        ModelWithOwnEvents::$eventLog[] = 'trait_beforeCreating_2';
    });

    ModelWithOwnEvents::afterCreated(function ($model) {
        ModelWithOwnEvents::$eventLog[] = 'trait_afterCreated_1';
    });

    ModelWithOwnEvents::afterCreated(function ($model) {
        ModelWithOwnEvents::$eventLog[] = 'trait_afterCreated_2';
    });

    ModelWithOwnEvents::create(['name' => 'Test User']);

    // Should fire all listeners in order they were registered
    expect(ModelWithOwnEvents::$eventLog)->toContain('trait_beforeCreating');
    expect(ModelWithOwnEvents::$eventLog)->toContain('trait_beforeCreating_1');
    expect(ModelWithOwnEvents::$eventLog)->toContain('trait_beforeCreating_2');
    expect(ModelWithOwnEvents::$eventLog)->toContain('trait_afterCreated');
    expect(ModelWithOwnEvents::$eventLog)->toContain('trait_afterCreated_1');
    expect(ModelWithOwnEvents::$eventLog)->toContain('trait_afterCreated_2');
});

it('maintains correct event order with complex operations', function () {
    // Create, update, soft delete, restore, then force delete
    $model = ModelWithOwnEvents::create(['name' => 'Test User']);
    ModelWithOwnEvents::clearEventLog();

    $model->update(['name' => 'Updated User']);
    $model->delete();
    $model->restore();
    $model->forceDelete();

    // Verify all operations fired their events
    $eventLog = ModelWithOwnEvents::$eventLog;

    // Update events
    expect($eventLog)->toContain('trait_beforeUpdating');
    expect($eventLog)->toContain('model_updating');
    expect($eventLog)->toContain('trait_afterUpdating');

    // Soft delete events
    expect($eventLog)->toContain('trait_beforeDeleting');
    expect($eventLog)->toContain('model_deleting');
    expect($eventLog)->toContain('trait_afterDeleting');

    // Restore events
    expect($eventLog)->toContain('trait_beforeRestoring');
    expect($eventLog)->toContain('model_restoring');
    expect($eventLog)->toContain('trait_afterRestoring');

    // All events should be present in the log
    expect(count($eventLog))->toBeGreaterThan(20);
});
