<?php

namespace App\Service\BookParser;

use Symfony\Component\HttpFoundation\File\File;

interface ParserInterface
{
    public function parse(File $file): ParserResult;
}
