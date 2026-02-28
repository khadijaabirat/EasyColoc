<?php

namespace Database\Seeders;

use App\Models\Colocations;
use App\Models\categories;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defaultCategories = ['Loyer', 'Courses', 'Électricité', 'Internet', 'Autres'];

        $colocations = Colocations::all();
        
        foreach ($colocations as $colocation) {
            // Only seed if the colocation has no categories yet
            if ($colocation->categories()->count() === 0) {
                foreach ($defaultCategories as $categoryName) {
                    $colocation->categories()->create([
                        'name' => $categoryName
                    ]);
                }
            }
        }
    }
}
