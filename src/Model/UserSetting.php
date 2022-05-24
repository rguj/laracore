<?php

namespace Rguj\Laracore\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

use Rguj\Laracore\Model\BaseModel;

class UserSetting extends BaseModel
{
    use HasFactory;

    protected $connection = 'hris';
    protected $table = 'ac_user_setting';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'integer';

    public $timestamps = true;
    protected $dateFormat  = 'Y-m-d H:i:s.u';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    
    protected $fillable = [
        'user_id',
        'key',
        'value',
    ];

    protected $hidden = [
        'user_id',
    ];

    protected $attributes = [
        
    ];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s.u',
        'updated_at' => 'datetime:Y-m-d H:i:s.u',
    ];




    protected function key(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => strtolower($value),
            set: fn ($value) => strtolower($value),
        );
    }

    protected function value(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => ($value),
            set: fn ($value) => ($value),
        );
    }





    public function user() {
        return $this->belongsTo(\App\Models\User::class, 'user_id', 'id');
    }

}
