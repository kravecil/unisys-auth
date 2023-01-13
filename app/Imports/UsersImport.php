<?php

namespace App\Imports;

use App\Models\Department;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\ToCollection;

use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Imports\HeadingRowFormatter;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Validators\Failure;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Exception;


HeadingRowFormatter::default('none');

class UsersImport implements ToCollection, WithHeadingRow, SkipsOnFailure, WithCalculatedFormulas, SkipsOnError
{
    use Importable, SkipsFailures, SkipsErrors;



    private $rowNumber = 0;


    public function collection(Collection $rows)
    {

        foreach ($rows as $row) {
            $this->rowNumber++;
            $separator = " \t\n";
            if (!isset($row['Сотрудник'])) {
                return null;
            }
            $FIO = [];
            $FIOtok = strtok($row['Сотрудник'], $separator);

            while ($FIOtok) {
                $FIO[] = $FIOtok;
                $FIOtok = strtok($separator);
            }

            $row['lastname'] = $FIO[0] ?? "";
            $row['firstname'] = $FIO[1] ?? "";
            $row['middlename'] = $FIO[2] ?? "";
            $lastnameLatin = $this->rusToLat($row['lastname']);
            $firstnameLatin = $this->rusToLat($row['firstname']);
            $row['username'] = strtolower(substr($firstnameLatin, 0, 1) . "." . $lastnameLatin);
            $row['post'] = str_replace(' (30)', '', $row['Должность']);
            $departmentFull = $row['Подразделение'];
            preg_match('/^[0-9]{3}/', $departmentFull, $departmentNumber);
            if (empty($departmentNumber)) {
                $departmentTitle = $departmentFull;
                $row['department_id'] = Department::where('title', $departmentTitle)->first()->id;
            } else {
                $departmentTitle = substr($departmentFull, 4);
                $row['department_id'] = Department::where('number', $departmentNumber)->first()?->id;
            };
            $row['is_leader'] = (empty($row['is_leader']) ? true : $row['is_leader']);
            $row['password'] = $row['Пароль'];
            try {
                $validator = Validator::make($row->toArray(), [
                    'username' => ['required', 'unique:users,username', 'string', 'max:255'],
                    'password' => ['required', 'string', 'min:6'],
                    'lastname' => ['required', 'string', 'max:255'],
                    'firstname' => ['required', 'string', 'max:255'],
                    'middlename' => ['string', 'max:255'],
                    'is_leader' => ['required', 'boolean'],
                    'department_id' => ['required', 'int', 'max:255'],
                ])->validate();

                User::create([
                    'username' => $row['username'],
                    'password' => Hash::make($row['password']),
                    'lastname' => $row['lastname'],
                    'firstname' => $row['firstname'],
                    'middlename' => $row['middlename'],
                    'department_id' => $row['department_id'],
                    'is_leader' => $row['is_leader'],
                    'post' => $row['post'],
                ]);
            } catch (Exception $e) {
                ('error ' . $this->rowNumber);
                continue;
            }
        }
    }


    function rusToLat($source = false)
    {
        if ($source) {
            $rus = [
                'А', 'Б', 'В', 'Г', 'Д', 'Е', 'Ё', 'Ж', 'З', 'И', 'Й', 'К', 'Л', 'М', 'Н', 'О', 'П', 'Р', 'С', 'Т', 'У', 'Ф', 'Х', 'Ц', 'Ч', 'Ш', 'Щ', 'Ъ', 'Ы', 'Ь', 'Э', 'Ю', 'Я',
                'а', 'б', 'в', 'г', 'д', 'е', 'ё', 'ж', 'з', 'и', 'й', 'к', 'л', 'м', 'н', 'о', 'п', 'р', 'с', 'т', 'у', 'ф', 'х', 'ц', 'ч', 'ш', 'щ', 'ъ', 'ы', 'ь', 'э', 'ю', 'я'
            ];
            $lat = [
                'A', 'B', 'V', 'G', 'D', 'E', 'Yo', 'Zh', 'Z', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'R', 'S', 'T', 'U', 'F', 'H', 'C', 'Ch', 'Sh', 'Shch', 'Y', 'Y', 'Y', 'E', 'Yu', 'Ya',
                'a', 'b', 'v', 'g', 'd', 'e', 'yo', 'zh', 'z', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'r', 's', 't', 'u', 'f', 'h', 'c', 'ch', 'sh', 'shch', 'y', 'y', 'y', 'e', 'yu', 'ya'
            ];
            return str_replace($rus, $lat, $source);
        }
    }
}
