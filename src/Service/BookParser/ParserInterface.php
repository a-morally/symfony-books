<?php

namespace App\Service\BookParser;

interface ParserInterface
{
    public function parse(string $filepath): ParserResult;
}