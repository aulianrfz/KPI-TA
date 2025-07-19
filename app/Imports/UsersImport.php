<?php

namespace App\Imports;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\Importable;
use App\Models\AllPeserta;

class UsersImport implements ToModel
{
    /**
     * @param array $row
     *
     * @return User|null
     */

    use Importable;

    public function model(array $row)
    {
        // dd($row);
        // return new User([
        //    'name'     => $row[0],
        //    'email'    => $row[1], 
        //    'password' => Hash::make($row[2]),
        // ]);
        return new AllPeserta([
            'nama_ketua'    => $row[0], // Kolom pertama di excel
            'nama_anggota'  => $row[1], // Kolom kedua di excel
            'nomor_telepon' => $row[2], // Kolom ketiga di excel
            'nama_institusi'=> $row[3], // Kolom keempat di excel
            'keterangan'    => $row[4], // Kolom kelima di excel
        ]);
    }
}