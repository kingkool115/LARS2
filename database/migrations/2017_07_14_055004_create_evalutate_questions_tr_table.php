<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEvalutateQuestionsTrTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('evaluate_questions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('student_id');
            $table->integer('question_id');
            $table->string('session_id');
            $table->boolean('is_text_response');
            $table->string('answer');
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
        Schema::dropIfExists('evaluate_questions_tr');
    }
}
