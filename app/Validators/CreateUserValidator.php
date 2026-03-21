<?php

declare(strict_types=1);

namespace App\Validators;

final class CreateUserValidator
{
    private const PASSWORD_REGEX = '/^(?=.*[A-Z])(?=.*\d).{8,}$/';
    private const PASSWORD_MSG   = 'A senha deve ter no mínimo 8 caracteres, uma letra maiúscula e um número.';

    public function validate(array $data): array
    {
        $errors = [];

        $name  = trim((string) ($data['name']  ?? ''));
        $email = trim((string) ($data['email'] ?? ''));
        $pass  = (string) ($data['password'] ?? '');

        if ($name === '') {
            $errors['name'] = ['obrigatório'];
        }

        if ($email === '') {
            $errors['email'] = ['obrigatório'];
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = ['e-mail inválido'];
        }

        if ($pass === '') {
            $errors['password'] = ['obrigatório'];
        } elseif (!preg_match(self::PASSWORD_REGEX, $pass)) {
            $errors['password'] = [self::PASSWORD_MSG];
        }

        return $errors;
    }
}
