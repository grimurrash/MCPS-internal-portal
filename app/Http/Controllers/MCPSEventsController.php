<?php

namespace App\Http\Controllers;

use chillerlan\QRCode\QRCode;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Revolution\Google\Sheets\Facades\Sheets;

class MCPSEventsController extends Controller
{
    private $spreadsheetId = '1XL1nyV7b3K1NieRzOeyRq9TuJmhI-AhfUMPLC4DDBRc';

    public function setEventUser(Request $request)
    {
        $request->session()->put('isEventUser', 'true');
        return response('ok');
    }

    private function getEventParticipantInfoFromGoogleSheet($eventId, $email): array
    {
        $sheet = Sheets::spreadsheet($this->spreadsheetId)
            ->sheet($eventId)
            ->get();
        $searchParticipant = null;
        foreach ($sheet as $index => $row) {
            if ($index === 0) continue;

            if ($row[0] !== $email) continue;

            $searchParticipant = $row;
        }

        if ($searchParticipant === null) return ['isHas' => false];
        return [
            'isHas' => true,
            'name' => $searchParticipant[1] . ' ' . $searchParticipant[2] . ' ' . $searchParticipant[3],
            'email' => $searchParticipant[0],
            'status' => $searchParticipant[1],
            'position' => $searchParticipant[4] ?? '',
            'workOrganisation' => $searchParticipant[5] ?? '',
//            'place' => $searchParticipant[8] ?? '',
//            'row' => $searchParticipant[9] ?? '',
            'qrCode' => "http://portal.cpvs.moscow/events/admin/participant/$eventId?id=$email"
        ];
    }

    private function setIsComeFromGoogleSheet($eventId, $email)
    {
        $isComeColumn = 'H';

        $sheet = Sheets::spreadsheet($this->spreadsheetId)
            ->sheet($eventId)
            ->all();
        foreach ($sheet as $index => $row) {
            if ($index === 0) continue;

            if ($row[0] !== $email) continue;
            Sheets::spreadsheet($this->spreadsheetId)
                ->sheet($eventId)
                ->range($isComeColumn . ($index + 1))
                ->update([['Пришел']]);
            $fio = $row[1] . ' ' . $row[2] . ' ' . $row[3];
            $place = $row[8] ?? '';
            $rowNumber = $row[9] ?? '';
//            (new Client())->get("https://tgbot.cpvs.moscow/api/events/sendQuestion?fio=$fio&rowNumber=$rowNumber&place=$place");
            break;
        }
    }

    public function participantQRCode(Request $request, $eventId)
    {
        $info = $this->getEventParticipantInfoFromGoogleSheet($eventId, $request->input('id'));
        if (!$info['isHas']) return view('participants.notRegistered');
        return view('participants.show', [
            'info' => $info,
            'qrCode' => (new QrCode())->render($info['qrCode'])
        ]);
    }

    public function readParticipantQRCode(Request $request, $eventId)
    {
        if (!Session::get('isEventUser', false)) return view('participants.notPermission');
        $email = $request->input('id');
        $info = $this->getEventParticipantInfoFromGoogleSheet($eventId, $email);
        if (!$info['isHas']) return view('participants.notRegistered');
        return view('participants.admin', [
            'eventId' => $eventId,
            'info' => $info,
            'qrCode' => (new QrCode())->render($info['qrCode']),
            'confirmationURl' => route('events.admin.confirmation', $eventId) . '?id=' . $email
        ]);

    }

    public function confirmationParticipant(Request $request, $eventId)
    {
        if (!Session::get('isEventUser', false)) return view('participants.notPermission');
        $this->setIsComeFromGoogleSheet($eventId, $request->input('id'));

        return view('participants.end');
    }

    public function updateGoogleSheet($eventId): string
    {
        $dates = DB::connection('mysql2')->table('sch_cf7_vdata_entry')
            ->where('cf7_id', $eventId)
            ->get();
        if (!count($dates)) return 'Нет такого события';
        $sheet = Sheets::spreadsheet($this->spreadsheetId)
            ->sheet($eventId)
            ->all();
        $newRows = [];
        foreach ($dates->groupBy('data_id') as $item) {
            $email = $item->where('name', 'your-email')->first()->value;
            $key = array_search($email, array_column($sheet, '1'));
            if ($key === false) {
                $newRows[] = [
                    $item->where('name', 'your-name')->first()->value,
                    $email,
                    $item->where('name', 'your-text')->first()->value,
                ];
            }
        }

        Sheets::spreadsheet($this->spreadsheetId)
            ->sheet($eventId)
            ->range('A' . (count($sheet) + 1))
            ->update($newRows);
        return 'Таблица участников обновлена';
    }
}
