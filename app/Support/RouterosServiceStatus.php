<?php

namespace App\Support;

class RouterosServiceStatus
{
    public static function isEnabled($value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_int($value)) {
            return $value === 1;
        }

        if (is_string($value)) {
            $normalized = strtolower(trim($value));
            return in_array($normalized, ['1', 'true', 'on', 'yes', 'enabled'], true);
        }

        return false;
    }

    public static function getServiceStatus(array $services, string $serviceName): string
    {
        foreach ($services as $service) {
            if (($service['name'] ?? null) !== $serviceName) {
                continue;
            }

            $disabled = strtolower((string) ($service['disabled'] ?? ''));

            if ($disabled === 'false' || $disabled === 'no' || $disabled === '0') {
                return 'enabled';
            }

            if ($disabled === 'true' || $disabled === 'yes' || $disabled === '1') {
                return 'disabled';
            }
        }

        return 'disabled';
    }
}
