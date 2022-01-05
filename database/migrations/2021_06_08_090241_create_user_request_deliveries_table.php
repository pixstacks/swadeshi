<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserRequestDeliveriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_request_deliveries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('request_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('product_type_id')->nullable();
            $table->unsignedBigInteger('provider_id')->nullable();
            $table->string('comments')->nullable();
            $table->string('image')->nullable();
            $table->integer('otp')->default(0);
            $table->float('weight',10,2)->default(0);
            $table->enum('status', ['open','pending', 'delivered'])->default('pending');
            $table->string('address')->nullable();
            $table->double('latitude', 15, 8)->nullable();
            $table->double('longitude', 15, 8)->nullable();
            
            $table->foreign('request_id')
                ->references('id')
                ->on('user_requests')->onDelete('cascade');

                $table->foreign('product_type_id')
                ->references('id')
                ->on('product_types')->onDelete('cascade');    
                
            $table->foreign('user_id')
                ->references('id')
                ->on('users')->onDelete('cascade');
                
            $table->foreign('provider_id')
                ->references('id')
                ->on('providers')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_request_deliveries');
    }
}
