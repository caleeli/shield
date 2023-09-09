<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ReportController extends Controller
{
    use ResourceTrait;

    private function safeQuery(string $sql, array $params): array
    {
        $filteredParams = [];

        foreach ($params as $key => $value) {
            // Use regex to match the parameter in the SQL.
            // The pattern looks for the ':' followed by the key and ensures
            // that the next character is not a word character (a-z, A-Z, 0-9, or _)
            $pattern = '/:' . preg_quote($key, '/') . '(?!\w)/';

            if (preg_match($pattern, $sql)) {
                $filteredParams[$key] = $value;
            }
        }

        return DB::select($sql, $filteredParams);
    }

    public function report(Request $request)
    {
        $sql = $request->input('sql');
        $parameters = json_decode($request->input('parameters', '{}'), true);
        $result = $this->safeQuery($sql, $parameters);

        return [
            'data' => $result
        ];
    }

    public function exportExcel(Request $request)
    {
        $sql = $request->input('sql');
        $parameters = json_decode($request->input('parameters', '{}'), true);
        $data = $this->safeQuery($sql, $parameters);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set headers first
        if (count($data) > 0) {
            $column = 1;
            foreach ($data[0] as $key => $value) {
                $sheet->setCellValueByColumnAndRow($column, 1, $key);
                $column++;
            }
        }

        // Fill the spreadsheet with data
        $row = 2;  // Start from the second row as first is header
        foreach ($data as $record) {
            $column = 1;
            foreach ($record as $value) {
                $sheet->setCellValueByColumnAndRow($column, $row, $value);
                $column++;
            }
            $row++;
        }

        // Applying style to header row
        $headerStyle = [
            'font' => [
                'bold' => true,
                'color' => [
                    'argb' => 'FFFFFFFF',  // White text
                ],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => [
                    'argb' => 'FF0000FF',  // Blue background
                ],
            ],
        ];

        $highestColumn = $sheet->getHighestColumn();  // Get the last column with data
        $sheet->getStyle('A1:' . $highestColumn . '1')->applyFromArray($headerStyle);

        $writer = new Xlsx($spreadsheet);

        // Create a temporary file in the system
        $fileName = 'report.xlsx';
        $temp_file = tempnam(sys_get_temp_dir(), $fileName);
        $writer->save($temp_file);

        // Return the excel file as an attachment
        return response()->download($temp_file, $fileName, ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'])->deleteFileAfterSend(true);
    }
}
