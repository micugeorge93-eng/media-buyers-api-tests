<?php

declare(strict_types=1);

namespace Tests\Support\Builders;

/**
 * Builds POST /api/mediabuyers request payloads from the contract defaults.
 * Tests express intent via fluent methods instead of inline JSON.
 */
final class CreateMediaBuyerRequestBuilder
{
    private ?string $mbId = '9001';
    private ?string $initials = 'TM';
    private ?string $name = 'Test Media Buyer';
    private ?string $email = 'test.media.buyer@example.com';
    private ?string $slackUserId = 'U05AZ3DQBBKK';
    private mixed $active = true;

    /** @var list<string> */
    private array $omitFields = [];

    public static function valid(): self
    {
        return new self();
    }

    public function withMbId(string $mbId): self
    {
        $clone = clone $this;
        $clone->mbId = $mbId;

        return $clone;
    }

    public function withInitials(?string $initials): self
    {
        $clone = clone $this;
        $clone->initials = $initials;

        return $clone;
    }

    public function withName(string $name): self
    {
        $clone = clone $this;
        $clone->name = $name;

        return $clone;
    }

    public function withEmail(string $email): self
    {
        $clone = clone $this;
        $clone->email = $email;

        return $clone;
    }

    public function withSlackUserId(?string $slackUserId): self
    {
        $clone = clone $this;
        $clone->slackUserId = $slackUserId;

        return $clone;
    }

    public function withActive(mixed $active): self
    {
        $clone = clone $this;
        $clone->active = $active;

        return $clone;
    }

    public function withoutMbId(): self
    {
        return $this->omit('mbId');
    }

    public function withoutName(): self
    {
        return $this->omit('name');
    }

    public function withoutEmail(): self
    {
        return $this->omit('email');
    }

    public function withoutActive(): self
    {
        return $this->omit('active');
    }

    public function withInvalidEmail(): self
    {
        return $this->withEmail('not-an-email');
    }

    public function withInitialsTooLong(): self
    {
        return $this->withInitials('TOO LONG');
    }

    public function withNameTooShort(): self
    {
        return $this->withName('A');
    }

    public function withNameTooLong(): self
    {
        return $this->withName(str_repeat('X', 31));
    }

    public function withInvalidMbId(): self
    {
        return $this->withMbId('abc');
    }

    public function withNonBooleanActive(): self
    {
        return $this->withActive('yes');
    }

    /**
     * @return array<string, mixed>
     */
    public function build(): array
    {
        $payload = [];

        if (!$this->isOmitted('mbId') && $this->mbId !== null) {
            $payload['mbId'] = $this->mbId;
        }

        if (!$this->isOmitted('initials') && $this->initials !== null) {
            $payload['initials'] = $this->initials;
        }

        if (!$this->isOmitted('name') && $this->name !== null) {
            $payload['name'] = $this->name;
        }

        if (!$this->isOmitted('email') && $this->email !== null) {
            $payload['email'] = $this->email;
        }

        if (!$this->isOmitted('slackUserId') && $this->slackUserId !== null) {
            $payload['slackUserId'] = $this->slackUserId;
        }

        if (!$this->isOmitted('active') && $this->active !== null) {
            $payload['active'] = $this->active;
        }

        return $payload;
    }

    private function omit(string $field): self
    {
        $clone = clone $this;
        $clone->omitFields[] = $field;

        return $clone;
    }

    private function isOmitted(string $field): bool
    {
        return in_array($field, $this->omitFields, true);
    }
}
