<?php

namespace App\Modules\Members\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class QuotaPlan extends Model
{
    use LogsActivity;

    protected $fillable = [
        'nome',
        'periodicidade_id',
        'valor',
        'tipo_vencimento_quota_id',
        'dia_vencimento_mes',
    ];

    protected function casts(): array
    {
        return [
            'valor' => 'decimal:2',
            'dia_vencimento_mes' => 'integer',
        ];
    }

    public function periodicidade(): BelongsTo
    {
        return $this->belongsTo(Periodicidade::class);
    }

    public function tipoVencimento(): BelongsTo
    {
        return $this->belongsTo(TipoVencimentoQuota::class, 'tipo_vencimento_quota_id');
    }

    public function members(): HasMany
    {
        return $this->hasMany(Member::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'nome', 'periodicidade_id', 'valor', 'tipo_vencimento_quota_id', 'dia_vencimento_mes',
            ])
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }
}
