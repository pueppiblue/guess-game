<?php
declare(strict_types=1);

namespace App\Domain\Player;

use App\Domain\Game\Game;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;

class Guess
{
    #[ORM\Column(length: 255, unique: true, nullable: true)]
    private Uuid $id;

    #[ORM\Column(length: 255, unique: true, nullable: true)]
    private string $guess;

    #[ORM\Column(length: 255, unique: true, nullable: true)]
    private \DateTimeInterface $createdAt;

    #[ORM\ManyToOne(targetEntity: Game::class, inversedBy: 'guesses')]
    private ?Game $game;

    #[ORM\ManyToOne(targetEntity: Player::class, inversedBy: 'guesses')]
    private ?Player $player;

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function setId(Uuid $id): void
    {
        $this->id = $id;
    }

    public function getGuess(): string
    {
        return $this->guess;
    }

    public function setGuess(string $guess): void
    {
        $this->guess = $guess;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getGame(): Game
    {
        return $this->game;
    }

    public function setGame(?Game $game): void
    {
        $this->game = $game;
    }

    public function getPlayer(): Player
    {
        return $this->player;
    }

    public function setPlayer(?Player $player): void
    {
        $this->player = $player;
    }
}