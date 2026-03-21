<?php

declare(strict_types=1);

namespace App\Validators;

final class UpdateUserValidator
{
    private const PASSWORD_REGEX = '/^(?=.*[A-Z])(?=.*\d).{8,}$/';
    private const PASSWORD_MSG   = 'A senha deve ter no mínimo 8 caracteres, uma letra maiúscula e um número.';

    public function validate(array $data): array
    {
        $errors = [];

        if (array_key_exists('name', $data)) {
            $name = trim((string) $data['name']);
            if ($name === '') {
                $errors['name'] = ['não pode ser vazio'];
            }
        }

        if (array_key_exists('email', $data)) {
            $email = trim((string) $data['email']);
            if ($email === '') {
                $errors['email'] = ['não pode ser vazio'];
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = ['e-mail inválido'];
            }
        }

        if (array_key_exists('password', $data) && $data['password'] !== null && $data['password'] !== '') {
            if (!preg_match(self::PASSWORD_REGEX, (string) $data['password'])) {
                $errors['password'] = [self::PASSWORD_MSG];
            }
        }

        return $errors;
    }
}
