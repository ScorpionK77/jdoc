<?php

use App\DataFixtures\UserPendingFixture;
use App\Entity\User;
use App\Enum\UserState;
use App\Tests\AbstractControllerTest;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class RegistrControllerTest extends AbstractControllerTest
{
    protected EntityManagerInterface $entityManager;

    protected CacheInterface $cache;

    protected function setUp(): void
    {
        parent::setUp();

        $this->entityManager = $this->client->getContainer()->get(EntityManagerInterface::class);
        $this->cache = $this->client->getContainer()->get(CacheInterface::class);
    }

    private function loadFixtures(): ReferenceRepository
    {
        $hasher = self::getContainer()->get(UserPasswordHasherInterface::class);

        $loader = new Loader();
        $loader->addFixture(new UserPendingFixture($hasher));
        $executor = new ORMExecutor($this->entityManager, null);
        $executor->execute($loader->getFixtures(), true);

        $this->entityManager->clear();

        return $executor->getReferenceRepository();
    }

    public function testFullRegistration(): void
    {
        // 1. Имитируем POST-запрос от фронтенда с JSON-payload
        $this->client->request(
            'POST',
            '/api/v1/registr',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            (string) json_encode([
                'vclogin' => 'test_user',
                'vcemail' => 'test@example.com',
                'vcpassword' => 'SecurePassword123',
                'vcpassword_confirm' => 'SecurePassword123',
            ])
        );

        // Проверяем, что контроллер успешно отработал и вернул HTTP 200
        $this->assertResponseIsSuccessful();
        $this->assertJsonStringEqualsJsonString(
            (string) json_encode(['success' => true]),
            $this->client->getResponse()->getContent()
        );

        // 2. Проверяем, что пользователь физически создался в базе данных
        /** @var User|null $user */
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['vclogin' => 'test_user']);

        $this->assertNotNull($user, 'Пользователь test_user не был найден в базе данных.');
        $this->assertEquals('test@example.com', $user->getVcemail());
        $this->assertEquals(UserState::PENDING_APPROVAL, $user->getIstateid());

        // 3. Проверяем отправку почты (Symfony Mailer)
        $this->assertEmailCount(1);

        // Извлекаем отправленное письмо для детального анализа содержимого
        $email = $this->getMailerMessage();
        $this->assertNotNull($email, 'Письмо не было сгенерировано.');

        // Проверяем заголовки и контент внутри Twig-шаблона
        $this->assertEmailHeaderSame($email, 'To', 'test@example.com');
        $this->assertEmailTextBodyContains($email, 'Добро пожаловать в JDoc!');
    }

    public function testConfirmActionSuccess(): void
    {
        $this->loadFixtures();

        // Ищем юзера, которого создала фикстура
        $user = $this->entityManager->getRepository(User::class)
            ->findOneBy(['vclogin' => UserPendingFixture::USER_REFERENCE]);

        // Дальше логика кэша и отправки запроса...
        $testToken = 'valid_test_token_123';
        $cacheKey = 'confirm_reg_' . $testToken;

        // Добавляем ItemInterface в аргументы замыкания
        $this->cache->get($cacheKey, function (ItemInterface $item) use ($user) {
            $item->expiresAfter(3600);
            return $user->getIuserid();
        });
        $this->client->request('GET', '/api/v1/confirm/' . $testToken);
        $this->assertResponseIsSuccessful();
        // D:\OSPanel\home\jdoc>docker compose exec php composer test tests/Controller/RegistrControllerTest.php
    }

    public function testConfirmActionWithExpiredCacheReturns404(): void
    {
        // Пытаемся перейти по вымышленному или просроченному токену
        $this->client->request('GET', '/api/v1/confirm/invalid_token_999');

        // Контроллер должен выбросить NotFoundHttpException, что вернет HTTP статус 404
        $this->assertResponseStatusCodeSame(404);
    }
}