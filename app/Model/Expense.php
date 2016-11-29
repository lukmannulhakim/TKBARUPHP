<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Vinkla\Hashids\Facades\Hashids;

class Expense extends Model
{
    use SoftDeletes;

    protected $dates = ['deleted_at'];

    protected $table = 'expenses';

    protected $fillable = [
        'name', 'type', 'amount', 'remarks'
    ];

    protected $hidden = [
        'expensable_type'
    ];

    public function hId()
    {
        return HashIds::encode($this->attributes['id']);
    }

    public function expensable()
    {
        return $this->morphTo();
    }

    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $user = Auth::user();
            if ($user) {
                $model->created_by = $user->id;
                $model->updated_by = $user->id;
            }
        });

        static::updating(function ($model) {
            $user = Auth::user();
            if ($user) {
                $model->updated_by = $user->id;
            }
        });

        static::deleting(function ($model) {
            $user = Auth::user();
            if ($user) {
                $model->deleted_by = $user->id;
                $model->save();
            }
        });
    }
}
