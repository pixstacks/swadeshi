<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRequestFiltersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('request_filters', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('request_id');
            $table->unsignedBigInteger('provider_id');
            $table->integer('status')->default(0);
            $table->timestamps();

            $table->foreign('request_id')
                ->references('id')
                ->on('user_requests')->onDelete('cascade');

            $table->foreign('provider_id')
                ->references('id')
                ->on('providers')->onDelete('cascade');

        });

        Schema::create('request_current_providers', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedBigInteger('request_id');
            $table->unsignedBigInteger('user_id');
            $table->integer('status')->default(0);
            $table->timestamps();

            $table->foreign('request_id')
                ->references('id')
                ->on('user_requests')->onDelete('cascade');

            $table->foreign('user_id')
                ->references('id')
                ->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('request_filters');
        Schema::dropIfExists('request_current_providers');
    }
}
