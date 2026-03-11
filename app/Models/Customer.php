<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Models\Concerns\MasksCreditCard;
use App\Models\Concerns\Searchable;
use App\Services\CreditCardService;

class Customer extends Authenticatable
{
    use MasksCreditCard, Searchable;

    protected $table = 'customers';
    protected $primaryKey = 'customers_id';
    public $timestamps = true;

    protected $fillable = [
        'billing_id',
        'customers_gender',
        'customers_firstname',
        'customers_lastname',
        'customers_dob',
        'customers_email_address',
        'customers_default_address_id',
        'customers_shipping_address_id',
        'customers_emergency_address_id',
        'customers_telephone',
        'customers_fax',
        'customers_password',
        'customers_newsletter',
        'customers_referral_id',
        'customers_referral_points',
        'cc_firstname',
        'cc_lastname',
        'cc_number',
        'cc_number_encrypted',
        'cc_expires_month',
        'cc_expires_year',
        'cc_cvv',
        'card_token',
        'insurance_amount',
        'insurance_fee',
        'backup_email_address',
        'customers_referral_referred',
        'referral_status',
        'default_postal_type',
        'billing_type',
        'invoicing_authorized',
        'editable_max_amount',
        'is_active',
    ];

    protected $hidden = [
        'customers_password',
        'cc_number_encrypted',
        'cc_cvv',
        'card_token',
    ];

    protected $casts = [
        'customers_dob' => 'datetime',
        'insurance_amount' => 'decimal:2',
        'insurance_fee' => 'decimal:2',
        'editable_max_amount' => 'decimal:2',
        'is_active' => 'boolean',
        'invoicing_authorized' => 'boolean',
    ];

    // ---------------------------------------------------------------
    // Auth overrides for non-standard columns
    // ---------------------------------------------------------------

    public function getAuthIdentifierName()
    {
        return 'customers_id';
    }

    public function getAuthPassword()
    {
        return $this->customers_password;
    }

    // ---------------------------------------------------------------
    // Relationships
    // ---------------------------------------------------------------

    public function addresses()
    {
        return $this->hasMany(Address::class, 'customers_id', 'customers_id');
    }

    public function shippingAddresses()
    {
        return $this->hasMany(ShippingAddress::class, 'customers_id', 'customers_id');
    }

    public function defaultAddress()
    {
        return $this->belongsTo(Address::class, 'customers_default_address_id', 'address_book_id');
    }

    public function shippingAddress()
    {
        return $this->belongsTo(Address::class, 'customers_shipping_address_id', 'address_book_id');
    }

    public function emergencyAddress()
    {
        return $this->belongsTo(Address::class, 'customers_emergency_address_id', 'address_book_id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'customers_id', 'customers_id');
    }

    public function authorizedNames()
    {
        return $this->hasMany(AuthorizedName::class, 'customers_id', 'customers_id');
    }

    public function passwordRequests()
    {
        return $this->hasMany(PasswordRequest::class, 'customer_id', 'customers_id');
    }

    public function reminders()
    {
        return $this->hasMany(CustomerReminder::class, 'customers_id', 'customers_id');
    }

    public function info()
    {
        return $this->hasOne(CustomersInfo::class, 'customers_info_id', 'customers_id');
    }

    // ---------------------------------------------------------------
    // Accessors
    // ---------------------------------------------------------------

    public function getFullNameAttribute(): string
    {
        return trim($this->customers_firstname . ' ' . $this->customers_lastname);
    }

    public function getCcExpiresDateAttribute(): ?string
    {
        if (empty($this->cc_expires_month) || empty($this->cc_expires_year)) {
            return null;
        }

        return $this->cc_expires_month . '/20' . $this->cc_expires_year;
    }

    // ---------------------------------------------------------------
    // Scopes
    // ---------------------------------------------------------------

    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }

    public function scopeIncompleteBilling($query)
    {
        return $query->where('billing_id', '')->orWhereNull('billing_id');
    }

    public function scopeExpiringCards($query, int $months = 1)
    {
        $now = now();

        return $query->active()
            ->where('cc_expires_year', '!=', '')
            ->where('cc_expires_month', '!=', '')
            ->whereRaw(
                "STR_TO_DATE(CONCAT('20', cc_expires_year, '-', cc_expires_month, '-01'), '%Y-%m-%d') <= ?",
                [$now->addMonths($months)]
            );
    }

    public function scopePartialSignups($query)
    {
        return $query->active()->incompleteBilling();
    }

    // ---------------------------------------------------------------
    // Credit Card Encryption / Decryption (OpenSSL)
    // ---------------------------------------------------------------

    public function encryptCreditCard(string $number): string
    {
        return app(CreditCardService::class)->encrypt($number);
    }

    public function decryptCreditCard(): string
    {
        if (empty($this->cc_number_encrypted)) {
            return '';
        }

        return app(CreditCardService::class)->decrypt($this->cc_number_encrypted);
    }

    // ---------------------------------------------------------------
    // Business Logic
    // ---------------------------------------------------------------

    /**
     * Generate a unique billing ID: 2-letter initials + 4 random digits.
     */
    public static function newBillingId(string $firstname, string $lastname): string
    {
        $prefix = strtoupper(substr($firstname, 0, 1) . substr($lastname, 0, 1));

        do {
            $id = $prefix . str_pad(random_int(0, 9999), 4, '0', STR_PAD_LEFT);
        } while (static::where('billing_id', $id)->exists());

        return $id;
    }

    /**
     * Close the customer account: deactivate and wipe credit card data.
     */
    public function closeAccount(): bool
    {
        return $this->update([
            'is_active' => false,
            'cc_number' => '',
            'cc_number_encrypted' => '',
            'cc_expires_month' => '',
            'cc_expires_year' => '',
            'cc_cvv' => '',
            'card_token' => '',
            'cc_firstname' => '',
            'cc_lastname' => '',
        ]);
    }

    // ---------------------------------------------------------------
    // Searchable implementation
    // ---------------------------------------------------------------

    public function indexData(): string
    {
        $parts = [
            $this->billing_id,
            $this->customers_firstname,
            $this->customers_lastname,
            $this->customers_email_address,
            $this->customers_telephone,
            $this->backup_email_address,
        ];

        foreach ($this->authorizedNames as $name) {
            $parts[] = $name->authorized_firstname . ' ' . $name->authorized_lastname;
        }

        return implode(' ', array_filter($parts));
    }
}
