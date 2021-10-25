<?php

namespace App\Controller;

use App\Entity\Subscriber;
use App\Repository\SubscriberRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class SubscriberController extends AbstractController
{

    private $subscriberRepository;

    public function __construct(SubscriberRepository $subscriberRepository) {
      $this->subscriberRepository = $subscriberRepository;
    }
    /**
     * @Route("/subscribers", name="subscriber", methods={"GET"})
     */
    public function index(Request $request): JsonResponse {
      $createria = [];
      $order_by = $request->query->get('orderBy', 'id');
      if (0 === mb_strpos($order_by, '-')) {
        $direction = SORT_DESC;
        $order_by = trim($order_by, '-');
      }
      else {
        $direction = SORT_ASC;
      }

      $subscribers = $this->subscriberRepository->findBy($createria, [$order_by, $direction]);
      $data = [];

      foreach ($subscribers as $subscriber) {
        $data[] = $subscriber->toArray();
      }

      return new JsonResponse($data, Response::HTTP_OK);
    }

    /**
     * @Route("/subscriber/add", name="add_subscriber", methods={"POST"})
     */
    public function add(Request $request): JsonResponse {
      $data = json_decode($request->getContent(), true);

      $name = $data['name'];
      $email = $data['email'];
      $date = $data['date'];
      $categories = $data['categories'];
      $subscription = $data['subscription'];

      if (empty($name) || empty($email) || empty($date) || empty($categories) || empty($subscription)) {
        throw new NotFoundHttpException('Expecting mandatory parameters!');
      }

      if ($errors = $this->subscriberRepository->saveSubscriber($name, $email, $date, $categories, $subscription)) {
        return new JsonResponse(['status' => (string)$errors], Response::HTTP_OK);
      }

      return new JsonResponse(['status' => 'Subscriber created!'], Response::HTTP_CREATED);
    }

    /**
     * @Route("/subscriber/{id}", name="get_one_subscriber", methods={"GET"})
     */
    public function get($id): JsonResponse {
      $subscribers = $this->subscriberRepository->findBy(['id' => $id]);
      if (empty($subscribers)) {
        throw new NotFoundHttpException('subscriber not found');
      }
      $subscriber = reset($subscribers);

      return new JsonResponse($subscriber->toArray(), Response::HTTP_OK);
    }

  /**
   * @Route("/subscriber/{id}", name="update_subscriber", methods={"PUT"})
   */
  public function update($id, Request $request): JsonResponse {
    $subscribers = $this->subscriberRepository->findBy(['id' => $id]);
    if (empty($subscribers)) {
      throw new NotFoundHttpException('Not existing subscriber!');
    }

    $subscriber = reset($subscribers);
    $data = json_decode($request->getContent(), true);

    empty($data['name']) ? true : $subscriber->setName($data['name']);
    empty($data['date']) ? true : $subscriber->setDate($data['date']);
    empty($data['email']) ? true : $subscriber->setEmail($data['email']);
    empty($data['categories']) ? true : $subscriber->setCategories($data['categories']);

    $updatedSubscriber = $this->subscriberRepository->updateSubscriber($subscriber);

    return new JsonResponse($updatedSubscriber->toArray(), Response::HTTP_OK);
  }

  /**
   * @Route("/subscriber/{id}", name="delete_subscriber", methods={"DELETE"})
   */
  public function delete($id): JsonResponse {
    $subscribers = $this->subscriberRepository->findBy(['id' => $id]);
    if (empty($subscribers)) {
      throw new NotFoundHttpException('Not existing subscriber!');
    }
    $subscriber = reset($subscribers);

    $this->subscriberRepository->removeSubscriber($subscriber);

    return new JsonResponse(['status' => 'Subscriber deleted'], Response::HTTP_NO_CONTENT);
  }

}
