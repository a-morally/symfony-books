<?php

namespace App\Repository\Book;

use App\Entity\BookAuthor;
use App\Entity\BookCategory;

class SearchParams
{
    private ?string $title = null;
    private ?string $authorName = null;
    private ?string $publishmentStatus = null;

    private ?BookCategory $category = null;
    private ?BookAuthor $author = null;

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $value): static
    {
        $this->title = $value;
        return $this;
    }

    public function getAuthorName(): ?string
    {
        return $this->authorName;
    }

    public function setAuthorName(string $value): static
    {
        $this->authorName = $value;
        return $this;
    }

    public function getPublishmentStatus(): ?string
    {
        return $this->publishmentStatus;
    }

    public function setPublishmentStatus(string $value): static
    {
        $this->publishmentStatus = $value;
        return $this;
    }

    public function getCategory(): ?BookCategory
    {
        return $this->category;
    }

    public function setCategory(BookCategory $value): static
    {
        $this->category = $value;
        return $this;
    }

    public function getAuthor(): ?BookAuthor
    {
        return $this->author;
    }

    public function setAuthor(BookAuthor $value): static
    {
        $this->author = $value;
        return $this;
    }
}
