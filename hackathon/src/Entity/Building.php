<?php

namespace App\Entity;

use App\Repository\BuildingRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=BuildingRepository::class)
 */
class Building
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $Name;

    /**
     * @ORM\OneToMany(targetEntity=Room::class, mappedBy="building")
     */
    private $Rooms;

    public function __construct()
    {
        $this->Rooms = new ArrayCollection();
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

    /**
     * @return Collection|Room[]
     */
    public function getRooms(): Collection
    {
        return $this->Rooms;
    }

    public function addRoom(Room $room): self
    {
        if (!$this->Rooms->contains($room)) {
            $this->Rooms[] = $room;
            $room->setBuilding($this);
        }

        return $this;
    }

    public function removeRoom(Room $room): self
    {
        if ($this->Rooms->removeElement($room)) {
            // set the owning side to null (unless already changed)
            if ($room->getBuilding() === $this) {
                $room->setBuilding(null);
            }
        }

        return $this;
    }
}
