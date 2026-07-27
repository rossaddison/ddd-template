<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\User;

use App\Infrastructure\Persistence\Trait\RequireId;
use App\User\UserRepository;
use App\Infrastructure\Persistence\Identity\Identity;
use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Entity;
use Cycle\Annotated\Annotation\Relation\HasOne;
use Cycle\Annotated\Annotation\Table\Index;
use Cycle\ORM\Entity\Behavior;
use DateTimeImmutable;
use Yiisoft\Security\PasswordHasher;

/**
 * Merges what the invoice app split across User + UserInv (a per-user
 * profile-extension entity) into one entity — only the generic profile
 * fields came along (active, language, name, telegram_chat_id); UserInv's
 * invoice-business fields (company/address/VAT/tax/IBAN/etc.) had no place
 * in a generic template and were dropped, along with the now-redundant
 * UserRbacLink pairing table (RBAC's own assignment storage already tracks
 * user↔role by user id).
 */
#[Entity(repository: UserRepository::class)]
#[Index(columns: ['login'], unique: true)]
#[Behavior\CreatedAt(field: 'created_at', column: 'created_at')]
#[Behavior\UpdatedAt(field: 'updated_at', column: 'updated_at')]
class User
{
    use RequireId;

    #[Column(type: 'primary')]
    private ?int $id = null;

    #[Column(type: 'string')]
    private string $passwordHash = '';

    #[Column(type: 'datetime')]
    private readonly DateTimeImmutable $created_at;

    #[Column(type: 'datetime')]
    private readonly DateTimeImmutable $updated_at;

    #[HasOne(target: Identity::class)]
    private readonly Identity $identity;

    #[Column(type: 'bool', default: false)]
    private bool $tfa_enabled = false;

    #[Column(type: 'string', nullable: true)]
    private ?string $totpSecret = '';

    #[Column(type: 'bool', typecast: 'bool', default: true)]
    private ?bool $active = true;

    #[Column(type: 'string(35)', nullable: true, default: 'en')]
    private ?string $language = 'en';

    #[Column(type: 'string(151)', nullable: true)]
    private ?string $name = '';

    /**
     * Per-user Telegram chat id — a different concept from
     * Domain\Telegram\BotConnection's single global chat id (the app's one
     * bot). Not consumed by anything yet this pass; a natural extension
     * point for sending a specific user their own message later.
     */
    #[Column(type: 'string(100)', nullable: true)]
    private ?string $telegram_chat_id = null;

    public function __construct(
        #[Column(type: 'string(48)')]
        private string $login,
        #[Column(type: 'string(254)')]
        private readonly string $email,
        string $password,
    ) {
        $this->created_at = new DateTimeImmutable();
        $this->updated_at = new DateTimeImmutable();
        $this->setPassword($password);
        // Generate a new auth key on signup
        $this->identity = new Identity();
    }

    public function reqId(): int
    {
        return $this->requireId($this->id, 'User');
    }

    public function getLogin(): string
    {
        return $this->login;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setLogin(string $login): void
    {
        $this->login = $login;
    }

    public function validatePassword(string $password): bool
    {
        return new PasswordHasher()->validate($password, $this->passwordHash);
    }

    public function setPassword(string $password): void
    {
        $this->passwordHash =  new PasswordHasher()->hash($password);
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->created_at;
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updated_at;
    }

    public function getIdentity(): Identity
    {
        return $this->identity;
    }

    public function getTotpSecret(): ?string
    {
        return $this->totpSecret;
    }

    public function setTotpSecret(?string $secret): void
    {
        $this->totpSecret = $secret;
    }

    public function is2FAEnabled(): bool
    {
        return $this->tfa_enabled ;
    }

    public function set2FAEnabled(bool $enabled): void
    {
        $this->tfa_enabled = $enabled;
    }

    public function isActive(): bool
    {
        return $this->active ?? true;
    }

    public function setActive(bool $active): void
    {
        $this->active = $active;
    }

    public function getLanguage(): ?string
    {
        return $this->language;
    }

    public function setLanguage(?string $language): void
    {
        $this->language = $language;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getTelegramChatId(): ?string
    {
        return $this->telegram_chat_id;
    }

    public function setTelegramChatId(?string $telegram_chat_id): void
    {
        $this->telegram_chat_id = $telegram_chat_id;
    }
}
