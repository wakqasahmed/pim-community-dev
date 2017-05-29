<?php

namespace Pim\Bundle\ApiBundle\Controller;

use Akeneo\Component\StorageUtils\Remover\RemoverInterface;
use Pim\Bundle\NotificationBundle\Entity\Notification;
use Pim\Bundle\NotificationBundle\Entity\Repository\UserNotificationRepositoryInterface;
use Pim\Bundle\NotificationBundle\NotifierInterface;
use Pim\Bundle\UserBundle\Context\UserContext;
use Pim\Bundle\UserBundle\Repository\UserRepositoryInterface;
use Pim\Component\Catalog\Repository\ProductRepositoryInterface;
use PimEnterprise\Bundle\WorkflowBundle\Doctrine\ORM\Repository\ProductDraftRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class NotificationController
{
    /** @var UserContext */
    protected $userContext;

    /** @var UserNotificationRepositoryInterface */
    protected $userNotifRepository;

    /** @var RemoverInterface */
    protected $userNotifRemover;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;
    /**
     * @var ProductDraftRepository
     */
    private $productDraftRepository;
    /**
     * @var UserRepositoryInterface
     */
    private $userRepository;
    /**
     * @var NotifierInterface
     */
    private $notifier;

    /**
     * @param UserContext                         $userContext
     * @param UserNotificationRepositoryInterface $userNotifRepository
     * @param RemoverInterface                    $userNotifRemover
     * @param ProductRepositoryInterface          $productRepository
     * @param ProductDraftRepository              $productDraftRepository
     * @param UserRepositoryInterface             $userRepository
     * @param NotifierInterface                   $notifier
     */
    public function __construct(
        UserContext $userContext,
        UserNotificationRepositoryInterface $userNotifRepository,
        RemoverInterface $userNotifRemover,
        ProductRepositoryInterface $productRepository,
        ProductDraftRepository $productDraftRepository,
        UserRepositoryInterface $userRepository,
        NotifierInterface $notifier
    ) {
        $this->userContext = $userContext;
        $this->userNotifRepository = $userNotifRepository;
        $this->userNotifRemover = $userNotifRemover;
        $this->productRepository = $productRepository;
        $this->productDraftRepository = $productDraftRepository;
        $this->userRepository = $userRepository;
        $this->notifier = $notifier;
    }

    /**
     * List user notifications for the current user
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function listAction(Request $request)
    {
        $user = $this->userContext->getUser();

        $notifications = $this->userNotifRepository
            ->findBy(['user' => $user], ['id' => 'DESC'], 10, $request->get('skip', 0));

        $result = [];
        foreach ($notifications as $notificationEntity) {
            $notification = $notificationEntity->getNotification();

            $notifType = 'pimee_workflow.product_draft.notification';
            if (substr($notification->getMessage(), 0, strlen($notifType)) === $notifType) {
                $productId = (int) $notification->getRouteParams()['id'];
                $product = $this->productRepository->findOneBy(['id' => $productId]);
                $productIdentifier = (string) $product->getIdentifier();
                $routeParams = $notification->getRouteParams();
                $routeParams['identifier'] = $productIdentifier;

                $result[] = [
                    'message'       => $notification->getMessage(),
                    'type'          => $notification->getType(),
                    'route'         => $notification->getRoute(),
                    'routeParams'   => $routeParams,
                    'messageParams' => $notification->getMessageParams(),
                    'context'       => $notification->getContext(),
                    'comment'       => $notification->getComment(),
                ];

                $this->userNotifRemover->remove($notificationEntity);
            }
        }

        return new JsonResponse($result);
    }

    public function draftsAction(Request $request)
    {
        $drafts = $this->productDraftRepository->findAll();

        $result = [];
        foreach ($drafts as $draftEntity) {
                $result[] = [
                    'product_code' => $draftEntity->getProduct()->getIdentifier()->getData(),
                    'changes' => $draftEntity->getChanges(),
                    'author' => $draftEntity->getAuthor(),
                    'status' => $draftEntity->getStatus(),
                ];
        }

        return new JsonResponse($result);
    }

    public function draftAction(Request $request, $code, $author)
    {
        $product = $this->productRepository->findOneByIdentifier($code);
        if (null === $product) {
            return new JsonResponse(null);
        }
        $draft = $this->productDraftRepository->findOneBy(['product' => $product, 'author' => $author]);
        if (null === $draft) {
            return new JsonResponse(null);
        }

        $result = [
            'product_code' => $draft->getProduct()->getIdentifier()->getData(),
            'changes' => $draft->getChanges(),
            'author' => $draft->getAuthor(),
            'status' => $draft->getStatus(),
        ];

        return new JsonResponse($result);
    }

    public function newProductNotificationAction()
    {
        $user = $this->userRepository->findOneBy(['username' => 'admin']);
        $userNotifications = $this->userNotifRepository->findBy(['user' => $user]);

        $alreadyNotified = false;
        if (null !== $userNotifications) {
            foreach ($userNotifications as $userNotifications) {
                if ($userNotifications->getNotification()->getMessage() === 'New products are ready to synchronize') {
                    $alreadyNotified = true;
                }
            }
        }

        if (!$alreadyNotified) {
            $notification = new Notification();

            $notification
                ->setType('success')
                ->setMessage('New products are ready to synchronize')
                ->setRoute('pim_enrich_product_index')
                ->setContext(['actionType' => 'Products ready to synchronize']);

            $this->notifier->notify($notification, [$user]);
        }

        return new JsonResponse(null);
    }

    /**
     * Return the number of unread notifications for the current user
     *
     * @return JsonResponse
     */
    public function countUnreadAction()
    {
        $user = $this->userContext->getUser();

        return new JsonResponse($this->userNotifRepository->countUnreadForUser($user));
    }

    /**
     * Mark user notifications as viewed
     *
     * @param int|null $id If null, all notifications will be marked as viewed
     *
     * @return JsonResponse
     */
    public function markAsViewedAction($id)
    {
        $user = $this->userContext->getUser();

        if (null !== $user) {
            $this->userNotifRepository->markAsViewed($user, $id);
        }

        return new JsonResponse();
    }

    /**
     * Remove a notification
     *
     * @param int $id
     *
     * @return JsonResponse
     */
    public function removeAction($id)
    {
        $user = $this->userContext->getUser();

        if (null !== $user) {
            $notification = $this->userNotifRepository->findOneBy(
                [
                    'id'   => $id,
                    'user' => $user
                ]
            );

            if ($notification) {
                $this->userNotifRemover->remove($notification);
            }
        }

        return new JsonResponse();
    }
}
