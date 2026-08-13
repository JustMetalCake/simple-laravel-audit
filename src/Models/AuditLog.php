<?php

namespace Motomedialab\SimpleLaravelAudit\Models;

use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    public const UPDATED_AT = null;

    protected $guarded = [];

    protected $attributes = [
        'context' => '[]'
    ];
    protected $casts = [
        'context' => 'array'
    ];

    public function getTable()
    {
        return config('simple-auditor.table_name', 'audit_logs');
    }

    public function hasAuditableModel(): Attribute
    {
        return Attribute::get(fn () => collect($this->context)->has(['id', 'class']));
    }

    public function model(): ?array
    {
        if (!$this->hasAuditableModel) {
            return null;
        }

        /** @var ?string $class */
        $class = collect($this->context)->get('class');

        if ($class && class_exists($class)) {
            return [
                'id' => collect($this->context)->get('id'),
                'class' => $class,
            ];
        }

        return null;
    }

    public function filamentResource(): ?string
    {
        if ($this->model()) {
            return Filament::getModelResource($this->model()['class']);
        }

        return null;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
