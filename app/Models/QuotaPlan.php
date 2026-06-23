<?php

namespace App\Models;

use App\Enums\Periodicidade;
use App\Enums\TipoVencimentoQuota;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class QuotaPlan extends Model
{
    use LogsActivity;
    protected $fillable = [
        'nome',
        'periodicidade',
        'valor',
        'tipo_vencimento',
        'dia_vencimento_mes',
    ];

    protected function casts(): array
    {
        return [
            'periodicidade' => Periodicidade::class,
            'tipo_vencimento' => TipoVencimentoQuota::class,
            'valor' => 'decimal:2',
            'dia_vencimento_mes' => 'integer',
        ];
    }

    public function members(): HasMany
    {
        return $this->hasMany(Member::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'nome', 'periodicidade', 'valor', 'tipo_vencimento', 'dia_vencimento_mes',
            ])
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }
}
