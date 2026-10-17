<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

#[Signature('admin:create')]
#[Description('Crea una cuenta de administrador para acceder al panel.')]
class CreateAdminUser extends Command
{
    public function handle(): int
    {
        if (! $this->input->isInteractive()) {
            $this->error('Ejecuta este comando en una terminal interactiva para ingresar la contraseña de forma segura.');

            return self::FAILURE;
        }

        $name = trim((string) $this->ask('Nombre del administrador'));

        if ($name === '') {
            $this->error('El nombre es obligatorio.');

            return self::FAILURE;
        }

        do {
            $email = Str::lower(trim((string) $this->ask('Correo electrónico')));

            if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
                $this->error('Ingresa un correo electrónico válido.');
            }
        } while (filter_var($email, FILTER_VALIDATE_EMAIL) === false);

        if (User::where('email', $email)->exists()) {
            $this->error('Ya existe una cuenta con ese correo. No se modificó.');

            return self::FAILURE;
        }

        $password = (string) $this->secret('Contraseña (mínimo 12 caracteres)');

        if (strlen($password) < 12) {
            $this->error('La contraseña debe tener al menos 12 caracteres.');

            return self::FAILURE;
        }

        if (! hash_equals($password, (string) $this->secret('Confirma la contraseña'))) {
            $this->error('Las contraseñas no coinciden. No se creó la cuenta.');

            return self::FAILURE;
        }

        User::create([
            'name' => $name,
            'email' => $email,
            'password' => $password,
        ]);

        $this->info("Cuenta de administrador creada para {$email}.");

        return self::SUCCESS;
    }
}
