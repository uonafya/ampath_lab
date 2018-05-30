<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTaqmanprocurementsTable extends Migration
{
    public $main = ['ending','wasted','issued','request','pos'];
    public $sub = ['qualkit','spexagent','ampinput','ampflapless','ampktips','ampwash','ktubes','consumables'];
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('taqmanprocurements', function (Blueprint $table) {
            $table->increments('id');
            $table->tinyInteger('month')->unsigned()->index();
            $table->integer('year')->unsigned()->index();
            $table->tinyInteger('testtype')->unsigned()->index();
            $table->integer('received')->unsigned()->default(0)->nullable();
            $table->integer('tests')->unsigned()->default(0)->nullable();
            foreach ($this->main as $key => $value) {
                foreach ($this->sub as $subkey => $subvalue) {
                    $table->integer($value.$subvalue)->unsigned()->default(0)->nullable();
                }
            }
            $table->date('datesubmitted');
            $table->bigInteger('submittedBy');
            $table->integer('lab_id')->unsigned()->index();
            // $table->tinyInteger('synchronized')->unsigned()->nullable();
            $table->date('datesynchronized');
            $table->string('comments', 50)->nullable();
            $table->string('issuedcomments', 100)->nullable();
            $table->bigInteger('approve')->unsigned()->index();
            $table->string('disapproverreason', 100)->nullable();
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
        Schema::dropIfExists('taqmanprocurements');
    }
}
