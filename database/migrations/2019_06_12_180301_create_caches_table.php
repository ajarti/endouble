<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCachesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('caches', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('source_id');
            $table->mediumInteger('item_index');
            $table->json('item');
            $table->date('dated_at');
            $table->timestamps();

            // Indexes.
            $table->index('dated_at');
            $table->index('source_id');
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('caches');
    }
}
