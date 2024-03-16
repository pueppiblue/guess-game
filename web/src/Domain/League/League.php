<?php
declare(strict_types=1);

namespace App\Domain\League;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

#[ORM\Entity()]
final class League
{

    #[ORM\Id]
    #[ORM\Column(type: "uuid", unique: true)]
    private UuidInterface $id;

    #[ORM\Column(length: 255, unique: true, nullable: true)]
    private string $name;

    #[ORM\Column(length: 255, unique: true, nullable: true)]
    private string $sluggedName;

    #[ORM\Column(length: 255, unique: true, nullable: true)]
    private string $logo;

    #[ORM\Column(length: 255, unique: true, nullable: true)]
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