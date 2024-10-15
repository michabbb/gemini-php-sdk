<?php /** @noinspection StaticClosureCanBeUsedInspection */

/** @noinspection UnknownInspectionInspection */

use Gemini\Data\UploadedFile;
use Gemini\Enums\FileState;
use Gemini\Testing\ClientFake;

test('upload file', function () {
    $fakeResponse = [
        'file' => [
            'name'           => 'files/946lb0s2ns5h',
            'displayName'    => '145784_v11.pdf',
            'mimeType'       => 'application/pdf',
            'sizeBytes'      => '5737046',
            'createTime'     => '2024-10-13T16:08:26.011225Z',
            'updateTime'     => '2024-10-13T16:08:26.011225Z',
            'expirationTime' => '2024-10-15T16:08:25.990944495Z',
            'sha256Hash'     => 'MjZmZWVkMTYxYzM2NDkwOWY1YWMzN2UzN2JjMTkwNmUzNTg4ZWY4NDYzMzEwNGE5MzU3MjAyNzI0YjEzOGQ4Zg==',
            'uri'            => 'https://generativelanguage.googleapis.com/v1beta/files/946lb0s2ns5h',
            'state'          => 'ACTIVE',
        ],
    ];


    $fake = new ClientFake([
                               UploadedFile::from($fakeResponse),
                           ]);

    $filePath = tempnam(sys_get_temp_dir(), 'gemini-test-');
    file_put_contents($filePath, 'Test content');

    $response = $fake->fileManager()->uploadFile($filePath, 'My Test File', 'text/plain');

    unlink($filePath);

    /** @noinspection PhpUndefinedFieldInspection */
    expect($response)
        ->toBeInstanceOf(UploadedFile::class)
        ->name->toBe('files/946lb0s2ns5h')
        ->displayName->toBe('145784_v11.pdf')
        ->mimeType->toBe('application/pdf')
        ->sizeBytes->toBe('5737046')
        ->createTime->toBe('2024-10-13T16:08:26.011225Z')
        ->updateTime->toBe('2024-10-13T16:08:26.011225Z')
        ->expirationTime->toBe('2024-10-15T16:08:25.990944495Z')
        ->sha256Hash->toBe('MjZmZWVkMTYxYzM2NDkwOWY1YWMzN2UzN2JjMTkwNmUzNTg4ZWY4NDYzMzEwNGE5MzU3MjAyNzI0YjEzOGQ4Zg==')
        ->uri->toBe('https://generativelanguage.googleapis.com/v1beta/files/946lb0s2ns5h')
        ->state->toBe(FileState::ACTIVE);
});