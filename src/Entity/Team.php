<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use \JsonSerializable;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TeamRepository")
 */
class Team implements JsonSerializable
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $strip;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\League", inversedBy="teams")
     */
    private $league;

    /**
     * @param string      $name
     * @param string      $strip
     * @param League|null $league
     *
     * @return Team
     */
    public static function create(string $name, string $strip, ?League $league = null): Team
    {
        $team = new self();
        $team->setName($name)
            ->setStrip($strip)
            ->setLeague($league);

        return $team;
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return null|string
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return Team
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getStrip(): ?string
    {
        return $this->strip;
    }

    /**
     * @param string $strip
     *
     * @return Team
     */
    public function setStrip(string $strip): self
    {
        $this->strip = $strip;

        return $this;
    }

    /**
     * @return League|null
     */
    public function getLeague(): ?League
    {
        return $this->league;
    }

    /**
     * @param League|null $league
     *
     * @return Team
     */
    public function setLeague(?League $league): self
    {
        $this->league = $league;

        return $this;
    }

    /**
     * Specify data which should be serialized to JSON
     *
     * @link  https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize(): array
    {
        return [
            'name'   => $this->getName(),
            'strip'  => $this->getStrip(),
            'league' => $this->getLeague()->getName(),
        ];
    }
}
