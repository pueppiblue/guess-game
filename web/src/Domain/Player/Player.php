<?php
declare(strict_types=1);

namespace App\Domain\Player;

use App\Infrastructure\Repository\PlayerRepository;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;


#[ORM\Entity(repositoryClass: PlayerRepository::class)]
class Player implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\Column(type: "uuid", unique: true)]
    private UuidInterface $id;

    #[ORM\Column(length: 255, unique: true, nullable: true)]
    private ?string $username = null;

    #[ORM\Column(length: 255, unique: true, nullable: true)]
    private ?string $email = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $password = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?bool $isActive = true;

    #[ORM\Column(length: 255, nullable: false)]
    private int $point = 0;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $avatar = null;

    #[ORM\Column(type: "datetime", nullable: true)]
    private DateTimeInterface $createdAt;

    #[ORM\OneToMany(targetEntity: Guess::class, mappedBy: "player", cascade: ['persist', 'remove'])]
    private Collection $guesses;

    public function __construct()
    {
        $this->id = Uuid::uuid4();
        $this->avatar = "1";
        $this->point = 0;
        $this->createdAt = new \DateTimeImmutable();
        $this->guesses = new ArrayCollection();
    }

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setEmail(?string $email): void
    {
        $this->email = $email;
    }

    public function setPassword(?string $password): void
    {
        $this->password = $password;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function getIsActive(): bool
    {
        return $this->isActive;
    }

    public function getPoint(): int
    {
        return $this->point;
    }

    public function getAvatar(): string
    {
        return $this->avatar;
    }

    public function getCreatedAt(): DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setId(Uuid $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function setUsername(?string $username): static
    {
        $this->username = $username;

        return $this;
    }

    public function addGuess(Guess $guess): static
    {
        if (!$this->guesses->contains($guess)) {
            $this->guesses->add($guess);
            $guess->setPlayer($this);
        }

        return $this;
    }

    public function removeGuess(Guess $guess): static
    {
        if ($this->guesses->removeElement($guess)) {
            // set the owning side to null (unless already changed)
            if ($guess->getPlayer() === $this) {
                $guess->setPlayer(null);
            }
        }

        return $this;
    }

    public function getRoles(): array
    {
        return ['ROLE_USER'];
    }

    public function eraseCredentials(): void
    {
        $this->password = null;
    }

    public function getUserIdentifier(): string
    {
        return $this->email;
    }
}
