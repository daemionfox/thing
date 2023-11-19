<?php

namespace App\Entity;

use App\Enumerations\TableCategoryEnumeration;
use App\Enumerations\TableTypeEnumeration;
use App\Enumerations\VendorStatusEnumeration;
use App\Repository\VendorRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VendorRepository::class)]
class Vendor
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: Types::DATETIMETZ_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $registrationdate = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $taxid = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $productsAndServices = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $rating = null;

    #[ORM\Column]
    private ?bool $isMature = false;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $website = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $twitter = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $tableRequestType = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $seatingRequests = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $neighborRequests = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $otherRequests = null;

    #[ORM\OneToOne(mappedBy: 'vendor', cascade: ['persist', 'remove'])]
    private ?VendorContact $vendorContact = null;

    #[ORM\OneToOne(mappedBy: 'vendor', cascade: ['persist', 'remove'])]
    private ?VendorAddress $vendorAddress = null;

    #[ORM\OneToMany(mappedBy: 'vendor', targetEntity: VendorImage::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $vendorImages;

    #[ORM\OneToMany(mappedBy: 'vendor', targetEntity: VendorCategory::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $vendorCategories;

    #[ORM\Column(type: Types::DATETIMETZ_MUTABLE)]
    private ?\DateTimeInterface $createdon = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $regfoxid = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $ImageBlock = null;

    #[ORM\Column(nullable: true)]
    private ?float $TableAmount = 0;

    #[ORM\Column(nullable: true)]
    private ?int $NumAssistants = 0;

    #[ORM\Column(nullable: true)]
    private ?float $AssistantAmount = 0;

    #[ORM\Column]
    private ?bool $hasEndcap = false;

    #[ORM\Column(length: 255)]
    private ?string $status = VendorStatusEnumeration::STATUS_NOTAPPROVED;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $imageURLS = null;

    #[ORM\OneToMany(mappedBy: 'Vendor', targetEntity: VoteItem::class, orphanRemoval: true)]
    private Collection $voteItems;

    private int $eventScore;

    #[ORM\Column(options: [ "default" => false ])]
    private ?bool $MatureDealersSection = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $tableCategory = null;

    #[ORM\OneToMany(mappedBy: 'vendor', targetEntity: VendorNote::class, orphanRemoval: true)]
    private Collection $vendorNotes;


    public function __construct()
    {
        $this->vendorImages = new ArrayCollection();
        $this->vendorCategories = new ArrayCollection();
        $this->createdon = new \DateTime();
        $this->voteItems = new ArrayCollection();
        $this->vendorNotes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getRegistrationdate(): ?\DateTimeInterface
    {
        return $this->registrationdate;
    }

    public function setRegistrationdate(?\DateTimeInterface $registrationdate): self
    {
        $this->registrationdate = $registrationdate;

        return $this;
    }

    public function getTaxid(): ?string
    {
        return $this->taxid;
    }

    public function setTaxid(?string $taxid): self
    {
        $this->taxid = $taxid;

        return $this;
    }

    public function getProductsAndServices(): ?string
    {
        return $this->productsAndServices;
    }

    public function setProductsAndServices(?string $productsAndServices): self
    {
        $this->productsAndServices = $productsAndServices;

        return $this;
    }

    public function getRating(): ?string
    {
        return $this->rating;
    }

    public function setRating(?string $rating): self
    {
        $this->rating = $rating;

        return $this;
    }

    public function isIsMature(): ?bool
    {
        return $this->isMature;
    }

    public function setIsMature(bool $isMature): self
    {
        $this->isMature = $isMature;

        return $this;
    }

    public function getWebsite(): ?string
    {
        return $this->website;
    }

    public function setWebsite(?string $website): self
    {
        $this->website = substr($website, 0, 250);

        return $this;
    }

    public function getTwitter(): ?string
    {
        return $this->twitter;
    }

    public function setTwitter(?string $twitter): self
    {
        $this->twitter = substr($twitter, 0, 250);

        return $this;
    }

    public function getTableRequestType(): ?string
    {
        return $this->tableRequestType;
    }

    public function setTableRequestType(?string $tableRequestType): self
    {
        $this->tableRequestType = $tableRequestType;

        return $this;
    }

    public function getSeatingRequests(): ?string
    {
        return $this->seatingRequests;
    }

    public function setSeatingRequests(?string $seatingRequests): self
    {
        $this->seatingRequests = $seatingRequests;

        return $this;
    }

    public function getNeighborRequests(): ?string
    {
        return $this->neighborRequests;
    }

    public function setNeighborRequests(?string $neighborRequests): self
    {
        $this->neighborRequests = $neighborRequests;

        return $this;
    }

    public function getOtherRequests(): ?string
    {
        return $this->otherRequests;
    }

    public function setOtherRequests(?string $otherRequests): self
    {
        $this->otherRequests = $otherRequests;

        return $this;
    }

    public function getVendorContact(): ?VendorContact
    {
        return $this->vendorContact;
    }

    public function setVendorContact(VendorContact $vendorContact): self
    {
        // set the owning side of the relation if necessary
        if ($vendorContact->getVendor() !== $this) {
            $vendorContact->setVendor($this);
        }

        $this->vendorContact = $vendorContact;

        return $this;
    }

    public function getVendorAddress(): ?VendorAddress
    {
        return $this->vendorAddress;
    }

    public function setVendorAddress(VendorAddress $vendorAddress): self
    {
        // set the owning side of the relation if necessary
        if ($vendorAddress->getVendor() !== $this) {
            $vendorAddress->setVendor($this);
        }

        $this->vendorAddress = $vendorAddress;

        return $this;
    }

    /**
     * @return Collection<int, VendorImage>
     */
    public function getVendorImages(): Collection
    {
        return $this->vendorImages;
    }

    public function addVendorImage(VendorImage $vendorImage): self
    {
        if (!$this->vendorImages->contains($vendorImage)) {
            $this->vendorImages->add($vendorImage);
            $vendorImage->setVendor($this);
        }

        return $this;
    }

    public function removeVendorImage(VendorImage $vendorImage): self
    {
        if ($this->vendorImages->removeElement($vendorImage)) {
            // set the owning side to null (unless already changed)
            if ($vendorImage->getVendor() === $this) {
                $vendorImage->setVendor(null);
            }
        }

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

    /**
     * @return Collection<int, VendorCategory>
     */
    public function getVendorCategories(): Collection
    {
        return $this->vendorCategories;
    }

    public function addVendorCategory(VendorCategory $vendorCategory): self
    {
        if (!$this->vendorCategories->contains($vendorCategory)) {
            $this->vendorCategories->add($vendorCategory);
            $vendorCategory->setVendor($this);
        }

        return $this;
    }

    public function removeVendorCategory(VendorCategory $vendorCategory): self
    {
        if ($this->vendorCategories->removeElement($vendorCategory)) {
            // set the owning side to null (unless already changed)
            if ($vendorCategory->getVendor() === $this) {
                $vendorCategory->setVendor(null);
            }
        }

        return $this;
    }

    public function getRegfoxid(): ?string
    {
        return $this->regfoxid;
    }

    public function setRegfoxid(string $regfoxid): self
    {
        $this->regfoxid = $regfoxid;

        return $this;
    }

    public function getImageBlock(): ?string
    {
        return $this->ImageBlock;
    }

    public function setImageBlock(?string $ImageBlock): self
    {
        $this->ImageBlock = $ImageBlock;

        return $this;
    }

    public function getTableAmount(): ?float
    {
        return $this->TableAmount;
    }

    public function setTableAmount(?float $TableAmount): self
    {
        $this->TableAmount = $TableAmount;

        return $this;
    }

    public function getNumAssistants(): ?int
    {
        return $this->NumAssistants;
    }

    public function setNumAssistants(?int $NumAssistants): self
    {
        $this->NumAssistants = $NumAssistants;

        return $this;
    }

    public function getAssistantAmount(): ?float
    {
        return $this->AssistantAmount;
    }

    public function setAssistantAmount(?float $AssistantAmount): self
    {
        $this->AssistantAmount = $AssistantAmount;

        return $this;
    }

    public function isHasEndcap(): ?bool
    {
        return $this->hasEndcap;
    }

    public function setHasEndcap(bool $hasEndcap): self
    {
        $this->hasEndcap = $hasEndcap;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getImageURLS(): ?string
    {
        return $this->imageURLS;
    }

    public function setImageURLS(?string $imageURLS): self
    {
        $this->imageURLS = $imageURLS;

        return $this;
    }

    /**
     * @return Collection<int, VoteItem>
     */
    public function getVoteItems(): Collection
    {
        return $this->voteItems;
    }

    public function setVoteItems(Collection $items): self
    {
        $this->voteItems = $items;
        return $this;
    }

    public function addVoteItem(VoteItem $voteItem): self
    {
        if (!$this->voteItems->contains($voteItem)) {
            $this->voteItems->add($voteItem);
            $voteItem->setVendor($this);
        }

        return $this;
    }

    public function removeVoteItem(VoteItem $voteItem): self
    {
        if ($this->voteItems->removeElement($voteItem)) {
            // set the owning side to null (unless already changed)
            if ($voteItem->getVendor() === $this) {
                $voteItem->setVendor(null);
            }
        }

        return $this;
    }

    /**
     * @param int $voteEvent
     * @return array
     */
    public function getVoteEventItems(int $voteEvent): ArrayCollection
    {
        $items = new ArrayCollection();
        $voteItems = $this->getVoteItems();
        /**
         * @var VoteItem $vi
         */
        foreach ($voteItems as $vi) {
            if ($vi->getVoteEvent()->getId() === $voteEvent) {
                $items->add($vi);
            }
        }
        return $items;
    }

    /**
     * @return int
     */
    public function getEventScore(): int
    {
        return $this->eventScore;
    }

    /**
     * @param int $eventScore
     */
    public function setEventScore(int $eventScore): Vendor
    {
        $this->eventScore = $eventScore;
        return $this;
    }

    public function calculateEventScore(int $voteEvent): Vendor
    {
        $items = $this->getVoteEventItems($voteEvent);
        $total = 0;
        /**
         * @var VoteItem $item
         */
        foreach ($items as $item) {
            $total += $item->getVotes();
        }
        $this->setEventScore($total);
        return $this;
    }

    public function getTableScore()
    {
        return TableTypeEnumeration::getSize($this->tableRequestType);
    }


    public function isMatureDealersSection(): ?bool
    {
        return $this->MatureDealersSection;
    }

    public function setMatureDealersSection(bool $MatureDealersSection): self
    {
        $this->MatureDealersSection = $MatureDealersSection;

        return $this;
    }

    public function getTableCategory(): ?string
    {
        return $this->tableCategory;
    }

    public function setTableCategory(?string $tableCategory): self
    {
        $this->tableCategory = $tableCategory;

        return $this;
    }

    public function detectTableCategory(): self
    {
        $this->tableCategory = TableCategoryEnumeration::category($this);
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
            $vendorNote->setVendor($this);
        }

        return $this;
    }

    public function removeVendorNote(VendorNote $vendorNote): self
    {
        if ($this->vendorNotes->removeElement($vendorNote)) {
            // set the owning side to null (unless already changed)
            if ($vendorNote->getVendor() === $this) {
                $vendorNote->setVendor(null);
            }
        }

        return $this;
    }


}
