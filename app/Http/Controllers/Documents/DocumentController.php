<?php

namespace App\Http\Controllers\Documents;

use App\Http\Controllers\Controller;
use App\Models\DocumentTemplate;
use Carbon\Carbon;
use RuntimeException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use ZipArchive;
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

        if (!is_dir($currentUserTempDir)) {
            !mkdir($currentUserTempDir);
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
            $filename = $currentUserTempDir . mb_substr(str_replace("/", ',', $row[0]), 0, 50);
            $word->saveAs($filename . '.docx');
        }

        $zip = new ZipArchive();
        $zipArchiveFile = $tempDir . '/user' . $userId . '.zip';

        if (file_exists($zipArchiveFile)) {
            unlink($zipArchiveFile);
        }

        if ($zip->open($zipArchiveFile,
                ZipArchive::CREATE) === TRUE) {
            $dir = opendir($currentUserTempDir);
            while ($d = readdir($dir)) {
                if ($d === '.' || $d === '..') continue;
                $zip->addFile($currentUserTempDir . $d, $d);
            }
            $zip->close();
        }
        $dir = opendir($currentUserTempDir);
        while ($d = readdir($dir)) {
            if ($d === '.' || $d === '..') continue;
            unlink($currentUserTempDir . $d);
        }
        rmdir($currentUserTempDir);
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
        $lists = [];

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
                $filename = $currentUserTempDir . mb_substr(str_replace("/", ',', $row[0]), 0, 50);
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
            $filename = $currentUserTempDir . mb_substr(str_replace('/', ',', $row[0] . ' и ' . $tempRow[0]), 1, 50);
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

        $zip = new ZipArchive();
        $zipArchiveFile = $tempDir . '/user' . $userId . '.zip';

        if (file_exists($zipArchiveFile)) {
            unlink($zipArchiveFile);
        }

        if ($zip->open($zipArchiveFile,
                ZipArchive::CREATE) === TRUE) {
            $dir = opendir($currentUserTempDir);
            while ($d = readdir($dir)) {
                if ($d === '.' || $d === '..') continue;
                $zip->addFile($currentUserTempDir . $d, $d);
            }
            $zip->close();
        }
        $dir = opendir($currentUserTempDir);
        while ($d = readdir($dir)) {
            if ($d === '.' || $d === '..') continue;
            unlink($currentUserTempDir . $d);
        }
        rmdir($currentUserTempDir);
        unlink($loadFilePath);
        return response()->download($zipArchiveFile)->deleteFileAfterSend();
    }
}
