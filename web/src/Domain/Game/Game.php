<?php
declare(strict_types=1);

namespace App\Domain\Game;

use App\Domain\League\League;
use App\Domain\Player\Guess;
use App\Domain\Team\Team;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\JoinColumn;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity()]
#[UniqueEntity(fields: ['homeTeam', 'visitingTeam', 'gameTime'])]
final class Game
{
    #[ORM\Id]
    #[ORM\Column(type: "uuid", unique: true)]
    private UuidInterface $id;

    #[ORM\Column(length: 255, unique: true, nullable: true)]
    private ?string $name = null;

    #[ORM\Column(type: "datetime", nullable: true)]
    private DateTimeInterface $gameTime;

    #[ORM\Column(length: 255, unique: true, nullable: true)]
    private string $score;

    #[ORM\ManyToOne(targetEntity: League::class)]
    #[JoinColumn(name: 'league_id', referencedColumnName: 'id' )]
    private League $league;

    #[ORM\ManyToOne(targetEntity: Team::class)]
    #[JoinColumn(name: 'home_team_id', referencedColumnName: 'id')]
    private Team $homeTeam;

    #[ORM\ManyToOne(targetEntity: Team::class)]
    #[JoinColumn(name: 'visiting_team_id', referencedColumnName: 'id')]
    private Team $visitingTeam;

    #[ORM\OneToMany(targetEntity: Guess::class, mappedBy: 'game' )]
    private Collection $guesses;

    public function __construct()
    {
        $this->id = Uuid::uuid4();
        $this->guesses = new ArrayCollection();
    }

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getGameTime(): DateTimeInterface
    {
        return $this->gameTime;
    }

    public function setGameTime(DateTimeInterface $gameTime): void
    {
        $this->gameTime = $gameTime;
    }

    public function getScore(): string
    {
        return $this->score;
    }

    public function setScore(string $score): void
    {
        $this->score = $score;
    }

    public function getLeague(): League
    {
        return $this->league;
    }

    public function setLeague(League $league): void
    {
        $this->league = $league;
    }

    public function getHomeTeam(): Team
    {
        return $this->homeTeam;
    }

    public function setHomeTeam(Team $homeTeam): void
    {
        $this->homeTeam = $homeTeam;
    }

    public function getVisitingTeam(): Team
    {
        return $this->visitingTeam;
    }

    public function setVisitingTeam(Team $visitingTeam): void
    {
        $this->visitingTeam = $visitingTeam;
    }

    public function addGuess(Guess $guess): static
    {
        if (!$this->guesses->contains($guess)) {
            $this->guesses->add($guess);
            $guess->setGame($this);
        }

        return $this;
    }

    public function removeGuess(Guess $guess): static
    {
        if($this->guesses->removeElement($guess)) {
            if($guess->getGame() === $this) {
                $guess->setGame(null);
            }
        }

        return $this;
    }
}
