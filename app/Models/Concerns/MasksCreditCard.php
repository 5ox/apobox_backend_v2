<?php

namespace App\Models\Concerns;

trait MasksCreditCard
{
    /**
     * Boot the MasksCreditCard trait.
     *
     * On saving, masks the cc_number to the last 4 digits and strips
     * the CVV before the record is persisted.
     */
    public static function bootMasksCreditCard(): void
    {
        static::saving(function ($model) {
            // Mask cc_number to last 4 digits
            if ($model->isDirty('cc_number') && ! empty($model->cc_number)) {
                $number = preg_replace('/\D/', '', $model->cc_number);
                if (strlen($number) > 4) {
                    $model->cc_number = str_repeat('X', strlen($number) - 4) . substr($number, -4);
                }
            }

            // Strip CVV before save -- never store the raw CVV
            if ($model->isDirty('cc_cvv') && ! empty($model->cc_cvv)) {
                $model->cc_cvv = '';
            }
        });
    }
}
