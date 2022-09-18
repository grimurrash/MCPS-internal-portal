<?php

namespace App\Http\Controllers\Events;

use App\Helper\ObsceneCensorRus;
use App\Http\Controllers\Controller;
use App\Http\Resources\WordCloudResource;
use App\Models\WordCloud;
use App\Models\WordCloudAnswer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Exception;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Revolution\Google\Sheets\Facades\Sheets;

class WordCloudController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $search = $request->input('q', '');
        $perPage = (int)$request->input('perPage', 10);
        $page = (int)$request->input('page', 1);
        $sortBy = $request->input('sortBy', 'id');
        if ($sortBy === null) $sortBy = 'id';
        $sortDesc = $request->input('sortDesc', false);
        $sortDesc = $sortDesc === "true";
        $search = Str::lower($search);
        $wordClouds = WordCloud::query();

        $wordClouds->where(function ($q) use ($search) {
            $q->Where(DB::raw('LOWER(question)'), 'like', "%$search%");
        });
        $wordClouds = $wordClouds->get();
        $wordClouds = $wordClouds->sortBy($sortBy, 0, $sortDesc);
        $totalCount = $wordClouds->count();
        $wordClouds = $wordClouds->skip($page * $perPage - $perPage)->take($perPage);
        return response()->json([
            'total' => $totalCount,
            'list' => WordCloudResource::collection($wordClouds)
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $wordCloud = WordCloud::create([
            'eventName' => $request->input('eventName'),
            'question' => $request->input('question'),
            'backgroundImage' => $request->input('backgroundImage', 'https://portal.cpvs.moscow/public/images/backgroud.svg'),
            'wordColor' => $request->input('wordColor', 'pastel1'),
            'exceptionWords' => Str::lower($request->input('exceptionWords', '')),
            'pageTitle' => $request->input('pageTitle', 'Облако слов'),
            'logo' => $request->input('logo', ''),
            'isUnique' => $request->input('isUnique', false),
            'additionalCss' => $request->input('additionalCss', ''),
            'createUser_id' => $request->user()->id,
            'fontSizeSmall' => $request->input('fontSizeSmall', 30),
            'fontSizeLarge' => $request->input('fontSizeLarge', 120),
            'showCounts' => $request->input('showCounts', false),
            'countSize' => $request->input('countSize', 20),
            'angle' => $request->input('angle', 0),
        ]);
        return response()->json([
            'status' => true,
            'word_cloud' => $wordCloud->id
        ]);
    }

    public function show(Request $request, int $wordCloudId): JsonResponse
    {
        $wordCloud = WordCloud::find($wordCloudId);
        if (is_null($wordCloud)) {
            return response()->json()->setStatusCode(404);
        }
        if ($request->user()->id !== $wordCloud->createUser_id) {
            return response()->json()->setStatusCode(403);
        }
        return response()->json(WordCloudResource::make($wordCloud));
    }

    public function update(Request $request, int $wordCloudId): JsonResponse
    {
        $wordCloud = WordCloud::find($wordCloudId);
        if (is_null($wordCloud)) {
            return response()->json()->setStatusCode(404);
        }
        if ($request->user()->id !== $wordCloud->createUser_id) {
            return response()->json()->setStatusCode(403);
        }
        $wordCloud->update([
            'eventName' => $request->input('eventName'),
            'question' => $request->input('question'),
            'backgroundImage' => $request->input('backgroundImage'),
            'wordColor' => $request->input('wordColor'),
            'exceptionWords' => Str::lower($request->input('exceptionWords')),
            'pageTitle' => $request->input('pageTitle', 'Облако слов'),
            'logo' => $request->input('logo'),
            'isUnique' => $request->input('isUnique'),
            'additionalCss' => $request->input('additionalCss'),
            'createUser_id' => $request->user()->id,
            'fontSizeSmall' => $request->input('fontSizeSmall', 30),
            'fontSizeLarge' => $request->input('fontSizeLarge', 120),
            'showCounts' => $request->input('showCounts', false),
            'countSize' => $request->input('countSize', 20),
            'angle' => $request->input('angle', 0),
        ]);
        return response()->json();
    }

    public function clear(Request $request, int $wordCloudId): JsonResponse
    {
        $wordCloud = WordCloud::find($wordCloudId);
        if (is_null($wordCloud)) {
            return response()->json()->setStatusCode(404);
        }
        if ($request->user()->id !== $wordCloud->createUser_id) {
            return response()->json()->setStatusCode(403);
        }
        $wordCloud->answers()->delete();
        return response()->json();
    }

    public function delete(Request $request, int $wordCloudId): JsonResponse
    {
        $wordCloud = WordCloud::find($wordCloudId);
        if (is_null($wordCloud)) {
            return response()->json()->setStatusCode(404);
        }
        if ($request->user()->id !== $wordCloud->createUser_id) {
            return response()->json()->setStatusCode(403);
        }
        $wordCloud->delete();
        return response()->json();
    }

    public function showWordCloudAnswerForm(Request $request, int $wordCloudId)
    {
        $wordCloud = WordCloud::find($wordCloudId);
        return view('events.wordcloud.show', compact('wordCloud'));
    }

    public function saveWordCloudAnswer(Request $request, int $wordCloudId)
    {
        $wordCloud = WordCloud::find($wordCloudId);
        $answer = Str::lower($request->input('answer'));
        if (!ObsceneCensorRus::isAllowed($answer)) return view('questions.answers.end');

        if (!empty($wordCloud->exceptionWords)) {
            $exceptionWords = explode(',', $wordCloud->exceptionWords);
            foreach ($exceptionWords as $exceptionWord) {
                $answer = str_replace(trim($exceptionWord), '', $answer);
            }
        }


        if ($wordCloud->isUnique) {
            WordCloudAnswer::query()->updateOrInsert([
                'wordCloud_id' => $wordCloudId,
                'ip' => $request->ip(),
            ], [
                'answer' => $answer
            ]);
        } else {
            WordCloudAnswer::query()->create([
                'wordCloud_id' => $wordCloudId,
                'ip' => $request->ip(),
                'answer' => $answer
            ]);
        }
        return view('events.wordcloud.end', compact('wordCloud'));
    }

    public function getWordCloudSetting(Request $request, int $wordCloudId): JsonResponse
    {
        $wordCloud = WordCloud::find($wordCloudId);
        return response()->json(WordCloudResource::make($wordCloud));
    }

    public function getWordCloudAnswer(Request $request, int $wordCloudId): JsonResponse
    {
        $allAnswer = WordCloudAnswer::query()->where('wordCloud_id', $wordCloudId)->get('answer');
        $answers = [];
        foreach ($allAnswer as $item) {
            $answer = Str::lower($item->answer);
            $answer = str_ireplace(['…', '.', ','], ';', $answer);
            foreach (explode(';', $answer) as $str) {
                $str = trim($str);
                if (mb_strlen($str) == 0) continue;

                $answers[] = $str;
            }
        }
        return response()->json([
            'answers' => $answers,
            'count' => count($answers)
        ]);
    }

    private function getWordCloudAnswers(WordCloud $wordCloud): array
    {
        $allAnswer = WordCloudAnswer::query()->where('wordCloud_id', $wordCloud->id)->get('answer');

        $answers = [];

        foreach ($allAnswer as $item) {
            $answer = Str::lower($item->answer);
            $answer = str_ireplace(['…', '.', ','], ';', $answer);
            foreach (explode(';', $answer) as $str) {
                $str = str_ireplace(['?', '!', '-'], '', $str);
                $str = trim($str);
                if (mb_strlen($str) == 0) continue;

                if (isset($answers[$str])) {
                    $answers[$str] = $answers[$str] + 1;
                } else {
                    $answers[$str] = 1;
                }
            }
        }

        $list = [];

        foreach ($answers as $answer => $count)
        {
            $list[] = [
                $answer,
                $count
            ];
        }
        usort($list, function ($a, $b) {
            return $a[1] < $b[1];
        });

        array_unshift($list, [
            'Ответ',
            'Кол-во'
        ]);

        array_unshift($list, [
            'Вопрос:',
            $wordCloud->question
        ]);

        return $list;
    }

    public function exportWordCloudAnswerToExcel(Request $request, int $wordCloudId) {
        /** @var WordCloud $wordCloud */
        $wordCloud = WordCloud::query()->find($wordCloudId);
        if ($wordCloud == null) {
            return response()->json(['message' => 'Not found WordCloud']);
        }
        $list = $this->getWordCloudAnswers($wordCloud);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray($list);

        foreach (range(0, 1) as $columnID) {
            $sheet->getColumnDimensionByColumn($columnID)->setAutoSize(true);
        }
        $tempPath = storage_path('app/public/temp/wordCloudAnswers' . $request->user()->id . '.xlsx');
        try {
            $writer = new Xlsx($spreadsheet);
            $writer->save($tempPath);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }
        $spreadsheet->disconnectWorksheets();
        return response()->download($tempPath)->deleteFileAfterSend();
    }

    public function exportWordCloudAnswerToGoogleSheep(Request $request, int $wordCloudId): JsonResponse
    {
        $googleTableId = '1qbgHxCQGoJ1td9P1AGfrTP9qqYwjsog9uqI9TXDhxFI';

        /** @var WordCloud $wordCloud */
        $wordCloud = WordCloud::query()->find($wordCloudId);
        if ($wordCloud == null) {
            return response()->json(['message' => 'Not found WordCloud']);
        }

        $list = $this->getWordCloudAnswers($wordCloud);

        $spreadsheet = Sheets::spreadsheet($googleTableId);

        if (!in_array($wordCloud->id, $spreadsheet->sheetList())) {
            $spreadsheet->addSheet($wordCloud->id);
        }
        $spreadsheet->sheet($wordCloud->id)
            ->range('A1')
            ->update($list);

        return response()->json(['message' => 'success']);
    }
}
