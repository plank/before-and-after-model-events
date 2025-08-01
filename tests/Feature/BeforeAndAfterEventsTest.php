<?php

use Plank\BeforeAndAfterModelEvents\Tests\Models\TestModel;

beforeEach(function () {
    $this->events = [];
});

it('fires before and after events for creating and created', function () {
    TestModel::beforeCreating(function ($model) {
        $this->events[] = 'beforeCreating';
    });

    TestModel::creating(function ($model) {
        $this->events[] = 'creating';
    });

    TestModel::afterCreating(function ($model) {
        $this->events[] = 'afterCreating';
    });

    TestModel::beforeCreated(function ($model) {
        $this->events[] = 'beforeCreated';
    });

    TestModel::created(function ($model) {
        $this->events[] = 'created';
    });

    TestModel::afterCreated(function ($model) {
        $this->events[] = 'afterCreated';
    });

    TestModel::create(['name' => 'Test User']);

    expect($this->events)->toBe([
        'beforeCreating',
        'creating',
        'afterCreating',
        'beforeCreated',
        'created',
        'afterCreated',
    ]);
});

it('fires before and after events for saving and saved on create', function () {
    TestModel::beforeSaving(function ($model) {
        $this->events[] = 'beforeSaving';
    });

    TestModel::saving(function ($model) {
        $this->events[] = 'saving';
    });

    TestModel::afterSaving(function ($model) {
        $this->events[] = 'afterSaving';
    });

    TestModel::beforeSaved(function ($model) {
        $this->events[] = 'beforeSaved';
    });

    TestModel::saved(function ($model) {
        $this->events[] = 'saved';
    });

    TestModel::afterSaved(function ($model) {
        $this->events[] = 'afterSaved';
    });

    TestModel::create(['name' => 'Test User']);

    expect($this->events)->toContain('beforeSaving')
        ->toContain('saving')
        ->toContain('afterSaving')
        ->toContain('beforeSaved')
        ->toContain('saved')
        ->toContain('afterSaved');
});

it('fires before and after events for updating and updated', function () {
    $model = TestModel::create(['name' => 'Test User']);

    $this->events = []; // Reset events after creation

    TestModel::beforeUpdating(function ($model) {
        $this->events[] = 'beforeUpdating';
    });

    TestModel::updating(function ($model) {
        $this->events[] = 'updating';
    });

    TestModel::afterUpdating(function ($model) {
        $this->events[] = 'afterUpdating';
    });

    TestModel::beforeUpdated(function ($model) {
        $this->events[] = 'beforeUpdated';
    });

    TestModel::updated(function ($model) {
        $this->events[] = 'updated';
    });

    TestModel::afterUpdated(function ($model) {
        $this->events[] = 'afterUpdated';
    });

    $model->update(['name' => 'Updated User']);

    expect($this->events)->toBe([
        'beforeUpdating',
        'updating',
        'afterUpdating',
        'beforeUpdated',
        'updated',
        'afterUpdated',
    ]);
});

it('fires before and after events for saving and saved on update', function () {
    $model = TestModel::create(['name' => 'Test User']);

    $this->events = []; // Reset events after creation

    TestModel::beforeSaving(function ($model) {
        $this->events[] = 'beforeSaving';
    });

    TestModel::saving(function ($model) {
        $this->events[] = 'saving';
    });

    TestModel::afterSaving(function ($model) {
        $this->events[] = 'afterSaving';
    });

    TestModel::beforeSaved(function ($model) {
        $this->events[] = 'beforeSaved';
    });

    TestModel::saved(function ($model) {
        $this->events[] = 'saved';
    });

    TestModel::afterSaved(function ($model) {
        $this->events[] = 'afterSaved';
    });

    $model->update(['name' => 'Updated User']);

    expect($this->events)->toContain('beforeSaving')
        ->toContain('saving')
        ->toContain('afterSaving')
        ->toContain('beforeSaved')
        ->toContain('saved')
        ->toContain('afterSaved');
});

it('fires before and after events for deleting and deleted', function () {
    $model = TestModel::create(['name' => 'Test User']);

    $this->events = []; // Reset events after creation

    TestModel::beforeDeleting(function ($model) {
        $this->events[] = 'beforeDeleting';
    });

    TestModel::deleting(function ($model) {
        $this->events[] = 'deleting';
    });

    TestModel::afterDeleting(function ($model) {
        $this->events[] = 'afterDeleting';
    });

    TestModel::beforeDeleted(function ($model) {
        $this->events[] = 'beforeDeleted';
    });

    TestModel::deleted(function ($model) {
        $this->events[] = 'deleted';
    });

    TestModel::afterDeleted(function ($model) {
        $this->events[] = 'afterDeleted';
    });

    $model->delete();

    expect($this->events)->toBe([
        'beforeDeleting',
        'deleting',
        'afterDeleting',
        'beforeDeleted',
        'deleted',
        'afterDeleted',
    ]);
});

