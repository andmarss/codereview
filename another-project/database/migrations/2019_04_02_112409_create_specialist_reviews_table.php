<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSpecialistReviewsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('specialist_reviews', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('specialist_id')->unsigned();
            $table->string('author')->nullable();
            $table->text('text')->nullable();
            $table->enum('rate', [1,2,3,4,5])->nullable();
            $table->text('answer_text')->nullable();
            $table->date('answered_at')->nullable();
            $table->timestamps();
        });

        Schema::table('specialist_reviews', function (Blueprint $table){
            $table->foreign('specialist_id')
                ->references('id')
                ->on('specialists')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('specialist_reviews');
    }
}
