<?php

namespace App\Entity;

use App\Repository\SubscriberRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=SubscriberRepository::class)
 */
class Subscriber
{

  const CATEGORIES = ['cat1', 'cat2', 'cat3'];

    /**
     * @ORM\Id
     * @ORM\Column(type="string", length=255)
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $Name;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $subscription;

    /**
     * @ORM\Column(type="date")
     */
    private $date;

    /**
     * @ORM\Column(type="array")
     * @Assert\Choice({"cat1", "cat2", "cat3"})
     */
    private $categories = [];

  public function setId($mail): self
  {
    $this->id = $mail;

    return $this;
  }

    public function getId(): ?string
    {
        return $this->email;
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

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getSubscription(): ?string
    {
        return $this->subscription;
    }

    public function setSubscription(string $subscription): self
    {
        $this->subscription = $subscription;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getCategories(): ?array
    {
        return $this->categories;
    }

    public function setCategories(array $categories): self
    {
        $this->categories = $categories;

        return $this;
    }

    public function toArray() {
      return [
        'id' => $this->getId(),
        'name' => $this->getName(),
        'email' => $this->getEmail(),
        'date' => $this->getDate(),
        'categories' => $this->getCategories(),
        'subscription' => $this->getSubscription(),
      ];
    }

}
