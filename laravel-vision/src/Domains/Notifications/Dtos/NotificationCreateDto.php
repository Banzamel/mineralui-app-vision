<?php

namespace Notifications\Dtos;

use Notifications\Enums\NotificationSeverityEnum;

/**
 * DTO carrying the full payload required to persist a new notification.
 * Used by the internal creation API — the caller must supply the company id explicitly,
 * no domain-wide user lookups happen inside the service.
 */
readonly class NotificationCreateDto
{
    /**
     * Builds the DTO with the full notification payload.
     *
     * @param int $companyId tenant the notification belongs to
     * @param int $userId recipient user id
     * @param string $type free-form type identifier used by the frontend renderer
     * @param NotificationSeverityEnum $severity severity level driving the UI colour/icon
     * @param string $title short notification title (English fallback — Web Push displays it as-is)
     * @param string $message notification body (English fallback — Web Push displays it as-is)
     * @param string|null $link optional link to open when the notification is clicked
     * @param array<string, mixed>|null $data structured payload for frontend i18n rendering
     *                                         (e.g. `{actor_name: 'Anna'}` for user_login)
     */
    public function __construct(
        private int $companyId,
        private int $userId,
        private string $type,
        private NotificationSeverityEnum $severity,
        private string $title,
        private string $message,
        private ?string $link = null,
        private ?array $data = null,
    ) {}

    /**
     * Returns the tenant id.
     *
     * @return int company id
     */
    public function getCompanyId(): int
    {
        return $this->companyId;
    }

    /**
     * Returns the recipient user id.
     *
     * @return int user id
     */
    public function getUserId(): int
    {
        return $this->userId;
    }

    /**
     * Returns the notification type identifier.
     *
     * @return string type string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Returns the notification severity.
     *
     * @return NotificationSeverityEnum severity enum
     */
    public function getSeverity(): NotificationSeverityEnum
    {
        return $this->severity;
    }

    /**
     * Returns the notification title.
     *
     * @return string title
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * Returns the notification message body.
     *
     * @return string message body
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * Returns the optional notification link.
     *
     * @return string|null link url or null
     */
    public function getLink(): ?string
    {
        return $this->link;
    }

    /**
     * Returns the structured payload for frontend i18n rendering (or null).
     *
     * @return array<string, mixed>|null structured data
     */
    public function getData(): ?array
    {
        return $this->data;
    }

    /**
     * Converts the DTO to the persistence array expected by the repository.
     *
     * @return array<string, mixed> row data keyed by DB column names
     */
    public function toArray(): array
    {
        return [
            'company_id' => $this->companyId,
            'user_id' => $this->userId,
            'type' => $this->type,
            'severity' => $this->severity->value,
            'title' => $this->title,
            'message' => $this->message,
            'data' => $this->data,
            'link' => $this->link,
        ];
    }
}
