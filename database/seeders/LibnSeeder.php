<?php

namespace Database\Seeders;

use App\Models\Libn;
use Illuminate\Database\Seeder;

class LibnSeeder extends Seeder
{
    public function run(): void
    {
        $books = [
            // Computer Science
            ['LIBN_TITULLI' => 'Introduction to Algorithms',               'LIBN_AUTORI' => 'Cormen, Leiserson, Rivest, Stein', 'LIBN_ISBN' => '978-0-262-03384-8', 'LIBN_VITI' => 2022, 'LIBN_STATUSI' => 'Disponueshem'],
            ['LIBN_TITULLI' => 'Clean Code',                               'LIBN_AUTORI' => 'Robert C. Martin',                  'LIBN_ISBN' => '978-0-13-235088-4', 'LIBN_VITI' => 2008, 'LIBN_STATUSI' => 'Disponueshem'],
            ['LIBN_TITULLI' => 'Database System Concepts',                 'LIBN_AUTORI' => 'Silberschatz, Korth, Sudarshan',   'LIBN_ISBN' => '978-0-07-352332-3', 'LIBN_VITI' => 2019, 'LIBN_STATUSI' => 'Disponueshem'],
            ['LIBN_TITULLI' => 'Computer Networking: A Top-Down Approach', 'LIBN_AUTORI' => 'Kurose, Ross',                      'LIBN_ISBN' => '978-0-13-359414-0', 'LIBN_VITI' => 2021, 'LIBN_STATUSI' => 'Huazuar'],
            // Business & Management
            ['LIBN_TITULLI' => 'Principles of Management',                 'LIBN_AUTORI' => 'Robbins, Coulter',                  'LIBN_ISBN' => '978-0-13-516662-4', 'LIBN_VITI' => 2021, 'LIBN_STATUSI' => 'Disponueshem'],
            ['LIBN_TITULLI' => 'Financial Accounting',                     'LIBN_AUTORI' => 'Libby, Libby, Short',               'LIBN_ISBN' => '978-1-26-026021-8', 'LIBN_VITI' => 2020, 'LIBN_STATUSI' => 'Disponueshem'],
            ['LIBN_TITULLI' => 'Marketing Management',                     'LIBN_AUTORI' => 'Kotler, Keller',                    'LIBN_ISBN' => '978-0-13-385646-0', 'LIBN_VITI' => 2022, 'LIBN_STATUSI' => 'Disponueshem'],
            // Law
            ['LIBN_TITULLI' => 'E Drejta Civile Shqiptare',               'LIBN_AUTORI' => 'Ardian Nuni',                       'LIBN_ISBN' => null,                'LIBN_VITI' => 2018, 'LIBN_STATUSI' => 'Disponueshem'],
            ['LIBN_TITULLI' => 'E Drejta Penale',                          'LIBN_AUTORI' => 'Ismet Elezi',                       'LIBN_ISBN' => null,                'LIBN_VITI' => 2015, 'LIBN_STATUSI' => 'Disponueshem'],
            // Mathematics & Physics
            ['LIBN_TITULLI' => 'Calculus: Early Transcendentals',         'LIBN_AUTORI' => 'James Stewart',                     'LIBN_ISBN' => '978-1-28-574155-0', 'LIBN_VITI' => 2016, 'LIBN_STATUSI' => 'Disponueshem'],
            ['LIBN_TITULLI' => 'Linear Algebra and Its Applications',     'LIBN_AUTORI' => 'Gilbert Strang',                    'LIBN_ISBN' => '978-0-98-023903-9', 'LIBN_VITI' => 2019, 'LIBN_STATUSI' => 'Disponueshem'],
            ['LIBN_TITULLI' => 'University Physics',                       'LIBN_AUTORI' => 'Young, Freedman',                   'LIBN_ISBN' => '978-0-13-397816-9', 'LIBN_VITI' => 2019, 'LIBN_STATUSI' => 'Disponueshem'],
            // Civil Engineering
            ['LIBN_TITULLI' => 'Structural Analysis',                      'LIBN_AUTORI' => 'R.C. Hibbeler',                     'LIBN_ISBN' => '978-0-13-461136-5', 'LIBN_VITI' => 2018, 'LIBN_STATUSI' => 'Disponueshem'],
            // Psychology & Education
            ['LIBN_TITULLI' => 'Psychology: An Introduction',              'LIBN_AUTORI' => 'Benjamin Lahey',                    'LIBN_ISBN' => '978-0-07-802767-5', 'LIBN_VITI' => 2012, 'LIBN_STATUSI' => 'Disponueshem'],
            ['LIBN_TITULLI' => 'Pedagogji e Përgjithshme',                 'LIBN_AUTORI' => 'Ndue Gjokutaj',                     'LIBN_ISBN' => null,                'LIBN_VITI' => 2014, 'LIBN_STATUSI' => 'Disponueshem'],
            // Tourism
            ['LIBN_TITULLI' => 'Tourism: Principles and Practice',        'LIBN_AUTORI' => 'Cooper, Fletcher, Fyall',           'LIBN_ISBN' => '978-0-27-368974-3', 'LIBN_VITI' => 2008, 'LIBN_STATUSI' => 'Disponueshem'],
            // Chemistry & Biology
            ['LIBN_TITULLI' => 'Organic Chemistry',                        'LIBN_AUTORI' => 'Paula Yurkanis Bruice',             'LIBN_ISBN' => '978-0-32-136727-0', 'LIBN_VITI' => 2016, 'LIBN_STATUSI' => 'Disponueshem'],
            ['LIBN_TITULLI' => 'Campbell Biology',                         'LIBN_AUTORI' => 'Urry, Cain, Wasserman',             'LIBN_ISBN' => '978-0-13-409341-4', 'LIBN_VITI' => 2020, 'LIBN_STATUSI' => 'Disponueshem'],
            // Political Science
            ['LIBN_TITULLI' => 'An Introduction to Political Science',    'LIBN_AUTORI' => 'Mark Dickerson',                    'LIBN_ISBN' => '978-0-17-650368-4', 'LIBN_VITI' => 2009, 'LIBN_STATUSI' => 'Disponueshem'],
            // Social Work
            ['LIBN_TITULLI' => 'Social Work: An Introduction',            'LIBN_AUTORI' => 'Nigel Horner',                      'LIBN_ISBN' => '978-1-84-445726-6', 'LIBN_VITI' => 2012, 'LIBN_STATUSI' => 'Disponueshem'],
        ];

        foreach ($books as $book) {
            // Unique by title + author if no ISBN
            if ($book['LIBN_ISBN']) {
                Libn::firstOrCreate(
                    ['LIBN_ISBN' => $book['LIBN_ISBN']],
                    $book
                );
            } else {
                Libn::firstOrCreate(
                    ['LIBN_TITULLI' => $book['LIBN_TITULLI'], 'LIBN_AUTORI' => $book['LIBN_AUTORI']],
                    $book
                );
            }
        }
    }
}
