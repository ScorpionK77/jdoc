<?php

namespace App\DTO;

use App\Entity\User;
use OpenApi\Attributes as OA;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[OA\Schema(
    description: "Данные по пользователю",
)]
#[UniqueEntity(fields: ['vclogin'], entityClass: User::class, message: 'Указанный логин уже используется', errorPath: 'vclogin')]
#[UniqueEntity(fields: ['vcemail'], entityClass: User::class, message: 'Указанный e-mail уже используется', errorPath: 'vcemail')]
final class RegistrationDTO
{
    public function __construct(
        #[Assert\NotBlank(message: 'Логин не должен быть пустым.')]
        #[OA\Property(description: "Логин",type: "string")]
        public ?string $vclogin = null,
        #[Assert\NotBlank(message: 'Пароль не должен быть пустым.')]
        #[OA\Property(description: "Пароль",type: "string")]
        public ?string $vcpassword = null,
        #[OA\Property(description: "Повторить пароль",type: "string")]
        public ?string $vcpassword_confirm = null,
        #[Assert\NotBlank(message: 'E-mail не должен быть пустым.')]
        #[Assert\Email(message: 'Введен некорректный формат e-mail адреса.')]
        #[OA\Property(description: "E-mail пользователя", type: "string", format: "email", example: "user@mail.ru")]
        public ?string $vcemail = null,
    ) {
    }

    #[Assert\Callback()]
    public function validatePasswords(ExecutionContextInterface $context): void
    {
        // Проверяем только если основной пароль не пустой
        if (!empty($this->vcpassword) && $this->vcpassword !== $this->vcpassword_confirm)
        {
            $context->buildViolation('Пароль и повтор не совпадают')
                ->atPath('vcpassword_confirm')
                ->addViolation();
        }
    }
}
