<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
class User
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "name can't be blanck")]
    #[Assert\Length(min: 1, max: 255, minMessage: "your name must have between 1 and 255 characters", maxMessage: "your name must have between 1 and 255 characters")]
    #[Assert\Regex(pattern: "/\d/", match: false, message: "Your name cannot contain a number")]
    #[Assert\Type(type: "string", message: "{{ value }} is not a valid {{ type }}")]
    #[Groups(["getCurentJob", "getEmployeesOf"])]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "firstname can't be blanck")]
    #[Assert\Length(min: 1, max: 255, minMessage: "your name must have between 1 and 255 characters", maxMessage: "your name must have between 1 and 255 characters")]
    #[Assert\Regex(pattern: "/\d/", match: false, message: "Your name cannot contain a number")]
    #[Assert\Type(type: "string", message: "{{ value }} is not a valid {{ type }}")]
    #[Groups(["getCurentJob", "getEmployeesOf"])]
    private ?string $firstName = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $birthdate = null;

    /**
     * @var Collection<int, Job>
     */
    #[ORM\OneToMany(targetEntity: Job::class, mappedBy: 'employee', orphanRemoval: true)]
    private Collection $jobs;

    public function __construct()
    {
        $this->jobs = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getfirstName(): ?string
    {
        return $this->firstName;
    }

    public function setfirstName(string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getBirthdate(): ?\DateTimeInterface
    {
        return $this->birthdate;
    }

    public function setBirthdate(\DateTimeInterface $birthdate): static
    {
        $this->birthdate = $birthdate;

        return $this;
    }

    /**
     * @return Collection<int, Job>
     */
    public function getJobs(): Collection
    {
        return $this->jobs;
    }

    public function addJob(Job $job): static
    {
        if (!$this->jobs->contains($job)) {
            $this->jobs->add($job);
            $job->setEmployee($this);
        }

        return $this;
    }

    public function removeJob(Job $job): static
    {
        if ($this->jobs->removeElement($job)) {
            // set the owning side to null (unless already changed)
            if ($job->getEmployee() === $this) {
                $job->setEmployee(null);
            }
        }

        return $this;
    }
}
