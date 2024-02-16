<?php

namespace JustBetter\MagentoStock\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use JustBetter\ErrorLogger\Models\Error;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * @property int $id
 * @property string $sku
 * @property bool $sync
 * @property bool $in_stock
 * @property bool $backorders
 * @property bool $magento_backorders_enabled
 * @property int $quantity
 * @property ?array $msi_stock
 * @property ?array $msi_status
 * @property bool $retrieve
 * @property bool $update
 * @property ?Carbon $last_retrieved
 * @property ?Carbon $last_updated
 * @property int $fail_count
 * @property ?Carbon $last_failed
 */
class MagentoStock extends Model
{
    use LogsActivity;

    public $casts = [
        'sync' => 'bool',
        'in_stock' => 'bool',
        'backorders' => 'bool',
        'magento_backorders_enabled' => 'bool',
        'retrieve' => 'bool',
        'update' => 'bool',
        'last_retrieved' => 'datetime',
        'last_updated' => 'datetime',
        'last_failed' => 'datetime',
        'msi_stock' => 'array',
        'msi_status' => 'array',
    ];

    protected $guarded = [];

    public static function booted()
    {
        static::updating(function (self $model) {
            if ($model->update && $model->retrieve) {
                if (! $model->isDirty(['retrieve'])) {
                    $model->retrieve = false;
                } else {
                    $model->update = false;
                }
            }
        });
    }

    public function errors(): MorphMany
    {
        return $this->morphMany(Error::class, 'model');
    }

    public function scopeShouldRetrieve(Builder $builder): Builder
    {
        return $builder
            ->where('sync', true)
            ->where('retrieve', true);
    }

    public function scopeShouldUpdate(Builder $builder): Builder
    {
        return $builder
            ->where('sync', true)
            ->where('update', true);
    }

    public function registerError(): void
    {
        $this->fail_count = ($this->fail_count ?? 0) + 1;
        $this->last_failed = now();

        $shouldRetry = $this->fail_count < config('magento-stock.fails.count');
        $this->sync = $shouldRetry;

        if (! $shouldRetry) {
            $this->update = false;
            $this->retrieve = false;
        }

        $this->save();
    }

    public function getActivitylogOptions(): LogOptions
    {
        $fields = ['sync', 'backorders'];

        if (config('magento-stock.msi', false)) {
            $fields = array_merge($fields, ['msi_stock', 'msi_status']);
        } else {
            $fields = array_merge($fields, ['in_stock', 'quantity']);
        }

        return LogOptions::defaults()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->logOnly($fields);
    }
}
