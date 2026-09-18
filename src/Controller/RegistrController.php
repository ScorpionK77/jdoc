<?php

namespace App\Controller;

use App\DTO\RegistrationDTO;
use App\Entity\User;
use App\Enum\UserState;
use App\Message\DocumentPublishedMessage;
use App\Message\UserStateMessage;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Attribute\Model;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use OpenApi\Attributes as OA;

#[Route('/api/v1', name: 'api.')]
#[OA\Tag(name: "Registr", description: "Регистрация")]
class RegistrController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher
    ) {
    }

    #[Route(path: '/registr', name: 'registr',  methods: ['POST'])]
    #[OA\RequestBody(content: new OA\JsonContent(ref: new Model(type: RegistrationDTO::class)))]
    #[OA\Response(response: 200, description: 'Данные пользователя.', content: new OA\JsonContent(ref: '#/components/schemas/ResultSucess'))]
    #[OA\Response(
        response: 422,
        description: 'Ошибка проверки данных',
        content: new OA\JsonContent(ref: '#/components/schemas/ErrorValidationResult')
    )]
    public function registrAction(#[MapRequestPayload(acceptFormat: 'json')] RegistrationDTO $dto): JsonResponse
    {
        $content = [];
        $user = new User();
        $user->setVclogin($dto->vclogin)
            ->setVcemail($dto->vcemail)
            ->setIstateid(UserState::PENDING_APPROVAL);

        $user->setVcpassword($this->passwordHasher->hashPassword($user,$dto->vcpassword));

        $this->entityManager->beginTransaction();
        try {
            $this->entityManager->persist($user);
            $this->entityManager->flush();
            $this->entityManager->commit();
            $content['success'] = true;
        } catch (\Exception  $e) {
            $this->entityManager->rollback();
            throw new BadRequestHttpException('Не удалось зарегистрировать пользователя', $e);
        }

        return $this->json($content);
    }

    #[Route(path: '/confirm/{key}', name: 'confirm', requirements: ['key' => '[a-zA-Z0-9\-_]+'],  methods: ['GET'])]
    #[OA\Response(response: 200, description: 'Данные пользователя.', content: new OA\JsonContent(ref: '#/components/schemas/ResultSucess'))]
    #[OA\Response(
        response: 404,
        description: 'Ошибка проверки данных',
        content: new OA\JsonContent(ref: '#/components/schemas/ErrorResult')
    )]
    public function confirmAction(string $key, CacheInterface $cache): JsonResponse
    {
        $content = [
            'success' => true
        ];
        $cacheKey = 'confirm_reg_' . $key;

        /** @var int|null $userId */
        $userId = $cache->get($cacheKey, function (ItemInterface $item) {
            $item->expiresAfter(-1);
            return null;
        });

        if (null === $userId) {
            throw $this->createNotFoundException('Ссылка для подтверждения устарела или недействительна.');
        }

        $userRepository = $this->entityManager->getRepository(User::class);
        $user = $userId ? $userRepository->find($userId) : null;

        if (!$user || $user->getIstateid() !== UserState::PENDING_APPROVAL)
        {
            throw $this->createNotFoundException('Пользователь не найден.');
        }

        // ставим подтвердление
        $user->setIstateid(UserState::APPROVED);
        $this->entityManager->flush();
        $cache->delete($cacheKey);

        return $this->json($content);
    }

    /*#[Route(path: '/bus', name: 'bus',  methods: ['GET'])]
    #[OA\Response(response: 200, description: 'Данные пользователя.', content: new OA\JsonContent(ref: '#/components/schemas/ResultSucess'))]
    public function busAction(MessageBusInterface $bus): JsonResponse
    {

        $bus->dispatch(new DocumentPublishedMessage(20));
        $content = [
            'success' => true
        ];

        return $this->json($content);
    }*/
}