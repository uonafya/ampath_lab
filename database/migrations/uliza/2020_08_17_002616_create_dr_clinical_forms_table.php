<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDrClinicalFormsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dr_clinical_forms', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('facility_id')->unsigned()->index();
            $table->string('cccno');
            $table->date('reporting_date');
            $table->date('artstart_date');
            $table->string('gender');
            $table->integer('curr_weight');
            $table->integer('height');
            $table->string('clinician_name');
            $table->string('facility_email');
            $table->string('facility_tel');
            $table->tinyInteger('primary_reason');
            $table->text('clinical_eval')->nullable();
            $table->tinyInteger('no_adherance_counseling')->nullable();
            $table->tinyInteger('no_homevisits')->nullable();
            $table->text('support_structures')->nullable();
            $table->text('adherence_concerns');
            $table->tinyInteger('no_dotsdone')->nullable();
            $table->text('likely_rootcauses')->nullable();
            $table->text('inadequate_dosing')->nullable();
            $table->text('drug_interactions')->nullable();
            $table->text('food_interactions')->nullable();
            $table->text('impaired_absorption')->nullable();
            $table->text('treatment_interruptions')->nullable();
            $table->text('drt_testing')->nullable();
            $table->text('mdt_discussions')->nullable();
            $table->text('mdt_members')->nullable();
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
        Schema::dropIfExists('dr_clinical_forms');
    }
}
