<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEvalutateQuestionsMcTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('evaluate_questions_mc', function (Blueprint $table) {
            $table->string('student_id');
            $table->integer('question_id');
            $table->string('session_id');
            $table->string('answer_ids');
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
        //
        Schema::dropIfExists('evaluate_questions_mc');
    }
}
