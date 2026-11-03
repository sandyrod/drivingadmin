<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class CreateUser extends Command
{
    protected $signature = 'app:create-user
                            {--name= : Nombre del usuario}
                            {--email= : Correo con el que iniciará sesión}';

    protected $description = 'Crea un usuario que puede iniciar sesión en el panel';

    public function handle(): int
    {
        $name = $this->option('name') ?: $this->ask('Nombre');
        $email = $this->option('email') ?: $this->ask('Correo');
        $password = $this->secret('Contraseña (mínimo 8 caracteres)');
        $confirmation = $this->secret('Repite la contraseña');

        $validator = Validator::make(
            ['name' => $name, 'email' => $email, 'password' => $password, 'password_confirmation' => $confirmation],
            [
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255|unique:users,email',
                'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
            ]
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        User::create([
            'name' => $name,
            'email' => strtolower($email),
            'password' => $password, // hashed by the model cast
        ]);

        $this->info("Usuario {$email} creado. Ya puede iniciar sesión.");

        return self::SUCCESS;
    }
}
