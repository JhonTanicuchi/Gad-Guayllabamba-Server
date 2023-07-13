<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Person;
use App\Models\Catalog;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $person = Person::create([
            'identification_type' => 'Cédula',
            'identification' => '1111111111',
            'names' => 'Asistente Web',
            'last_names' => 'GAD Administrador',
        ]);

        $user = User::create([
            'email' => 'guayllabambagad@gmail.com',
            'password' => Hash::make('Web2023@02.10'),
            'person' => $person->id,
        ]);
        $user->assignRole('Super Administrador');
    }
}
