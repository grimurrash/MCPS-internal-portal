<?php

namespace App\Exceptions\Console\Commands;

use App\Mail\AdvisorMail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Revolution\Google\Sheets\Facades\Sheets;
use Throwable;

class SendMailsCommand extends Command
{
    protected $signature = 'mails:send';

    protected $description = 'Отправка рассылок';

    private $spreadsheet = '1wYGs7F0OOMAYZAUXifdQPjma1tmvBpepCmNOur0WY-A';

    private $sheetName = 'invoices_success_cetra';

    public function handle(): void
    {
        $sheet = Sheets::spreadsheet($this->spreadsheet)
            ->sheet('invoices_success_cetra')
            ->get();

        $items1 = [];
        $str = '';
        $amount = 0.;
        $amount1 = 0.;
        foreach ($sheet as $row) {
            if ($row[1] === 'shop_id') {
                continue;
            }

            if (strlen($row[2]) === 36) {
                $amount1 += (float)$row[5];
            } else {
//                $str .= "'$row[2]',";
//                 $count ++;
                $items1[$row[2]] = [
                    'shop_id' => $row[1],
                    'id' => $row[0],
//                    'amount' => $row[5],
                    'amount' => str_replace(',', '.', $row[5])
                ];
            }
        }
        $sheet2 = Sheets::spreadsheet($this->spreadsheet)
            ->sheet('lava_finance_payment_tries')
            ->get();
        foreach ($sheet2 as $row) {
            if (isset($items1[$row[0]])) {
                unset($items1[$row[0]]);
            }
        }

        dd(count($items1));
        $str = json_encode(array_values($items1));

        $csv = fopen('php://temp/maxmemory:' . (5 * 1024 * 1024), 'rb+');
        fwrite($csv, $str);
//        fwrite($csv, trim($str, ','));
        rewind($csv);
        $output = stream_get_contents($csv);
        fclose($csv);
        $csvName = time() . '_json.txt';
        Storage::put("/export/" . $csvName, $output);
//
//        $str = trim($str2, ',');
//        $csv = fopen('php://temp/maxmemory:' . (5 * 1024 * 1024), 'rb+');
//        fwrite($csv, $str);
//        rewind($csv);
//        $output = stream_get_contents($csv);
//        fclose($csv);
//        $csvName = time() . '_all.txt';
//        Storage::put("/export/" . $csvName, $output);
        dd('');
        $list = [];
        $startIndex = 2;
        echo "Start sending mails\n";

        echo "Mail count: " . (count($sheet) - 1) . "\n\n";
        $countSend = 0;
        try {
            foreach ($sheet as $index => $row) {
                if ($index === 0) continue;

                if (!empty($row[5]) && $row[5] === "1") {
                    $list[] = [1];
                    continue;
                }
                echo "SEND $row[4] ; code: $row[0];\n";
                Mail::send(new AdvisorMail(
                    $row[0],
//                    'rashit.sabirov1999@gmail.com'
//                    'sabirovra@patriotsport.moscow'
                    $row[4]
                ));
                echo "SEND END!\n\n";
                $list[] = [1];
                $countSend++;
            }
        } catch (Throwable $e) {
            echo "EXCEPTION: {$e->getMessage()}\n";
        }

        echo "\nUPDATE SHEET...\n";
        if (count($list) > 0) {
            echo "UPDATE LIST LENGTH " . count($list) . "\n";
            Sheets::spreadsheet($this->spreadsheet)
                ->sheet($this->sheetName)
                ->range('F' . $startIndex)
                ->update($list);
        }
        echo "\nSEND $countSend mail\n";
        echo "\nFINISH!\n";
    }
}
