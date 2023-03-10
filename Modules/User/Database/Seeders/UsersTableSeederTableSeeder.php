<?php

namespace Modules\User\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class UsersTableSeederTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data=array(
            array(
                'name'=>'Admin',
                'email'=>'admin@bazar.com',
                'password'=>Hash::make('Bazar2023!'),
                'role'=>'admin',
                'active'=>1
            )
        );

        DB::table('users')->insert($data);
    }
}
