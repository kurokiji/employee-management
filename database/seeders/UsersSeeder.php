<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\Hash;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create('es_ES');
        for ($i=0; $i < 100; $i++) {
            DB::table('users')->insert([
                'name' => "$faker->firstName $faker->lastName",
                'email' => $faker->unique()->companyEmail,
                'password' => Hash::make($faker->password),
                'job' => $faker->randomElement(['employee', 'executive', 'humanresources']),
                'salary' => $faker->randomFloat(2, 10000, 70000),
                'biography'=> $faker->paragraphs(2, true),
                'profileImgUrl' => $faker->imageUrl(600, 600, 'cats', 'true'),
            ]);
        }
    }
}
