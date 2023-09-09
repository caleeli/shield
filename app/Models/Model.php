<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Support\Str;

class Model extends EloquentModel
{
    protected $textSearchColumns = [];

    public function creadoPor()
    {
        return $this->belongsTo(Usuario::class, 'creado_por');
    }

    // set creado_por and actualizado_por user
    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $user = auth()->user();
            if ($user) {
                $model->creado_por = $user->id;
                $model->actualizado_por = $user->id;
            }
        });

        static::updating(function ($model) {
            if (auth()->id()) {
                $model->actualizado_por = auth()->id();
            }
        });
    }

    public function scopeWhereSearch($query, $search)
    {
        if (empty($this->textSearchColumns)) {
            return $query;
        }
        $search = trim($search);
        $search = preg_replace('/\s+/', '%', $search);
        // create where clause (foo=:bar or baz=:bar)
        $query->where(function ($query) use ($search) {
            // if postgresql, use ilike
            $like = 'like';
            if (config('database.default') === 'pgsql') {
                $like = 'ilike';
            }
            foreach ($this->textSearchColumns as $column) {
                $isIdColumn = preg_match('/_id$/', $column);
                if ($isIdColumn) {
                    $query->orWhereHas(Str::camel(substr($column, 0, -3)), function ($query) use ($search, $like) {
                        $query->where('nombre', $like, "%$search%");
                    });
                } else {
                    $query->orWhere($column, $like, "%$search%");
                }
            }
        });
    }

    public function scopeWhereActivo($query)
    {
        return $query->whereEstadoId(1);
    }
}
