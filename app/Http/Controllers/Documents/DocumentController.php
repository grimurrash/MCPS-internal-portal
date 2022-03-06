<?php

namespace App\Http\Controllers\Documents;

use App\Helper\ZipHelper;
use App\Http\Controllers\Controller;
use App\Models\DocumentTemplate;
use Carbon\Carbon;
use RuntimeException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpWord\TemplateProcessor;

class DocumentController extends Controller
{


    /**
     * @param \Illuminate\Http\Request $request
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     * @throws \PhpOffice\PhpWord\Exception\CopyFileException
     * @throws \PhpOffice\PhpWord\Exception\CreateTemporaryFileException
     */
    public function printExcelDataByTemplate(Request $request): BinaryFileResponse
    {
        $request->validate([
            'templateFile' => ['required', 'max:50000', 'mimes:doc,docx'],
            'dataFile' => ['required', 'max:50000', 'mimes:xlsx,xls,xlsm']
        ]);
        $userId = $request->user()->id;
        $loadFile = $request->file('dataFile');
        $dateTime = Carbon::now()->addHours(3)->format('d-m-Y-H-i');

        $tempDir = storage_path('app/public/documents/temp');

        $loadFileName = "loadData-$userId-$dateTime." . $loadFile->getClientOriginalExtension();
        Storage::put('/documents/temp/' . $loadFileName, file_get_contents($loadFile->getRealPath()));
        $loadFilePath = "$tempDir/$loadFileName";

        $excel = IOFactory::load($loadFilePath);
        $worksheet = $excel->getActiveSheet();
        $list = $worksheet->toArray();
        unset($excel);
        $templateFile = $request->file('templateFile');

        $templateFileName = "/template-$userId-$dateTime." . $templateFile->getClientOriginalExtension();
        Storage::put('/documents/temp/' . $templateFileName, file_get_contents($templateFile->getRealPath()));
        $templateFilePath = "$tempDir/$templateFileName";
        $currentUserTempDir = $tempDir . '/user' . $userId . '-to-' . $dateTime . '/';

        if (is_dir($currentUserTempDir)) {
            ZipHelper::rrmdir($currentUserTempDir);
        }
        if (!mkdir($currentUserTempDir) && !is_dir($currentUserTempDir)) {
            throw new RuntimeException(sprintf('Directory "%s" was not created', $currentUserTempDir));
        }

        $itemKeys = [];
        foreach ($list as $index => $row) {
            if ($index === 0) {
                foreach ($row as $cell) {
                    $itemKeys[] = $cell;
                }
                continue;
            }

            $word = new TemplateProcessor($templateFilePath);
            foreach ($itemKeys as $colNum => $key) {
                if ($key === null) continue;
                $word->setValue($key, $row[$colNum] ?? '');
            }
            $filename = str_replace("/", ',', $row[0]);
            $dir = stristr($filename, ',,,', true);
            if ($dir !== '') {
                $dir = $currentUserTempDir . '/' . $dir;
                if (!file_exists($dir)) {
                    mkdir($dir);
                }
            }
            $filename = str_replace(',,,', '/', $filename);
            $filename = $currentUserTempDir . mb_substr($filename, 0, 80);
            $word->saveAs($filename . '.docx');
        }

        $zipArchiveFile = $tempDir . '/user' . $userId . '.zip';

        ZipHelper::zip($currentUserTempDir, $zipArchiveFile);
        ZipHelper::rrmdir($currentUserTempDir);
        unlink($templateFilePath);
        unlink($loadFilePath);
        return response()->download($zipArchiveFile)->deleteFileAfterSend();
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     * @throws \PhpOffice\PhpWord\Exception\CopyFileException
     * @throws \PhpOffice\PhpWord\Exception\CreateTemporaryFileException
     */
    public function printExcelDataByExistentTemplate(Request $request): BinaryFileResponse
    {
        $request->validate([
            'template_id' => ['required'],
            'file' => ['required', 'max:50000', 'mimes:xlsx,xls,xlsm']
        ]);
        $userId = $request->user()->id;
        $loadFile = $request->file('file');
        $dateTime = gmdate('d-m-Y-H-i');

        $tempDir = storage_path('app/public/documents/temp');

        $loadFileName = "loadData-$userId-$dateTime." . $loadFile->getClientOriginalExtension();
        Storage::put('/documents/temp/' . $loadFileName, file_get_contents($loadFile->getRealPath()));
        $loadFilePath = "$tempDir/$loadFileName";

        $excel = IOFactory::load($loadFilePath);
        $worksheet = $excel->getActiveSheet();
        $list = $worksheet->toArray();
        unset($excel);

        $template = DocumentTemplate::find($request->input('template_id'));
        $templateFilePath = storage_path('app/public') . $template->filePath;
        $currentUserTempDir = $tempDir . '/user' . $userId . 'to-' . $dateTime . '/';
        if (is_dir($currentUserTempDir)) {
            ZipHelper::rrmdir($currentUserTempDir);
        }
        if (!mkdir($currentUserTempDir) && !is_dir($currentUserTempDir)) {
            throw new RuntimeException(sprintf('Directory "%s" was not created', $currentUserTempDir));
        }
        $itemKeys = [];
        $tempRow = null;
        foreach ($list as $index => $row) {
            if ($index === 0) {
                foreach ($row as $cell) {
                    $itemKeys[] = $cell;
                }
                continue;
            }

            if (!$template->isDouble) {
                $word = new TemplateProcessor($templateFilePath);
                foreach ($itemKeys as $colNum => $key) {
                    if ($key === null) continue;
                    $word->setValue($key, $row[$colNum] ?? '');
                }
                $filename = str_replace("/", ',', $row[0]);
                $dir = stristr($filename, ',,,', true);
                if ($dir !== '') {
                    $dir = $currentUserTempDir . '/' . $dir;
                    if (!file_exists($dir)) {
                        mkdir($dir);
                    }
                }
                $filename = str_replace(',,,', '/', $filename);
                $filename = $currentUserTempDir . mb_substr($filename, 0, 80);
                $word->saveAs($filename . '.docx');
                continue;
            }
            if ($tempRow === null) {
                $tempRow = $row;
                continue;
            }
            $word = new TemplateProcessor($templateFilePath);
            foreach ($itemKeys as $colNum => $key) {
                if ($key === null) continue;
                $word->setValue($key, $row[$colNum] ?? '');
                $word->setValue($key . '1', $tempRow[$colNum] ?? '');
            }

            $filename = str_replace("/", ',', $row[0] . ' и ' . $tempRow[0]);
            $dir = stristr($filename, ',,,', true);
            if ($dir !== '') {
                $dir = $currentUserTempDir . '/' . $dir;
                if (!file_exists($dir)) {
                    mkdir($dir);
                }
            }
            $filename = str_replace(',,,', '/', $filename);
            $filename = $currentUserTempDir . mb_substr($filename, 0, 80);
            $word->saveAs($filename . '.docx');
            $tempRow = null;
        }

        if ($tempRow !== null) {
            $word = new TemplateProcessor($templateFilePath);
            foreach ($itemKeys as $colNum => $key) {
                if ($key === null || $tempRow[$colNum] === null) continue;
                $word->setValue($key, $tempRow[$colNum]);
            }
            $filename = $currentUserTempDir . $tempRow[0];
            $word->saveAs($filename . '.docx');
        }

        $zipArchiveFile = $tempDir . '/user' . $userId . '.zip';

        ZipHelper::zip($currentUserTempDir, $zipArchiveFile);
        ZipHelper::rrmdir($currentUserTempDir);

        unlink($loadFilePath);
        return response()->download($zipArchiveFile)->deleteFileAfterSend();
    }
}
