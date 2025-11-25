<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, HasRoles, Notifiable, TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
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
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

    /**
     * Get the transactions created by the user.
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Get the transactions approved by the user.
     */
    public function approvedTransactions()
    {
        return $this->hasMany(Transaction::class, 'approved_by');
    }

    /**
     * Get the approval requests submitted by the user.
     */
    public function submittedApprovals()
    {
        return $this->hasMany(Approval::class, 'submitted_by');
    }

    /**
     * Get the approvals reviewed by the user.
     */
    public function reviewedApprovals()
    {
        return $this->hasMany(Approval::class, 'reviewed_by');
    }

    /**
     * Get the user's app notifications.
     */
    public function appNotifications()
    {
        return $this->hasMany(AppNotification::class);
    }

    /**
     * Get the user's unread app notifications count.
     */
    public function unreadNotificationsCount(): int
    {
        return $this->appNotifications()->unread()->count();
    }

    /**
     * Check if user is a Requester role.
     */
    public function isRequester(): bool
    {
        return $this->hasRole('Requester');
    }

    /**
     * Check if user can approve transactions.
     */
    public function canApproveTransactions(): bool
    {
        return $this->can('approve-transactions');
    }
}
