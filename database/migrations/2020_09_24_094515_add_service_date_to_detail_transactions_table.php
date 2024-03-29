<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddServiceDateToDetailTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('detail_transactions', function (Blueprint $table) {
            // TAMBAHKAN FIELD BARU KE DALAM TABLE DETAIL_TRANSACTION
            $table->datetime('start_date')->nullable()->after('laundry_type_id');
            $table->datetime('end_date')->nullable()->after('start_date');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('detail_transactions', function (Blueprint $table) {
            $table->dropColumn('start_date');
            $table->dropColumn('end_date');
        });
    }
}
