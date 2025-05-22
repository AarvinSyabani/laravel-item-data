<?php

namespace App\Models;

use App\Helpers\EncryptionHelper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'address',
        'phone',
        'email',
        'contact_person',
    ];

    /**
     * Attributes that should be encrypted when stored in the database
     */
    protected $encrypted = [
        'address',
        'phone',
        'email',
        'contact_person',
    ];

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        // Register an observer to encrypt sensitive data before saving
        static::saving(function ($model) {
            foreach ($model->encrypted as $field) {
                if (isset($model->attributes[$field])) {
                    $model->attributes[$field] = EncryptionHelper::encrypt($model->attributes[$field]);
                }
            }
        });
    }

    /**
     * Get the address attribute
     *
     * @param string|null $value
     * @return string|null
     */
    public function getAddressAttribute($value)
    {
        return $value ? EncryptionHelper::decrypt($value) : null;
    }

    /**
     * Get the phone attribute
     *
     * @param string|null $value
     * @return string|null
     */
    public function getPhoneAttribute($value)
    {
        return $value ? EncryptionHelper::decrypt($value) : null;
    }

    /**
     * Get the email attribute
     *
     * @param string|null $value
     * @return string|null
     */
    public function getEmailAttribute($value)
    {
        return $value ? EncryptionHelper::decrypt($value) : null;
    }

    /**
     * Get the contact_person attribute
     *
     * @param string|null $value
     * @return string|null
     */
    public function getContactPersonAttribute($value)
    {
        return $value ? EncryptionHelper::decrypt($value) : null;
    }

    /**
     * Get masked phone number for display purposes
     *
     * @return string
     */
    public function getMaskedPhoneAttribute()
    {
        return $this->phone ? EncryptionHelper::mask($this->phone, 3, 2) : null;
    }

    /**
     * Get masked email for display purposes
     *
     * @return string
     */
    public function getMaskedEmailAttribute()
    {
        if (!$this->email) return null;
        
        $parts = explode('@', $this->email);
        if (count($parts) !== 2) return EncryptionHelper::mask($this->email);
        
        $username = EncryptionHelper::mask($parts[0], 2, 1);
        return $username . '@' . $parts[1];
    }

    public function items(): HasMany
    {
        return $this->hasMany(Item::class);
    }
}
