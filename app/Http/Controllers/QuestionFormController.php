<?php

namespace App\Http\Controllers;

use App\Helper\ObsceneCensorRus;
use App\Models\QuestionAnswerForm;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Revolution\Google\Sheets\Facades\Sheets;

class QuestionFormController extends Controller
{
    private $testMode = true;
    private $questionSpreadsheetId = '1wD-ZgKnpssUUVd6-86omSAIfsErS_l0gLnZL-6sUl4o';

    public function clearAnswers($id)
    {
        QuestionAnswerForm::query()->where('question_id', $id)->delete();
    }

    public function answerShow($id)
    {
        $question = [
            1 => [
                'question_id' => 1,
                'question_text' => 'Какие ценности в воспитании вы бы определили в качестве базовых?'
            ],
            2 => [
                'question_id' => 2,
                'question_text' => 'Какие процессы в школе должны работать на достижение результата (модели выпускника)?'
            ],
            3 => [
                'question_id' => 3,
                'question_text' => 'Какие направления можно развивать в ШСК (кроме спорта)?'
            ],
            4 => [
                'question_id' => 4,
                'question_text' => 'Какие виды спорта/двигательной активности наиболее привлекательны для девочек/девушек в школе?'
            ],
            5 => [
                'question_id' => 5,
                'question_text' => 'Какие форматы проведения соревнований по шахматам наиболее востребованы среди московских школ: личные, командные, лично-командные?'
            ],
            6 => [
                'question_id' => 6,
                'question_text' => 'Клубы по каким видам спорта стали бы наиболее востребованы для сотрудников ОО?'
            ],
            7 => [
                'question_id' => 7,
                'question_text' => 'Какие на ваш взгляд разделы следует предусмотреть для оказания дополнительной поддержки в воспитательной работе проекта «Классный руководитель онлайн»?'
            ],
            8 => [
                'question_id' => 8,
                'question_text' => 'Какие темы мероприятий проекта «Мастерская классных руководителей» были бы для Вас актуальны и полезны?'
            ],
            9 => [
                'question_id' => 9,
                'question_text' => 'Мастер-класс на какую тему Вы готовы провести в рамках проекта «Мастерская классных руководителей»?'
            ],
            10 => [
                'question_id' => 10,
                'question_text' => 'Какие темы мероприятий проекта «Классный руководитель в московском образовании» необходимы для Вашего развития в ближайшей перспективе?'
            ],
            11 => [
                'question_id' => 11,
                'question_text' => 'Чем киберспорт полезен для школьников?'
            ],
            12 => [
                'question_id' => 12,
                'question_text' => 'Как вы думаете, в какую компьютерную игру играет большинство школьников?'
            ],
            13 => [
                'question_id' => 13,
                'question_text' => 'Какие темы вы бы хотели обсудить на следующем мероприятии?'
            ],
            14 => [
                'question_id' => 14,
                'question_text' => 'Напишите примеры мероприятий  в рамках модуля «Ключевые общешкольные дела»'
            ]
        ];
        return view('questions.answers.show', $question[$id]);
    }

    public function answerEnd(Request $request, $id)
    {
        $answer = $request->input('answer');
        if (!ObsceneCensorRus::isAllowed($answer)) return view('questions.answers.end');

        if ($this->testMode) {
            QuestionAnswerForm::query()->create([
                'question_id' => $id,
                'ip_address' => $request->ip(),
                'answer' => $answer
            ]);
        } else {
            QuestionAnswerForm::query()->updateOrInsert([
                'question_id' => $id,
                'ip_address' => $request->ip(),
            ], [
                'answer' => $answer
            ]);
        }

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

    public function askShow(Request $request)
    {
        return view('questions.ask.show', ['event' => $request->input('event')]);
    }

    public function askEnd(Request $request)
    {
        $question = ObsceneCensorRus::getFiltered($request->input('question'));
        $event = $request->input('event');
        $name = $request->input('name');
        $email = $request->input('email');
        (new Client())->get("https://tgbot.cpvs.moscow/api/events/sendQuestion?text=$question&email=$email&name=$name&event=$event");

        $list = Sheets::spreadsheet($this->questionSpreadsheetId)
            ->sheet('Вопросы')
            ->all();
        $index = count($list) + 1;
        Sheets::spreadsheet($this->questionSpreadsheetId)
            ->sheet('Вопросы')
            ->range('A' . $index )
            ->update([[$request->ip(), $name, $email, $event, $question, Carbon::now()->format('d.m.Y H:i')]]);

        return view('questions.ask.end');
    }
}