it('fires before and after events for restoring and restored', function () {
    $model = TestModel::create(['name' => 'Test User']);
    $model->delete();

    $this->events = []; // Reset events after deletion

    TestModel::beforeRestoring(function ($model) {
        $this->events[] = 'beforeRestoring';
    });

    TestModel::restoring(function ($model) {
        $this->events[] = 'restoring';
    });

    TestModel::afterRestoring(function ($model) {
        $this->events[] = 'afterRestoring';
    });

    TestModel::beforeRestored(function ($model) {
        $this->events[] = 'beforeRestored';
    });

    TestModel::restored(function ($model) {
        $this->events[] = 'restored';
    });

    TestModel::afterRestored(function ($model) {
        $this->events[] = 'afterRestored';
    });

    $model->restore();

    expect($this->events)->toBe([
        'beforeRestoring',
        'restoring',
        'afterRestoring',
        'beforeRestored',
        'restored',
        'afterRestored',
    ]);
});

it('fires before and after events for force deleting', function () {
    $model = TestModel::create(['name' => 'Test User']);

    $this->events = []; // Reset events after creation

    TestModel::beforeDeleting(function ($model) {
        $this->events[] = 'beforeDeleting';
    });

    TestModel::deleting(function ($model) {
        $this->events[] = 'deleting';
    });

    TestModel::afterDeleting(function ($model) {
        $this->events[] = 'afterDeleting';
    });

    TestModel::beforeDeleted(function ($model) {
        $this->events[] = 'beforeDeleted';
    });

    TestModel::deleted(function ($model) {
        $this->events[] = 'deleted';
    });

    TestModel::afterDeleted(function ($model) {
        $this->events[] = 'afterDeleted';
    });

    $model->forceDelete();

    expect($this->events)->toBe([
        'beforeDeleting',
        'deleting',
        'afterDeleting',
        'beforeDeleted',
        'deleted',
        'afterDeleted',
    ]);
});

it('passes the correct model instance to event callbacks', function () {
    $capturedModel = null;

    TestModel::beforeCreating(function ($model) use (&$capturedModel) {
        $capturedModel = $model;
    });

    $createdModel = TestModel::create(['name' => 'Test User']);

    expect($capturedModel)->toBe($createdModel);
});

it('can prevent model operations by returning false from before events', function () {
    TestModel::beforeCreating(function ($model) {
        return false;
    });

    $model = new TestModel(['name' => 'Test User']);
    $result = $model->save();

    expect($result)->toBeFalse();
    expect(TestModel::count())->toBe(0);
});

it('can prevent model updates by returning false from before events', function () {
    $model = TestModel::create(['name' => 'Test User']);
    $originalName = $model->name;

    TestModel::beforeUpdating(function ($model) {
        return false;
    });

    $result = $model->update(['name' => 'Updated User']);

    expect($result)->toBeFalse();
    expect($model->fresh()->name)->toBe($originalName);
});

it('can prevent model deletion by returning false from before events', function () {
    $model = TestModel::create(['name' => 'Test User']);

    TestModel::beforeDeleting(function ($model) {
        return false;
    });

    $result = $model->delete();

    expect($result)->toBeFalse();
    expect($model->exists)->toBeTrue();
});

it('before and after events are called in correct order during full lifecycle', function () {
    TestModel::beforeCreating(fn () => $this->events[] = 'beforeCreating');
    TestModel::creating(fn () => $this->events[] = 'creating');
    TestModel::afterCreating(fn () => $this->events[] = 'afterCreating');
    TestModel::beforeCreated(fn () => $this->events[] = 'beforeCreated');
    TestModel::created(fn () => $this->events[] = 'created');
    TestModel::afterCreated(fn () => $this->events[] = 'afterCreated');
    TestModel::beforeSaving(fn () => $this->events[] = 'beforeSaving');
    TestModel::saving(fn () => $this->events[] = 'saving');
    TestModel::afterSaving(fn () => $this->events[] = 'afterSaving');
    TestModel::beforeSaved(fn () => $this->events[] = 'beforeSaved');
    TestModel::saved(fn () => $this->events[] = 'saved');
    TestModel::afterSaved(fn () => $this->events[] = 'afterSaved');

    TestModel::create(['name' => 'Test User']);

    expect($this->events)->toBe([
        'beforeSaving',
        'saving',
        'afterSaving',
        'beforeCreating',
        'creating',
        'afterCreating',
        'beforeCreated',
        'created',
        'afterCreated',
        'beforeSaved',
        'saved',
        'afterSaved',
    ]);
});

it('can access and modify model attributes in before events', function () {
    TestModel::beforeCreating(function ($model) {
        $model->email = 'test@example.com';
    });

    $model = TestModel::create(['name' => 'Test User']);

    expect($model->email)->toBe('test@example.com');
});
