<?php

namespace Modules\AdminUsers\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;

class TeamMembers extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $table = 'team_members';

    // You can use guarded or fillable. 
    // Using guarded as in your code means all attributes are mass assignable EXCEPT those in the array (currently none).
    protected $guarded = [];

    // If you want to use fillable instead, you could do:
    // protected $fillable = ['id_secure', 'uid', 'team_id', 'permissions', 'pending', 'status', 'invite_token'];

    /**
     * Get the team that this member belongs to.
     */
    public function team()
    {
        return $this->belongsTo(Teams::class, 'team_id');
    }

    /**
     * Get the user that this team member refers to.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'uid');
    }

    /**
     * Get permissions as array.
     */
    public function getPermissionsAttribute($value)
    {
        return $value ? json_decode($value, true) : [];
    }

    /**
     * Set permissions as JSON.
     */
    public function setPermissionsAttribute($value)
    {
        $this->attributes['permissions'] = is_array($value) ? json_encode($value) : $value;
    }
}
