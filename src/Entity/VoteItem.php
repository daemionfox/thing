<?php

namespace App\Entity;

use App\Repository\VoteItemRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VoteItemRepository::class)]
class VoteItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'voteItems')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $User = null;

    #[ORM\ManyToOne(inversedBy: 'voteItems')]
    #[ORM\JoinColumn(nullable: false)]
    private ?VoteEvent $VoteEvent = null;

    #[ORM\ManyToOne(inversedBy: 'voteItems')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Vendor $Vendor = null;

    #[ORM\Column]
    private ?int $Votes = null;

    #[ORM\Column(type: Types::DATETIMETZ_MUTABLE)]
    private ?\DateTimeInterface $CreatedOn = null;

    #[ORM\Column]
    private ?bool $isSkip = false;

    private ?int $maxVotes = null;

    public function __construct()
    {
        $this->CreatedOn = new \DateTime();
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

    public function getVoteEvent(): ?VoteEvent
    {
        return $this->VoteEvent;
    }

    public function setVoteEvent(?VoteEvent $VoteEvent): self
    {
        $this->VoteEvent = $VoteEvent;

        return $this;
    }

    public function getVendor(): ?Vendor
    {
        return $this->Vendor;
    }

    public function setVendor(?Vendor $Vendor): self
    {
        $this->Vendor = $Vendor;

        return $this;
    }

    public function getVotes(): ?int
    {
        return $this->Votes;
    }

    public function setVotes(int $Votes): self
    {
        $this->Votes = $Votes;

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

    public function isIsSkip(): ?bool
    {
        return $this->isSkip;
    }

    public function setIsSkip(bool $isSkip): self
    {
        $this->isSkip = $isSkip;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getMaxVotes(): ?int
    {
        return $this->maxVotes;
    }

    /**
     * @param int|null $maxVotes
     */
    public function setMaxVotes(?int $maxVotes): self
    {
        $this->maxVotes = $maxVotes;
        return $this;
    }



    public function __toString()
    {
        $temp = [
            'id' => $this->id,
            'vendor' => $this->Vendor->getId(),
            'event' => $this->VoteEvent->getId(),
            'user' => $this->User->getId(),
            'votes' => $this->getVotes(),
            'skipped' => $this->isSkip === true ? '1' : '0'
        ];

        return json_encode($temp, JSON_PRETTY_PRINT);
    }
}
