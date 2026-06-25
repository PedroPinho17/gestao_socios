<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class Member extends Model
{
    use LogsActivity;
    protected $fillable = [
        'numero',
        'nome',
        'email',
        'telefone',
        'data_adesao',
        'quota_plan_id',
        'foto_path',
        'ativo',
        'notas',
        'cargo_cartao',
        'validade_manual',
    ];

    protected function casts(): array
    {
        return [
            'data_adesao' => 'date',
            'validade_manual' => 'date',
            'ativo' => 'boolean',
        ];
    }

    public function quotaPlan(): BelongsTo
    {
        return $this->belongsTo(QuotaPlan::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class)->orderByDesc('data');
    }

    public function user(): HasOne
    {
        return $this->hasOne(User::class);
    }

    public function fotoUrl(): ?string
    {
        if (! $this->foto_path) {
            return null;
        }

        return route('secure.member.photo', $this);
    }

    /**
     * Cache da situação de quota durante o request (evita recalcular na mesma linha).
     *
     * @var array<string, mixed>|null
     */
    protected ?array $quotaSituationCache = null;

    public function quotaSituation(): array
    {
        if ($this->quotaSituationCache === null) {
            $this->quotaSituationCache = app(\App\Services\QuotaService::class)->getSituation($this);
        }

        return $this->quotaSituationCache;
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'numero', 'nome', 'email', 'telefone', 'data_adesao',
                'quota_plan_id', 'foto_path', 'ativo', 'notas',
                'cargo_cartao', 'validade_manual',
            ])
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }

    public static function nextNumero(): string
    {
        $nums = static::query()
            ->pluck('numero')
            ->map(fn (string $n) => (int) $n)
            ->filter(fn (int $n) => $n > 0);

        $next = $nums->isEmpty() ? 1 : $nums->max() + 1;

        return (string) $next;
    }
}
