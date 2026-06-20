<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pelanggans', function (Blueprint $table) {
            if (!Schema::hasColumn('pelanggans', 'rajaongkir_destination_id')) {
                $table->string('rajaongkir_destination_id', 50)->nullable()->after('alamat_kode_pos');
            }

            if (!Schema::hasColumn('pelanggans', 'rajaongkir_destination_label')) {
                $table->string('rajaongkir_destination_label')->nullable()->after('rajaongkir_destination_id');
            }
        });

        Schema::table('pesanans', function (Blueprint $table) {
            if (!Schema::hasColumn('pesanans', 'shipping_courier')) {
                $table->string('shipping_courier', 80)->nullable()->after('ongkir');
            }

            if (!Schema::hasColumn('pesanans', 'shipping_service')) {
                $table->string('shipping_service', 120)->nullable()->after('shipping_courier');
            }

            if (!Schema::hasColumn('pesanans', 'shipping_etd')) {
                $table->string('shipping_etd', 120)->nullable()->after('shipping_service');
            }

            if (!Schema::hasColumn('pesanans', 'shipping_destination_id')) {
                $table->string('shipping_destination_id', 50)->nullable()->after('shipping_etd');
            }

            if (!Schema::hasColumn('pesanans', 'shipping_destination_label')) {
                $table->string('shipping_destination_label')->nullable()->after('shipping_destination_id');
            }

            if (!Schema::hasColumn('pesanans', 'shipping_weight')) {
                $table->unsignedInteger('shipping_weight')->nullable()->after('shipping_destination_label');
            }

            if (!Schema::hasColumn('pesanans', 'customer_confirmed_at')) {
                $table->timestamp('customer_confirmed_at')->nullable()->after('pembayaran_terkonfirmasi_at');
            }

            if (!Schema::hasColumn('pesanans', 'customer_confirmed_by')) {
                $table->unsignedBigInteger('customer_confirmed_by')->nullable()->after('customer_confirmed_at');
            }

            if (!Schema::hasColumn('pesanans', 'testimonial_submitted_at')) {
                $table->timestamp('testimonial_submitted_at')->nullable()->after('customer_confirmed_by');
            }
        });

        Schema::table('testimonis', function (Blueprint $table) {
            if (!Schema::hasColumn('testimonis', 'transaksi_type')) {
                $table->string('transaksi_type')->nullable()->after('pelanggan_id');
            }

            if (!Schema::hasColumn('testimonis', 'transaksi_id')) {
                $table->unsignedBigInteger('transaksi_id')->nullable()->after('transaksi_type');
            }
        });
    }

    public function down(): void
    {
        Schema::table('testimonis', function (Blueprint $table) {
            if (Schema::hasColumn('testimonis', 'transaksi_id')) {
                $table->dropColumn('transaksi_id');
            }

            if (Schema::hasColumn('testimonis', 'transaksi_type')) {
                $table->dropColumn('transaksi_type');
            }
        });

        Schema::table('pesanans', function (Blueprint $table) {
            $columns = array_filter([
                Schema::hasColumn('pesanans', 'shipping_courier') ? 'shipping_courier' : null,
                Schema::hasColumn('pesanans', 'shipping_service') ? 'shipping_service' : null,
                Schema::hasColumn('pesanans', 'shipping_etd') ? 'shipping_etd' : null,
                Schema::hasColumn('pesanans', 'shipping_destination_id') ? 'shipping_destination_id' : null,
                Schema::hasColumn('pesanans', 'shipping_destination_label') ? 'shipping_destination_label' : null,
                Schema::hasColumn('pesanans', 'shipping_weight') ? 'shipping_weight' : null,
                Schema::hasColumn('pesanans', 'customer_confirmed_at') ? 'customer_confirmed_at' : null,
                Schema::hasColumn('pesanans', 'customer_confirmed_by') ? 'customer_confirmed_by' : null,
                Schema::hasColumn('pesanans', 'testimonial_submitted_at') ? 'testimonial_submitted_at' : null,
            ]);

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });

        Schema::table('pelanggans', function (Blueprint $table) {
            $columns = array_filter([
                Schema::hasColumn('pelanggans', 'rajaongkir_destination_id') ? 'rajaongkir_destination_id' : null,
                Schema::hasColumn('pelanggans', 'rajaongkir_destination_label') ? 'rajaongkir_destination_label' : null,
            ]);

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
