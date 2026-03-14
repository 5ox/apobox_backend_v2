<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class EmailTemplate extends Model
{
    protected $primaryKey = 'key';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['key', 'subject', 'body'];

    protected const CACHE_KEY = 'email_templates';
    protected const CACHE_TTL = 3600;

    /**
     * Registry of all editable email templates with metadata.
     */
    public const TEMPLATES = [
        'welcome' => [
            'label' => 'Welcome',
            'description' => 'Sent when a new customer completes signup',
            'default_subject' => 'Welcome to APO Box Shipping',
            'variables' => ['firstName', 'lastName', 'billingId', 'address', 'almostFinishedUrl'],
        ],
        'forgot_password' => [
            'label' => 'Forgot Password',
            'description' => 'Sent when a customer requests a password reset',
            'default_subject' => 'Your Password Reset Link',
            'variables' => ['customerName', 'url'],
        ],
        'confirm_close' => [
            'label' => 'Confirm Close Account',
            'description' => 'Sent when a customer requests to close their account',
            'default_subject' => 'Confirm Closing Your Account',
            'variables' => ['customerName', 'url'],
        ],
        'order_shipped' => [
            'label' => 'Order Shipped',
            'description' => 'Sent when an order is marked as shipped',
            'default_subject' => 'APO Box Order #{{orderId}} - Shipped',
            'variables' => ['firstName', 'lastName', 'orderId', 'outboundTracking', 'inboundTracking', 'trackingUrl'],
        ],
        'order_status_update' => [
            'label' => 'Order Status Update',
            'description' => 'Sent when an order status changes',
            'default_subject' => 'APO Box Order #{{orderId}} - Status Update',
            'variables' => ['firstName', 'lastName', 'orderId', 'status', 'comments'],
        ],
        'order_failed_payment' => [
            'label' => 'Order Failed Payment',
            'description' => 'Sent when automatic payment fails for an order',
            'default_subject' => 'APO Box Order #{{orderId}} - Awaiting Payment',
            'variables' => ['orderId', 'payUrl', 'comments'],
        ],
        'awaiting_payment_alert' => [
            'label' => 'Awaiting Payment Alert',
            'description' => 'Sent to remind customers of packages awaiting payment',
            'default_subject' => 'APO Box Account - Package Awaiting Payment',
            'variables' => ['customerName', 'orderId', 'payUrl', 'comments'],
        ],
        'customer_card_expiring' => [
            'label' => 'Credit Card Expiring',
            'description' => 'Sent when a customer\'s credit card is about to expire',
            'default_subject' => 'APO Box Account - Credit Card Expiring',
            'variables' => ['firstName', 'lastName'],
        ],
        'customer_card_expired' => [
            'label' => 'Credit Card Expired',
            'description' => 'Sent when a customer\'s credit card has expired',
            'default_subject' => 'APO Box Account - Credit Card Expired',
            'variables' => ['customerName', 'updatePaymentUrl'],
        ],
        'partial_signup_alert' => [
            'label' => 'Partial Signup Alert',
            'description' => 'Sent to remind customers to complete registration',
            'default_subject' => 'APO Box Account - Complete Your Registration',
            'variables' => ['customerName', 'addAddressUrl'],
        ],
        'blank' => [
            'label' => 'Blank Message',
            'description' => 'Generic message with custom body (used for one-off emails)',
            'default_subject' => '(Dynamic)',
            'variables' => ['name', 'body'],
        ],
        'manager_message' => [
            'label' => 'Manager Message',
            'description' => 'Custom message sent by a manager',
            'default_subject' => '(Dynamic)',
            'variables' => ['message'],
        ],
    ];

    public static function getBody(string $key): ?string
    {
        $templates = static::allCached();
        return $templates[$key]['body'] ?? null;
    }

    public static function getSubject(string $key): ?string
    {
        $templates = static::allCached();
        return $templates[$key]['subject'] ?? null;
    }

    public static function allCached(): array
    {
        return Cache::remember(static::CACHE_KEY, static::CACHE_TTL, function () {
            try {
                return static::all()->keyBy('key')->map(fn ($t) => [
                    'subject' => $t->subject,
                    'body' => $t->body,
                ])->toArray();
            } catch (\Exception $e) {
                return [];
            }
        });
    }

    public static function clearCache(): void
    {
        Cache::forget(static::CACHE_KEY);
    }
}
