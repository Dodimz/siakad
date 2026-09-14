<?php

namespace App\Traits;

use App\Models\Kampus;
use App\Support\TenantManager;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToTenant
{
    protected static function bootBelongsToTenant(): void
    {
        static::addGlobalScope('kampus', function (Builder $builder) {
            $manager = app(TenantManager::class);

            if (! $manager->isSetup()) {
                return;
            }

            $builder->where($builder->getModel()->getTable().'.kampus_id', $manager->id());
        });

        static::creating(function (Model $model) {
            $manager = app(TenantManager::class);

            if ($manager->isSetup() && ! array_key_exists('kampus_id', $model->getAttributes())) {
                $model->kampus_id = $manager->id();
            }
        });
    }

    public function kampus(): BelongsTo
    {
        return $this->belongsTo(Kampus::class);
    }

    /**
     * Query yang di-scope ke kampus tertentu (mengabaikan global scope).
     */
    public function scopeForKampus(Builder $query, Kampus|int $kampus): Builder
    {
        $kampusId = $kampus instanceof Kampus ? $kampus->id : $kampus;

        return $query->withoutGlobalScope('kampus')
            ->where($query->getModel()->getTable().'.kampus_id', $kampusId);
    }
}
