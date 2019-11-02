<?php

namespace App\Http\Controllers;

use App\Contact;
use App\CsvData;
use App\Http\Requests\CsvImportRequest;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ImportController extends Controller
{

    public function getImport()
    {
        return view('import');
    }

    public function parseImport(CsvImportRequest $request)
    {

        if ($request->has('header')) {
            $data1 = Excel::toArray(config('app.db_fields'),$request->file('csv_file'));
        } else {
            $data1 = array_map('str_getcsv', file($path));
        }
        if (count($data1[0]) > 0) {
            if ($request->has('header')) {
                $csv_header_fields = config('app.db_fields');
                
            }
            
            $csv_data = array_slice($data1[0],1);


            $csv_data_file = CsvData::create([
                'csv_filename' => $request->file('csv_file')->getClientOriginalName(),
                'csv_header' => $request->has('header'),
                'csv_data' => json_encode($data1)
            ]);
        } else {
            return redirect()->back();
        }

        return view('import_fields',compact('csv_header_fields','csv_data', 'csv_data_file'));

    }

    public function processImport(Request $request)
    {
        $data1 = CsvData::find($request->csv_data_file_id);

        $csv_data = json_decode($data1->csv_data, true);
         $csv_data1 = array_slice($csv_data[0],1);
        foreach ($csv_data1 as $row) {
            $contact = new Contact();
            foreach (config('app.db_fields') as $index => $field) {
                if ($data1->csv_header) {
                    $contact->$field = $row[$request->fields[$field]];
                } else {
                    $contact->$field = $row[$request->fields[$index]];
                }
            }
            $contact->save();
        }
    

        return view('import_success');
    }

}
