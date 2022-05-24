<?php

namespace Rguj\Laracore\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

use Rguj\Laracore\Model\BaseModel;

class Menu extends BaseModel
{
    use HasFactory;

    protected $connection = 'hris';
    protected $table = 'ac_menu';
    protected $primaryKey = 'id';
    protected $keyType = 'integer';
    public $incrementing = true;

    public $timestamps = true;
    protected $dateFormat  = 'Y-m-d H:i:s.u';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    
    protected $fillable = [
        'value',
    ];

    protected $hidden = [
        
    ];

    protected $attributes = [
        
    ];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s.u',
        'updated_at' => 'datetime:Y-m-d H:i:s.u',
    ];




    protected function value(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => json_decode($value, true),
            set: fn ($value) => json_encode($value),
        );
    }

    protected function isMiddleware(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value === 1 ? 'Yes' : 'No',
            set: fn ($value) => $value === 'Yes' ? 1 : 0,
        );
    }
    
    protected function isRole(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value === 1 ? 'Yes' : 'No',
            set: fn ($value) => $value === 'Yes' ? 1 : 0,
        );
    }



    /*protected function id(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => json_decode($value, true),
            set: fn ($value) => json_encode($value),
        );
    }*/


    public function role() {
        return $this->hasOne(\App\Models\Role::class, 'id', 'role_id');
    }


}
