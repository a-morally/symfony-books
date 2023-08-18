<?php

namespace App\Service\Upload;

use Exception;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;
use App\Service\Upload\Exception\UploadException;
use App\Service\Upload\Exception\UrlNotFoundException;

class FileUploader
{
    public function __construct(private string $targetDirectory, private Filesystem $fs)
    {
    }

    /**
     * @param bool $copy should file be copied or just moved
     *
     * @throws UploadException
     *
     * @return string upload filename
     */
    public function upload(File $file, bool $copy = true): string
    {
        $targetFilename = $this->generateName() . '.' . $file->guessExtension();
        $targetDirectory = $this->getTargetDirectory();
        $targetPath = $targetDirectory . '/' . $targetFilename;

        try {
            if ($copy) {
                $this->fs->copy($file->getRealPath(), $targetPath);
            } else {
                $file->move($targetDirectory, $targetFilename);
            }
        } catch (Exception $e) {
            throw new UploadException($e->getMessage());
        }

        return $targetFilename;
    }

    /**
     * @throws UploadException
     * @throws UrlNotFoundException
     *
     * @return string upload filename
     */
    public function uploadFromUrl(string $url): string
    {
        try {
            $contents = file_get_contents($url);
        } catch (Exception $e) {
            throw new UploadException($e->getMessage());
        }

        if ($contents === false) {
            throw new UrlNotFoundException();
        }

        try {
            $tempFile = $this->fs->tempnam(sys_get_temp_dir(), 'upload_');
        } catch (Exception $e) {
            throw new UploadException($e->getMessage());
        }

        file_put_contents($tempFile, $contents);
        return $this->upload(new File($tempFile));
    }

    public function getTargetDirectory(): string
    {
        return $this->targetDirectory;
    }

    private function generateName(): string
    {
        return md5(uniqid() . microtime());
    }
}
