<?php

namespace App\Console\Commands;

use App\Models\Student;
use App\Models\StudentLicense;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportStudents extends Command
{
    protected $signature = 'students:import
        {file=storage/app/imports/students-report.csv : Ruta al CSV}
        {--package=Importado : Paquete asignado a los estudiantes importados}
        {--transmission=Automático : Transmisión asignada}
        {--dry-run : Valida y muestra el resumen sin guardar nada}';

    protected $description = 'Importa estudiantes (y su permiso de aprendizaje) desde el reporte CSV';

    public function handle(): int
    {
        $path = $this->argument('file');
        if (!is_file($path)) {
            $path = base_path($path);
        }
        if (!is_file($path)) {
            $this->error("No se encontró el archivo: {$this->argument('file')}");
            return self::FAILURE;
        }

        $handle = fopen($path, 'r');
        $header = array_map(fn ($h) => trim($h, " \xEF\xBB\xBF"), fgetcsv($handle));

        $created = 0;
        $skipped = [];
        $warnings = [];
        $seenEmails = Student::withTrashed()->whereNotNull('email')->pluck('email')
            ->map(fn ($e) => strtolower($e))->flip()->all();
        $seenPermits = StudentLicense::pluck('license_number')->flip()->all();
        $seenContracts = Student::withTrashed()->whereNotNull('contract_number')
            ->pluck('contract_number')->flip()->all();

        DB::beginTransaction();
        try {
            $line = 1;
            while (($cols = fgetcsv($handle)) !== false) {
                $line++;
                if (count(array_filter($cols, fn ($c) => trim((string) $c) !== '')) === 0) {
                    continue;
                }
                $row = array_combine($header, array_pad(array_map('trim', $cols), count($header), ''));
                $name = $row['First Name'] . ' ' . $row['Last Name'];
                $permit = preg_replace('/\s+/', '', $row['Permit']);
                $contract = $row['Contract'];

                if ($contract !== '' && isset($seenContracts[$contract])) {
                    $skipped[] = "L{$line} {$name}: contrato {$contract} ya existe";
                    continue;
                }
                if ($permit !== '' && isset($seenPermits[$permit])) {
                    $skipped[] = "L{$line} {$name}: permiso {$permit} ya existe";
                    continue;
                }

                // Email: opcional y único
                $email = strtolower($row['Email']);
                if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $warnings[] = "L{$line} {$name}: email inválido '{$row['Email']}' (se guarda igual)";
                }
                if ($email !== '' && isset($seenEmails[$email])) {
                    $warnings[] = "L{$line} {$name}: email {$email} repetido, se deja vacío";
                    $email = '';
                }

                $gender = strtoupper($row['Sex']);
                if (!in_array($gender, ['M', 'F', 'O'], true)) {
                    $gender = null;
                }

                $address = trim(preg_replace('/\s+/', ' ', $row['Address1'] . ' ' . $row['Address2']));

                $student = new Student([
                    'contract_number' => $contract ?: null,
                    'first_name' => $this->title($row['First Name']),
                    'middle_name' => $this->title($row['Middle Name']) ?: null,
                    'last_name' => $this->title(trim($row['Last Name'] . ' ' . $row['Suffix'])),
                    'birth_date' => $this->date($row['Birth Date']),
                    'gender' => $gender,
                    'address' => mb_substr($address, 0, 255),
                    'city' => $this->title($row['City']),
                    'state' => strtoupper($row['State']),
                    'zip_code' => $row['Zip'],
                    'phone' => $row['Mobile'] ?: $row['Work Phone'],
                    'email' => $email ?: null,
                    'package' => $this->option('package'),
                    'transmission' => $this->option('transmission'),
                    'plan_cost' => 0,
                    'paid_amount' => 0,
                    'debt_amount' => 0,
                    'status' => 'active',
                ]);

                if (!$this->option('dry-run')) {
                    $student->save();
                }

                if ($permit !== '') {
                    $expires = $this->date($row['Permit Expires']);
                    if (!$expires) {
                        $warnings[] = "L{$line} {$name}: permiso sin fecha de vencimiento";
                    }
                    if (!$this->option('dry-run')) {
                        $student->licenses()->create([
                            'license_number' => $permit,
                            'expiration_date' => $expires,
                            'license_class' => 'Class D',
                            'is_active' => true,
                        ]);
                    }
                    $seenPermits[$permit] = true;
                }

                if ($email !== '') {
                    $seenEmails[$email] = true;
                }
                if ($contract !== '') {
                    $seenContracts[$contract] = true;
                }
                $created++;
            }

            $this->option('dry-run') ? DB::rollBack() : DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error("Error en la línea {$line}: {$e->getMessage()}");
            return self::FAILURE;
        } finally {
            fclose($handle);
        }

        foreach ($warnings as $w) {
            $this->warn($w);
        }
        foreach ($skipped as $s) {
            $this->line("<comment>Omitido</comment> {$s}");
        }

        $verb = $this->option('dry-run') ? 'Se importarían' : 'Importados';
        $this->info("{$verb}: {$created} estudiantes. Omitidos: " . count($skipped) . '. Avisos: ' . count($warnings) . '.');

        return self::SUCCESS;
    }

    private function date(string $value): ?string
    {
        if ($value === '') {
            return null;
        }
        try {
            return Carbon::createFromFormat('!m/d/Y', $value)->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }

    private function title(string $value): string
    {
        $value = trim(preg_replace('/\s+/', ' ', str_replace(',', ' ', $value)));
        return mb_convert_case(mb_strtolower($value), MB_CASE_TITLE, 'UTF-8');
    }
}
