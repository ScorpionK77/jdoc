<?php

namespace App\Services\Env;

use App\Entity\Config;
use App\Repository\ConfigRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\EnvVarProcessorInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class DbSettingProcessor implements EnvVarProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $em,
        private CacheInterface $cache
    ) {
    }

    public function getEnv(string $prefix, string $name, \Closure $getEnv): mixed
    {
        $parts = explode(':', $name, 2);

        if (count($parts) !== 2)
        {
            throw new \RuntimeException(sprintf('Неверный формат параметра "%s". Используйте "group:key".', $name));
        }

        [$groupName, $key] = $parts;

        // Создаем уникальный ключ кэша для всей групп
        $cacheKey = 'db_settings_group_' . $groupName;

        // 1. Получаем ВСЕ настройки этой группы из кэша
        $groupSettings = $this->cache->get($cacheKey, function (ItemInterface $item) use ($groupName) {
            $item->expiresAfter(null); // Вечный кэш до сброса

            /** @var ConfigRepository $repository */
            $repository = $this->em->getRepository(Config::class);
            // ищем группу
            /** @var Config|null $group */
            $group = $repository->findOneBy(['vcname' => $groupName, 'vctype' => 'group']);
            if (!$group)
            {
                throw new \RuntimeException(sprintf('Группа параметров "%s" не найдена.".', $groupName));
            }
            // Ищем в БД все записи, принадлежащие этой группе
            $groupConfig = [];
            /** @var Config $row */
            foreach ($repository->findBy(['parent' => $group->getIconfigid()]) as $row)
            {
                $groupConfig[$row->getVcname()] = $row->getVcvalue();
            }

            return $groupConfig;
        });

        // 2. Возвращаем конкретную переменную из массива группы
        return $groupSettings[$key] ?? null;
    }

    public static function getProvidedTypes(): array
    {
        return [
            'db_setting' => 'string',
        ];
    }
}
