<?php

namespace App\Http\Controllers;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
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
        foreach ($allEducationOffices as $educationOffice) {
            $educationOfficeUrl = 'https://map.mskobr.ru/api/eduoffices/' . $educationOffice['eo_id'] . '.json';
            $educationOfficeDetails = json_decode(file_get_contents($educationOfficeUrl), true, 512, JSON_THROW_ON_ERROR)['list'];
            $educationOfficeTemp = [
                'id' => $educationOfficeDetails['eo_id'],
                'title' => $educationOfficeDetails['title'],
                'title_full' => $educationOfficeDetails['title_full'],
            ];
            if (!empty($educationOfficeDetails['founder_representative'])) {
                $educationOfficeTemp['founder_representative'] = $educationOfficeDetails['founder_representative'][0];
                $educationOfficeTemp['note'] = '';
            } else {
                $educationOfficeTemp['founder_representative'] = [
                    'name' => '',
                    'position' => '',
                    'phone' => '',
                    'add_phone' => '',
                    'mobile_phone' => '',
                    'email' => '',
                ];
                $educationOfficeTemp['note'] = 'Нет представителя учредителя в Управляющем совете';
            }
            $educationOfficeList[] = $educationOfficeTemp;
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'id')
            ->setCellValue('B1', 'title')
            ->setCellValue('C1', 'title_full')
            ->setCellValue('D1', 'founder_representative.name')
            ->setCellValue('E1', 'founder_representative.position')
            ->setCellValue('F1', 'founder_representative.phone')
            ->setCellValue('G1', 'founder_representative.add_phone')
            ->setCellValue('H1', 'founder_representative.mobile_phone')
            ->setCellValue('I1', 'founder_representative.email')
            ->setCellValue('J1', 'note');
        $row = 2;
        foreach ($educationOfficeList as $educationOffice) {
            $sheet->setCellValue('A' . $row, $educationOffice['id'])
                ->setCellValue('B' . $row, $educationOffice['title'])
                ->setCellValue('C' . $row, $educationOffice['title_full'])
                ->setCellValue('D' . $row, $educationOffice['founder_representative']['name'])
                ->setCellValue('E' . $row, $educationOffice['founder_representative']['position'])
                ->setCellValue('F' . $row, $educationOffice['founder_representative']['phone'])
                ->setCellValue('G' . $row, $educationOffice['founder_representative']['add_phone'])
                ->setCellValue('H' . $row, $educationOffice['founder_representative']['mobile_phone'])
                ->setCellValue('I' . $row, $educationOffice['founder_representative']['email'])
                ->setCellValue('J' . $row, $educationOffice['note']);
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
}
