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
        Schema::create('orders', function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->bigIncrements('orders_id');
            $table->integer('customers_id')->default(0);
            $table->string('customers_name', 64)->default('');
            $table->string('customers_company', 32)->nullable();
            $table->string('customers_street_address', 64)->default('');
            $table->string('customers_suburb', 32)->nullable();
            $table->string('customers_city', 32)->default('');
            $table->string('customers_postcode', 10)->default('');
            $table->string('customers_state', 32)->nullable();
            $table->string('customers_country', 32)->default('');
            $table->string('customers_telephone', 32)->default('');
            $table->string('customers_email_address', 96)->default('');
            $table->integer('customers_address_format_id')->default(0);

            $table->string('delivery_name', 64)->default('');
            $table->string('delivery_company', 32)->nullable();
            $table->string('delivery_street_address', 64)->default('');
            $table->string('delivery_suburb', 32)->nullable();
            $table->string('delivery_city', 32)->default('');
            $table->string('delivery_postcode', 10)->default('');
            $table->string('delivery_state', 32)->nullable();
            $table->string('delivery_country', 32)->default('');
            $table->integer('delivery_address_format_id')->default(0);

            $table->string('billing_name', 64)->default('');
            $table->string('billing_company', 32)->nullable();
            $table->string('billing_street_address', 64)->default('');
            $table->string('billing_suburb', 32)->nullable();
            $table->string('billing_city', 32)->default('');
            $table->string('billing_postcode', 10)->default('');
            $table->string('billing_state', 32)->nullable();
            $table->string('billing_country', 32)->default('');
            $table->integer('billing_address_format_id')->default(0);

            $table->string('payment_method', 32)->default('');
            $table->string('cc_type', 20)->nullable();
            $table->string('cc_owner', 64)->nullable();
            $table->string('cc_number', 32)->nullable();
            $table->string('cc_expires', 4)->nullable();
            $table->string('comments', 255)->nullable();
            $table->dateTime('last_modified')->nullable();
            $table->dateTime('date_purchased')->nullable();
            $table->string('turnaround_sec', 15)->nullable();
            $table->integer('orders_status')->default(0);
            $table->dateTime('orders_date_finished')->nullable();

            $table->string('ups_track_num', 40)->nullable();
            $table->string('usps_track_num', 40)->nullable();
            $table->string('usps_track_num_in', 40)->nullable();
            $table->string('fedex_track_num', 40)->nullable();
            $table->string('fedex_freight_track_num', 40)->nullable();
            $table->string('dhl_track_num', 40)->nullable();

            $table->char('currency', 3)->nullable();
            $table->decimal('currency_value', 14, 6)->nullable();
            $table->decimal('shipping_tax', 7, 4)->default(0.0000);
            $table->tinyInteger('billing_status')->default(0);
            $table->tinyInteger('qbi_imported')->unsigned()->default(0);

            $table->string('width', 10)->nullable();
            $table->string('length', 10)->nullable();
            $table->string('depth', 10)->nullable();
            $table->string('weight_oz', 10)->nullable();
            $table->string('mail_class', 15)->nullable();
            $table->string('package_type', 20)->nullable();
            $table->string('NonMachinable', 10)->nullable();
            $table->string('OversizeRate', 10)->nullable();
            $table->string('BalloonRate', 10)->nullable();
            $table->string('package_flow', 5)->nullable();
            $table->string('shipped_from', 40)->nullable();
            $table->string('insurance_coverage', 10)->nullable();
            $table->string('warehouse', 15)->nullable();
            $table->string('postage_id', 25)->default('');
            $table->string('trans_id', 25)->default('');
            $table->enum('moved_to_invoice', ['0', '1'])->default('0');

            $table->index('customers_id', 'idx_customers_id');
            $table->index('orders_status', 'idx_orders_status');
            $table->index('qbi_imported', 'idx_qbi_imported');
            $table->foreign('customers_id')
                  ->references('customers_id')
                  ->on('customers');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
