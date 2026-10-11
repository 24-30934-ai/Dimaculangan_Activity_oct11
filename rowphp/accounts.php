<?php
function account_file_path(): string
{
    return dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'rowphp-data' . DIRECTORY_SEPARATOR . 'accounts.json';
}

function open_account_file(int $lockType)
{
    $directory = dirname(account_file_path());
    if (!is_dir($directory) && !mkdir($directory, 0700, true) && !is_dir($directory)) {
        throw new RuntimeException('Could not create the account storage folder.');
    }

    $file = fopen(account_file_path(), 'c+');
    if ($file === false) {
        throw new RuntimeException('Could not open the account storage file.');
    }
    if (!flock($file, $lockType)) {
        fclose($file);
        throw new RuntimeException('Could not lock the account storage file.');
    }

    return $file;
}

function read_accounts($file): array
{
    rewind($file);
    $contents = stream_get_contents($file);
    if ($contents === false) {
        throw new RuntimeException('Could not read the account storage file.');
    }
    if (trim($contents) === '') {
        return [];
    }

    $accounts = json_decode($contents, true);
    if (!is_array($accounts)) {
        throw new RuntimeException('The account storage file contains invalid data.');
    }

    return $accounts;
}

function create_account(string $firstName, string $lastName, string $email, string $password): bool
{
    $file = open_account_file(LOCK_EX);
    try {
        $accounts = read_accounts($file);
        foreach ($accounts as $account) {
            if (isset($account['email']) && strcasecmp($account['email'], $email) === 0) {
                return false;
            }
        }

        $accounts[] = [
            'firstName' => $firstName,
            'lastName' => $lastName,
            'email' => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT),
        ];

        $contents = json_encode($accounts, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR);
        rewind($file);
        if (!ftruncate($file, 0) || fwrite($file, $contents) !== strlen($contents) || !fflush($file)) {
            throw new RuntimeException('Could not save the account.');
        }

        return true;
    } finally {
        flock($file, LOCK_UN);
        fclose($file);
    }
}

function account_login_is_valid(string $email, string $password): bool
{
    $file = open_account_file(LOCK_SH);
    try {
        foreach (read_accounts($file) as $account) {
            if (
                isset($account['email'], $account['password'])
                && strcasecmp($account['email'], $email) === 0
                && password_verify($password, $account['password'])
            ) {
                return true;
            }
        }

        return false;
    } finally {
        flock($file, LOCK_UN);
        fclose($file);
    }
}
