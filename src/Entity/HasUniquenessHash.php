<?php

namespace App\Entity;

interface HasUniquenessHash
{
    public function getUniquenessHash(): ?string;
}
