<?php

declare(strict_types=1);

namespace Gemini\Resources;

use Gemini\Contracts\TransporterContract;
use Gemini\Data\UploadedFile;
use Gemini\Enums\Method;
use Gemini\Exceptions\ErrorException;
use Gemini\Exceptions\TransporterException;
use Gemini\Exceptions\UnserializableResponse;
use Gemini\Requests\FileManager\UploadFileRequest;
use Http\Discovery\Psr17Factory;
use JsonException;

final class FileManager
{
    public function __construct(
        private readonly TransporterContract $transporter
    )
    {
    }

    /**
     * @throws ErrorException
     * @throws TransporterException
     * @throws UnserializableResponse
     * @throws JsonException
     */
    public function uploadFile(string $filePath, string $displayName, string $mimeType): UploadedFile
    {
        $fileSize = filesize($filePath);

        $psr17Factory = new Psr17Factory();
        $fileStream   = $psr17Factory->createStreamFromFile($filePath);

        // Initial resumable request (using UploadFileRequest)
        $startRequest = (new UploadFileRequest($displayName, $fileStream, $mimeType, $fileSize))
            ->withHeaders([
                              'X-Goog-Upload-Protocol'              => 'resumable',
                              'X-Goog-Upload-Command'               => 'start',
                              'X-Goog-Upload-Header-Content-Length' => $fileSize,
                              'X-Goog-Upload-Header-Content-Type'   => $mimeType,
                              'Content-Type'                        => 'application/json',
                          ]);

        $startRequest->setUri('/upload/v1beta/files');

        $response  = $this->transporter->request($startRequest);
        $uploadUrl = $response->data()['X-Goog-Upload-URL'][0] ?? null;

        $queryString = parse_url($uploadUrl, PHP_URL_QUERY);
        parse_str($queryString, $queryParams);

        if (!$uploadUrl) {
            throw new \RuntimeException('Failed to get upload URL');
        }

        // Upload the actual bytes
        $psr17Factory           = new Psr17Factory();
        $fileStream             = $psr17Factory->createStreamFromFile($filePath);
        $uploadRequest          = new UploadFileRequest($displayName, $fileStream, $mimeType, $fileSize);
        $uploadRequest->method  = Method::POST;
        $uploadRequest->headers = [
            'Content-Length'        => $fileSize,
            'Content-Type'          => $mimeType,
            'X-Goog-Upload-Offset'  => 0,
            'X-Goog-Upload-Command' => 'upload, finalize',
        ];

        $uploadRequest->setUri($uploadUrl);
        $uploadRequest->setQueryParams($queryParams);

        $finalizeResponse = $this->transporter->request($uploadRequest);


        return UploadedFile::from($finalizeResponse->data());
    }
}