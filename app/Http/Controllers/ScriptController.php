<?php

namespace App\Http\Controllers;

use \Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Exception;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ScriptController extends Controller
{
    /**
     * @throws \JsonException
     */
    public function importFounderRepresentative()
    {
        $sphinxUrl = "https://map.mskobr.ru/api/eduoffices/sphinx.json?area=&by_legal=1&district=&limit=1433&metro=&mskobr_special_order=1&page=1&program=&search=";
        $allEducationOffices = json_decode(file_get_contents($sphinxUrl), true, 512, JSON_THROW_ON_ERROR)['list'];

        $educationOfficeList = [];
        try {
            foreach ($allEducationOffices as $educationOffice) {
                $educationOfficeUrl = 'https://map.mskobr.ru/api/eduoffices/' . $educationOffice['eo_id'] . '.json';
                $educationOfficeDetails = json_decode(file_get_contents($educationOfficeUrl), true, 512, JSON_THROW_ON_ERROR)['list'];
                $educationOfficeTemp = [
                    'id' => $educationOfficeDetails['eo_id'],
                    'title' => $educationOfficeDetails['title'],
                    'title_full' => $educationOfficeDetails['title_full'],
                ];
                if (!empty($educationOfficeDetails['current_director'])) {
                    $educationOfficeTemp['current_director'] = $educationOfficeDetails['current_director'];
                    $educationOfficeTemp['note'] = '';
                } else {
                    $educationOfficeTemp['current_director'] = [
                        'fio' => '',
                        'age' => '',
                        'eo_phone' => '',
                        'public_phone' => '',
                        'email' => '',
                    ];
                    $educationOfficeTemp['note'] = 'Нет актуальной информции о директоре';
                }
                $educationOfficeList[] = $educationOfficeTemp;
            }
        } catch (\Exception $exception)
        {
            dd($educationOffice, $exception);
        }


        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'Id')
            ->setCellValue('B1', 'title)')
            ->setCellValue('C1', 'title_full')
            ->setCellValue('D1', 'current_director.fio')
            ->setCellValue('E1', 'current_director.age')
            ->setCellValue('F1', 'current_director.eo_phone')
            ->setCellValue('G1', 'current_director.public_phone')
            ->setCellValue('H1', 'current_director.email')
            ->setCellValue('I1', 'note');
        $row = 2;
        foreach ($educationOfficeList as $educationOffice) {
            $sheet->setCellValue('A' . $row, $educationOffice['id'])
                ->setCellValue('B' . $row, $educationOffice['title'])
                ->setCellValue('C' . $row, $educationOffice['title_full'])
                ->setCellValue('D' . $row, $educationOffice['current_director']['fio'])
                ->setCellValue('E' . $row, $educationOffice['current_director']['age'])
                ->setCellValue('F' . $row, $educationOffice['current_director']['eo_phone'])
                ->setCellValue('G' . $row, $educationOffice['current_director']['public_phone'])
                ->setCellValue('H' . $row, $educationOffice['current_director']['email'])
                ->setCellValue('I' . $row, $educationOffice['note']);
            $row++;
        }
        $sheet->setCellValue('A' . ($row + 1), 'Всего: ' . count($educationOfficeList));

        foreach (range('A', 'I') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }
        $tempPath = storage_path('app/public/temp/importFounderRepresentative.xlsx');
        try {
            $writer = new Xlsx($spreadsheet);
            $writer->save($tempPath);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }
        $spreadsheet->disconnectWorksheets();
        return response()->download($tempPath)->deleteFileAfterSend();
    }

    public function importDirectorGTOData(Request $request)
    {
        $storageDir = storage_path('app');

        $loadFile = $request->file('file');
        $loadFileName = 'loadDataFile.' . $loadFile->getClientOriginalExtension();
        Storage::put($loadFileName, file_get_contents($loadFile->getRealPath()));
        $loadFilePath = $storageDir . '/' . $loadFileName;

        $excel = IOFactory::load($loadFilePath);
        $worksheet = $excel->getActiveSheet();
        $loadDataList = $worksheet->toArray();
    }
}
