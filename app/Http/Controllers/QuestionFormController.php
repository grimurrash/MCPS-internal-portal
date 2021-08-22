<?php

namespace App\Http\Controllers;

use App\Helper\ObsceneCensorRus;
use App\Models\QuestionAnswerForm;
use Illuminate\Http\Request;

class QuestionFormController extends Controller
{
    public function answerShow($id)
    {
        $question = [
            1 => [
                'question_id' => 1,
                'question_text' => 'Как тебя зовут?'
            ],
            2 => [
                'question_id' => 2,
                'question_text' => 'Сколько тебе лет?'
            ],
        ];

        return view('questions.answers.show', $question[$id]);
    }

    public function answerEnd(Request $request, $id)
    {
        $answer = $request->input('answer');
        if (!ObsceneCensorRus::isAllowed($answer)) return view('questions.answers.end');

        QuestionAnswerForm::query()->updateOrInsert([
            'question_id' => $id,
            'ip_address' => $request->ip(),
        ], [
            'answer' => $answer
        ]);
        return view('questions.answers.end');
    }

    public function answerText($id): string
    {
        $allAnswer = QuestionAnswerForm::query()->where('question_id', $id)->get('answer');
        $text = "";
        foreach ($allAnswer as $item) {
            $text .= $item->answer . "\n";
        }
        return $text;
    }

    public function askShow()
    {
        return view('questions.ask.show');
    }

    public function askEnd()
    {
        return view('questions.answers.end');
    }
}
