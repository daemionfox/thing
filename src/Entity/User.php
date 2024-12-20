<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    private ?string $email = null;

    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $title = null;

    #[ORM\Column]
    private ?bool $isVerified = false;

    #[ORM\Column(type: Types::DATETIMETZ_MUTABLE, options: [ "default" => "CURRENT_TIMESTAMP"])]
    private ?\DateTimeInterface $createdon = null;

    #[ORM\Column]
    private ?bool $isDeleted = false;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Message::class)]
    private Collection $messages;

    #[ORM\OneToMany(mappedBy: 'User', targetEntity: VoteItem::class, orphanRemoval: true)]
    private Collection $voteItems;

    #[ORM\OneToMany(mappedBy: 'owner', targetEntity: VendorNote::class, orphanRemoval: true)]
    private Collection $vendorNotes;


    public function __construct()
    {
        $this->createdon = new \DateTime();
        $this->messages = new ArrayCollection();
        $this->voteItems = new ArrayCollection();
        $this->vendorNotes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function isIsVerified(): ?bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): self
    {
        $this->isVerified = $isVerified;

        return $this;
    }

    public function getCreatedon(): ?\DateTimeInterface
    {
        return $this->createdon;
    }

    public function setCreatedon(\DateTimeInterface $createdon): self
    {
        $this->createdon = $createdon;

        return $this;
    }

    public function isIsDeleted(): ?bool
    {
        return $this->isDeleted;
    }

    public function setIsDeleted(bool $isDeleted): self
    {
        $this->isDeleted = $isDeleted;

        return $this;
    }

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    /**
     * @return Collection<int, Message>
     */
    public function getMessages(): Collection
    {
        return $this->messages;
    }

    public function addMessage(Message $message): self
    {
        if (!$this->messages->contains($message)) {
            $this->messages->add($message);
            $message->setUser($this);
        }

        return $this;
    }

    public function removeMessage(Message $message): self
    {
        if ($this->messages->removeElement($message)) {
            // set the owning side to null (unless already changed)
            if ($message->getUser() === $this) {
                $message->setUser(null);
            }
        }

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
            $voteItem->setUser($this);
        }

        return $this;
    }

    public function removeVoteItem(VoteItem $voteItem): self
    {
        if ($this->voteItems->removeElement($voteItem)) {
            // set the owning side to null (unless already changed)
            if ($voteItem->getUser() === $this) {
                $voteItem->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, VendorNote>
     */
    public function getVendorNotes(): Collection
    {
        return $this->vendorNotes;
    }

    public function addVendorNote(VendorNote $vendorNote): self
    {
        if (!$this->vendorNotes->contains($vendorNote)) {
            $this->vendorNotes->add($vendorNote);
            $vendorNote->setOwner($this);
        }

        return $this;
    }

    public function removeVendorNote(VendorNote $vendorNote): self
    {
        if ($this->vendorNotes->removeElement($vendorNote)) {
            // set the owning side to null (unless already changed)
            if ($vendorNote->getOwner() === $this) {
                $vendorNote->setOwner(null);
            }
        }

        return $this;
    }

}
