<?php

namespace App\Repository;

use App\Entity\Subscriber;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Yaml\Parser as YamlParser;
use Symfony\Component\Yaml\Yaml;
use function Symfony\Component\DependencyInjection\Loader\Configurator\env;

/**
 * @method Subscriber|null find($id, $lockMode = null, $lockVersion = null)
 * @method Subscriber|null findOneBy(array $criteria, array $orderBy = null)
 * @method Subscriber[]    findAll()
 */
class SubscriberRepository extends ServiceEntityRepository
{
    private $file;

    public function __construct(ManagerRegistry $registry, string $projectDir) {
        parent::__construct($registry, Subscriber::class);
        $this->yamlParser = new YamlParser();
        $this->file = $projectDir . '/var/subscribers.yaml';
        $filesystem = new Filesystem();
        if (!$filesystem->exists($this->file)) {
          $filesystem->touch($this->file);
        }

    }

  public function saveSubscriber($name, $email, $date, $categories, $subscription): ?ConstraintViolationListInterface {
    $newSubscriber = new Subscriber();

    $newSubscriber
      ->setId($email)
      ->setName($name)
      ->setEmail($email)
      ->setDate(\DateTime::createFromFormat('d/m/Y', $date))
      ->setCategories($categories)
      ->setSubscription($subscription);

    $validator = Validation::createValidator();
    $errors = $validator->validate($newSubscriber);
    if (count($errors) > 0) {
      return $errors;
    }

    $value = Yaml::parseFile($this->file);
    $value[] = $newSubscriber->toArray();
    $yaml = Yaml::dump($value);
    file_put_contents($this->file, $yaml);
    return NULL;
  }

  public function updateSubscriber(Subscriber $subscriber): Subscriber {
    $value = Yaml::parseFile($this->file);
    foreach ($value as $key => $item) {
      if ($item['id'] == $subscriber->getId()) {
        $value[$key] = $subscriber->toArray();
        break;
      }
    }
    $yaml = Yaml::dump($value);
    file_put_contents($this->file, $yaml);

    return $subscriber;
  }

  public function removeSubscriber(Subscriber $subscriber)
  {
    $value = Yaml::parseFile($this->file);
    foreach ($value as $item) {
      if ($item['id'] == $subscriber->getId()) {
        unset($item['id']);
        break;
      }
    }
    $yaml = Yaml::dump($value);
    file_put_contents($this->file, $yaml);
  }

  /**
   * Finds entities by a set of criteria.
   *
   * @param int|null $limit
   * @param int|null $offset
   * @psalm-param array<string, mixed> $criteria
   * @psalm-param array<string, string>|null $orderBy
   *
   * @return object[] The objects.
   * @psalm-return list<T>
   */
  public function findBy(array $criteria, ?array $orderBy = null, $limit = null, $offset = null)
  {
    $value = Yaml::parseFile($this->file);
    if (!empty($criteria)) {
      foreach ($criteria as $name => $val) {
        foreach ($value as $key => $item) {
          if ($item[$name] != $val) {
            unset($value[$key]);
          }
        }
      }
    }
    if ($orderBy) {
      $column = array_column($value, $orderBy[0]);
      array_multisort($column, $orderBy[1], $value);
    }

    $result = [];
    foreach ($value as $key => $item) {
      $entity = new Subscriber();
      $entity->setId($item['id']);
      $entity->setEmail($item['email']);
      $entity->setSubscription($item['subscription']);
      $dt = new \DateTime();
      $dt->setTimestamp($item['date']);
      $entity->setDate($dt);
      $entity->setName($item['name']);
      $entity->setCategories($item['categories']);
      $result[] = $entity;
    }

    return $result;
  }

}
