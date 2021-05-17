<?php

namespace App\Imports;

use Maatwebsite\Excel\Row;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;

use Str;
use App\Viralpatient;

class AmpathMflImport implements OnEachRow, WithHeadingRow, WithChunkReading
{


    public function onRow(Row $row)
    {
        $row_array = $row->toArray();

        if(Str::contains($row_array['lab_tested_in'], ['Alupe', 'KEMRI']) && env('APP_LAB') != 3) return;
        if(Str::contains($row_array['lab_tested_in'], ['AMPATH', 'Eldoret']) && env('APP_LAB') != 5) return;

        $p = Viralpatient::where(['patient' => $row_array['current_ccc']])->first();
        if(!$p) return;
        $p->patient = $row_array['proposed_ccc'];
        $p->pre_update();
    }

    public function chunkSize(): int
    {
        return 100;
    }
}
