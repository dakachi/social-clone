<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Modules\AdminPlans\Models\Plans;
use Illuminate\Notifications\Notifiable;
use Modules\AdminUsers\Models\Teams;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = [];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function sendPasswordResetNotification($token)
    {
        $resetUrl = url('auth/recovery-password') . '?token=' . $token . '&email=' . urlencode($this->email);

        \MailSender::sendByTemplate('forgot_password', $this->email, [
            'fullname'   => $this->fullname,
            'reset_url'  => $resetUrl
        ]);
    }

    public function plan()
    {
        return $this->belongsTo(Plans::class, 'plan_id');
    }

    public function ownedTeams()
    {
        return $this->hasMany(Teams::class, 'owner', 'id');
    }

    public function teams()
    {
        return $this->belongsToMany(Teams::class, 'team_members', 'uid', 'team_id')
            ->withPivot('status', 'permissions', 'pending');
    }

    public function paymentSubscriptions()
    {
        return $this->hasMany(\Modules\AdminPaymentSubscriptions\Models\PaymentSubscription::class, 'uid', 'id');
    }
}
