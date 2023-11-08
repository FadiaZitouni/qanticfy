<?php

namespace App\Entity;

use App\Repository\ItemRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ItemRepository::class)]
class Item
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $origin_id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $item_description = null;

    #[ORM\Column(length: 255)]
    private ?string $unit_code = null;

    #[ORM\Column(length: 255)]
    private ?string $unit_description = null;

    #[ORM\Column]
    private ?float $vat_percentage = null;

    #[ORM\OneToMany(mappedBy: 'item', targetEntity: CommandLine::class)]
    private Collection $commandLines;

    #[ORM\Column]
    private ?float $unit_price = null;

    public function __construct()
    {
        $this->commandLines = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOriginId(): ?string
    {
        return $this->origin_id;
    }

    public function setOriginId(string $origin_id): static
    {
        $this->origin_id = $origin_id;

        return $this;
    }

    public function getItemDescription(): ?string
    {
        return $this->item_description;
    }

    public function setItemDescription(string $item_description): static
    {
        $this->item_description = $item_description;

        return $this;
    }

    public function getUnitCode(): ?string
    {
        return $this->unit_code;
    }

    public function setUnitCode(string $unit_code): static
    {
        $this->unit_code = $unit_code;

        return $this;
    }

    public function getUnitDescription(): ?string
    {
        return $this->unit_description;
    }

    public function setUnitDescription(string $unit_description): static
    {
        $this->unit_description = $unit_description;

        return $this;
    }

    public function getVatPercentage(): ?float
    {
        return $this->vat_percentage;
    }

    public function setVatPercentage(float $vat_percentage): static
    {
        $this->vat_percentage = $vat_percentage;

        return $this;
    }

    /**
     * @return Collection<int, CommandLine>
     */
    public function getCommandLines(): Collection
    {
        return $this->commandLines;
    }

    public function addCommandLine(CommandLine $commandLine): static
    {
        if (!$this->commandLines->contains($commandLine)) {
            $this->commandLines->add($commandLine);
            $commandLine->setItem($this);
        }

        return $this;
    }

    public function removeCommandLine(CommandLine $commandLine): static
    {
        if ($this->commandLines->removeElement($commandLine)) {
            // set the owning side to null (unless already changed)
            if ($commandLine->getItem() === $this) {
                $commandLine->setItem(null);
            }
        }

        return $this;
    }

    public function getUnitPrice(): ?float
    {
        return $this->unit_price;
    }

    public function setUnitPrice(float $unit_price): static
    {
        $this->unit_price = $unit_price;

        return $this;
    }
}
