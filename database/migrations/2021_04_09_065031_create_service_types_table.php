<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateServiceTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('service_types', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('parent_id')->default(0)->nullable();
            $table->text('name');
            $table->string('image')->nullable();
            $table->string('marker')->nullable();
            $table->integer('fixed')->nullable();
            $table->float('price', 10, 2)->default(0);
            $table->float('type_price', 10, 2)->default(0);
            $table->enum('calculator', ['DEFAULT', 'FIXED', 'HOUR', 'DAILY', 'WEIGHT', 'ESTIMATE', 'PSQFT'])->default('DEFAULT');
            $table->text('description')->nullable();
            $table->integer('status')->default(0);

            // $table->foreign('parent_id')
            //     ->references('id')
            //     ->on('service_types')->onDelete('cascade');
            // Add Foreign Key Constraint To Parent Id.
            $table->softDeletes();
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
        Schema::dropIfExists('service_types');
    }
}
