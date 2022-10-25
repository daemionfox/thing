<?php

namespace App\Entity;

use App\Repository\ActionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ActionRepository::class)]
class Action
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $User = null;

    #[ORM\Column(length: 255)]
    private ?string $Event = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $Action = null;

    #[ORM\Column(type: Types::DATETIMETZ_MUTABLE)]
    private ?\DateTimeInterface $CreatedOn = null;

    public function __construct(User $user = null, string $event=null, string $action=null, EntityManagerInterface $entityManager = null)
    {
        $this->CreatedOn = new \DateTime();
        $this->User = $user;
        $this->Action = $action;
        $this->Event = $event;

        if (!empty($entityManager)) {
            $entityManager->persist($this);
        }

    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->User;
    }

    public function setUser(?User $User): self
    {
        $this->User = $User;

        return $this;
    }

    public function getEvent(): ?string
    {
        return $this->Event;
    }

    public function setEvent(string $Event): self
    {
        $this->Event = $Event;

        return $this;
    }

    public function getAction(): ?string
    {
        return $this->Action;
    }

    public function setAction(?string $Action): self
    {
        $this->Action = $Action;

        return $this;
    }

    public function getCreatedOn(): ?\DateTimeInterface
    {
        return $this->CreatedOn;
    }

    public function setCreatedOn(\DateTimeInterface $CreatedOn): self
    {
        $this->CreatedOn = $CreatedOn;

        return $this;
    }
}
