<?php
namespace Rguj\Laracore\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Rguj\Laracore\Model\BaseModel;

use App\Core\Traits\SpatieLogsActivity;
use Illuminate\Support\Facades\Storage;

class BMUserInfo extends BaseModel
{
    use SpatieLogsActivity;

    //protected $connection = '';
    protected $table = 'user_info';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'integer';

    public $timestamps = true;
    protected $dateFormat  = 'Y-m-d H:i:s.u';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    protected $fillable = [
        'user_id',
        'avatar',
        'company',
        'phone',
        'website',
        'country',
        'language',
        'timezone',
        'currency',
        'communication',
        'marketing',
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



    /**
     * Prepare proper error handling for url attribute
     *
     * @return string
     */
    public function getAvatarUrlAttribute()
    {
        // if file avatar exist in storage folder
        // $avatar = public_path(Storage::url($this->avatar));
        $avatar = public_path(Storage::url($this->avatar));
        if (is_file($avatar) && file_exists($avatar)) {
            // get avatar url from storage
            return Storage::url($this->avatar);
        }

        // check if the avatar is an external url, eg. image from google
        if (filter_var($this->avatar, FILTER_VALIDATE_URL)) {
            return $this->avatar;
        }

        // no avatar, return blank avatar
        $img_blank = asset(theme()->getMediaUrlPath().'avatars/blank'.(theme()->isDarkMode() ? '-dark' : '').'.png');

        return $img_blank;
    }
    // public function getAvatarUrlAttribute()
    // {
    //     // if file avatar exist in storage folder
    //     $avatar = public_path(Storage::url($this->avatar));
    //     if (is_file($avatar) && file_exists($avatar)) {
    //         // get avatar url from storage
    //         return Storage::url($this->avatar);
    //     }

    //     // check if the avatar is an external url, eg. image from google
    //     if (filter_var($this->avatar, FILTER_VALIDATE_URL)) {
    //         return $this->avatar;
    //     }

    //     // no avatar, return blank avatar
    //     $img_blank = asset(theme()->getMediaUrlPath().'avatars/blank'.(theme()->isDarkMode() ? '-dark' : '').'.png');

    //     return $img_blank;
    // }

    /**
     * User info relation to user model
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Unserialize values by default
     *
     * @param $value
     *
     * @return mixed|null
     */
    public function getCommunicationAttribute($value)
    {
        // test to un-serialize value and return as array
        $data = @unserialize($value);
        if ($data !== false) {
            return $data;
        } else {
            return null;
        }
    }
}
