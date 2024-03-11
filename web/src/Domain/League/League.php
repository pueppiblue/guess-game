<?php
declare(strict_types=1);

namespace App\Domain\League;

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

final class League
{

    private UuidInterface $id;

    private string $name;
    private string $sluggedName;
    private string $logo;
    private string $apiId;

    public function __construct()
    {
        $this->id = Uuid::uuid4();
    }

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function setId(UuidInterface $id): void
    {
        $this->id = $id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getSluggedName(): string
    {
        return $this->sluggedName;
    }

    public function setSluggedName(string $sluggedName): void
    {
        $this->sluggedName = $sluggedName;
    }

    public function getLogo(): string
    {
        return $this->logo;
    }

    public function setLogo(string $logo): void
    {
        $this->logo = $logo;
    }

    public function getApiId(): string
    {
        return $this->apiId;
    }

    public function setApiId(string $apiId): void
    {
        $this->apiId = $apiId;
    }
}