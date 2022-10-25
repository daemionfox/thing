<?php

namespace App\Entity;

use App\Repository\VoteEventRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VoteEventRepository::class)]
class VoteEvent
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $Name = null;

    #[ORM\Column]
    private ?int $StaffVotes = null;

    #[ORM\Column(nullable: true)]
    private ?int $MaxVendorVotes = null;

    #[ORM\Column(type: Types::DATETIMETZ_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $StartsOn = null;

    #[ORM\Column(type: Types::DATETIMETZ_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $EndsOn = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $CreatedBy = null;

    #[ORM\Column(type: Types::DATETIMETZ_MUTABLE)]
    private ?\DateTimeInterface $CreatedOn = null;

    #[ORM\OneToMany(mappedBy: 'VoteEvent', targetEntity: VoteItem::class, orphanRemoval: true)]
    private Collection $voteItems;

    #[ORM\Column]
    private ?bool $voteComplete = false;

    public function __construct()
    {
        $this->voteItems = new ArrayCollection();
        $this->CreatedOn = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->Name;
    }

    public function setName(string $Name): self
    {
        $this->Name = $Name;

        return $this;
    }

    public function getStaffVotes(): ?int
    {
        return $this->StaffVotes;
    }

    public function setStaffVotes(int $StaffVotes): self
    {
        $this->StaffVotes = $StaffVotes;

        return $this;
    }

    public function getMaxVendorVotes(): ?int
    {
        return $this->MaxVendorVotes;
    }

    public function setMaxVendorVotes(?int $MaxVendorVotes): self
    {
        $this->MaxVendorVotes = $MaxVendorVotes;

        return $this;
    }

    public function getStartsOn(): ?\DateTimeInterface
    {
        return $this->StartsOn;
    }

    public function setStartsOn(?\DateTimeInterface $StartsOn): self
    {
        $this->StartsOn = $StartsOn;

        return $this;
    }

    public function getEndsOn(): ?\DateTimeInterface
    {
        return $this->EndsOn;
    }

    public function setEndsOn(?\DateTimeInterface $EndsOn): self
    {
        $this->EndsOn = $EndsOn;

        return $this;
    }

    public function getCreatedBy(): ?User
    {
        return $this->CreatedBy;
    }

    public function setCreatedBy(?User $CreatedBy): self
    {
        $this->CreatedBy = $CreatedBy;

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

    /**
     * @return Collection<int, VoteItem>
     */
    public function getVoteItems(): Collection
    {
        return $this->voteItems;
    }

    public function addVoteItem(VoteItem $voteItem): self
    {
        if (!$this->voteItems->contains($voteItem)) {
            $this->voteItems->add($voteItem);
            $voteItem->setVoteEvent($this);
        }

        return $this;
    }

    public function removeVoteItem(VoteItem $voteItem): self
    {
        if ($this->voteItems->removeElement($voteItem)) {
            // set the owning side to null (unless already changed)
            if ($voteItem->getVoteEvent() === $this) {
                $voteItem->setVoteEvent(null);
            }
        }

        return $this;
    }

    public function isVoteComplete(): ?bool
    {
        return $this->voteComplete;
    }

    public function setVoteComplete(bool $voteComplete): self
    {
        $this->voteComplete = $voteComplete;

        return $this;
    }

}
