<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->increments('customers_id');
            $table->string('billing_id', 8)->default('');
            $table->string('customers_gender', 1)->default('');
            $table->string('customers_firstname', 32)->default('');
            $table->string('customers_lastname', 32)->default('');
            $table->dateTime('customers_dob')->nullable();
            $table->string('customers_email_address', 96)->default('');
            $table->integer('customers_default_address_id')->nullable();
            $table->integer('customers_shipping_address_id')->default(0);
            $table->integer('customers_emergency_address_id')->default(0);
            $table->string('customers_telephone', 32)->default('');
            $table->string('customers_fax', 32)->nullable();
            $table->string('customers_password', 255)->default('');
            $table->string('customers_newsletter', 1)->nullable();
            $table->string('customers_referral_id', 64)->default('');
            $table->integer('customers_referral_points')->default(0);
            $table->string('cc_firstname', 64)->default('');
            $table->string('cc_lastname', 64)->default('');
            $table->string('cc_number', 32)->default('');
            $table->text('cc_number_encrypted');
            $table->string('cc_expires_month', 2)->default('');
            $table->string('cc_expires_year', 2)->default('');
            $table->text('cc_cvv');
            $table->string('card_token', 32)->default('');
            $table->decimal('insurance_amount', 15, 2)->default(50.00);
            $table->decimal('insurance_fee', 15, 2)->default(1.65);
            $table->string('backup_email_address', 255)->default('');
            $table->string('customers_referral_referred', 64)->default('');
            $table->integer('referral_status')->default(0);
            $table->string('default_postal_type', 64)->default('apobox_direct');
            $table->string('billing_type', 15)->default('cc');
            $table->tinyInteger('invoicing_authorized')->default(1);
            $table->decimal('editable_max_amount', 15, 2)->nullable();
            $table->tinyInteger('is_active')->default(1);
            $table->timestamps();

            $table->index('billing_id', 'idx_billing_id');
            $table->index('customers_email_address', 'idx_email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
