<?php
namespace Rguj\Laracore\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Rguj\Laracore\Model\BaseModel;

class BMProgram extends BaseModel
{
    use HasFactory;

    //protected $connection = '';
    //protected $table = '';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'integer';

    public $timestamps = true;
    protected $dateFormat  = 'Y-m-d H:i:s.u';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    
    protected $fillable = [
        'code',
        'program',
		'is_undergrad',
		'is_postgrad',
		'is_master',
		'is_doctor',
		'is_junior_hs',
		'is_senior_hs',
		'is_offered',
		'is_open_admission',
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


	public function rstaffs() {
        return $this->hasMany(\App\Models\RStaff::class, 'id', 'program_id');
    }


}
