<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLabPerformanceTrackersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lab_performance_trackers', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('lab_id')->unsigned()->index();
            $table->tinyInteger('month')->unsigned()->index();
            $table->integer('year')->unsigned()->index();
            // $table->tinyInteger('submitted')->unsigned()->index();
            // $table->tinyInteger('eamilsent')->unsigned()->index();
            $table->date('dateemailsent')->nullable();
            $table->tinyInteger('testtype')->unsigned()->index();
            $table->tinyInteger('sampletype')->unsigned()->nullable();
            $table->integer('received')->unsigned()->nullable();
            $table->integer('rejected')->unsigned()->nullable();
            $table->integer('loggedin')->unsigned()->nullable();
            $table->integer('notlogged')->unsigned()->nullable();
            $table->integer('tested')->unsigned()->nullable();
            $table->string('reasonforbacklog', 100)->nullable();
            $table->date('datesubmitted');
            $table->bigInteger('submittedBy');
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
        Schema::dropIfExists('lab_performance_trackers');
    }
}
