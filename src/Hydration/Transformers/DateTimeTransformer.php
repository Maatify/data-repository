<?php

declare(strict_types=1);

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     Maatify.dev Data Repository
 * @Project     maatify/data-repository
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-11-27 14:15:00
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/data-repository view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

namespace Maatify\DataRepository\Hydration\Transformers;

use DateTimeImmutable;
use Exception;
use Maatify\DataRepository\Hydration\TransformerInterface;

class DateTimeTransformer implements TransformerInterface
{
    private ?string $format;

    public function __construct(?string $format = null)
    {
        $this->format = $format;
    }

    /**
     * @param mixed $value
     * @return DateTimeImmutable|null
     */
    public function transform(mixed $value): ?DateTimeImmutable
    {
        if ($value instanceof DateTimeImmutable) {
            return $value;
        }

        if (is_string($value)) {
            try {
                if ($this->format) {
                    $date = DateTimeImmutable::createFromFormat($this->format, $value);
                    return $date !== false ? $date : null;
                }
                return new DateTimeImmutable($value);
            } catch (Exception) {
                return null;
            }
        }

        if (is_int($value)) {
            return (new DateTimeImmutable())->setTimestamp($value);
        }

        return null;
    }
}
