<?php

namespace JustBetter\MagentoStock\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;
use JustBetter\MagentoAsync\Concerns\HasOperations;
use JustBetter\MagentoProducts\Models\MagentoProduct;
use JustBetter\MagentoStock\Enums\Backorders;
use JustBetter\MagentoStock\Repositories\BaseRepository;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * @property int $id
 * @property string $sku
 * @property bool $sync
 * @property bool $in_stock
 * @property Backorders $backorders
 * @property int $quantity
 * @property ?array $msi_stock
 * @property ?array $msi_status
 * @property bool $retrieve
 * @property bool $update
 * @property ?Carbon $last_retrieved
 * @property ?Carbon $last_updated
 * @property string $checksum
 * @property int $fail_count
 * @property ?Carbon $last_failed
 */
class Stock extends Model
{
    use HasOperations;
    use LogsActivity;

    protected $table = 'magento_stocks';

    protected $casts = [
        'sync' => 'bool',
        'in_stock' => 'bool',
        'backorders' => Backorders::class,
        'retrieve' => 'bool',
        'update' => 'bool',
        'last_retrieved' => 'datetime',
        'last_updated' => 'datetime',
        'last_failed' => 'datetime',
        'msi_stock' => 'array',
        'msi_status' => 'array',
    ];

    protected $guarded = [];

    public static function booted(): void
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

    public function product(): HasOne
    {
        return $this->hasOne(MagentoProduct::class, 'sku', 'sku');
    }

    public function failed(): void
    {
        $this->last_failed = now();
        $this->fail_count++;

        $shouldRetry = $this->fail_count < BaseRepository::resolve()->failLimit();
        $this->sync = $shouldRetry;

        if (! $shouldRetry) {
            $this->update = false;
            $this->retrieve = false;
            $this->fail_count = 0;
        }

        $this->save();
    }

    public function getActivitylogOptions(): LogOptions
    {
        $fields = ['sync', 'backorders'];

        if (BaseRepository::resolve()->msi()) {
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
