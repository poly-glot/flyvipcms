<?php

declare(strict_types=1);

if (!function_exists('ui_icon')) {
    function ui_icon(string $name, string $class = 'icon'): string
    {
        return sprintf('<svg class="%s" aria-hidden="true" focusable="false"><use href="#i-%s"/></svg>', esc($class, 'attr'), esc($name, 'attr'));
    }
}

if (!function_exists('ui_initials')) {
    function ui_initials(string ...$names): string
    {
        $present = array_values(array_filter(array_map(trim(...), $names), static fn (string $name): bool => $name !== ''));
        $letters = array_map(static fn (string $name): string => mb_strtoupper(mb_substr($name, 0, 1)), array_slice($present, 0, 2));

        return implode('', $letters) ?: '?';
    }
}

if (!function_exists('ui_avatar')) {
    function ui_avatar(string $first, string $last = '', string $size = ''): string
    {
        return sprintf('<span class="avatar %s" aria-hidden="true">%s</span>', esc($size, 'attr'), esc(ui_initials($first, $last)));
    }
}

if (!function_exists('ui_date')) {
    function ui_date(?string $value, string $format = 'j M Y'): string
    {
        $timestamp = $value === null || $value === '' ? false : strtotime($value);

        return $timestamp === false ? '—' : date($format, $timestamp);
    }
}

if (!function_exists('ui_points')) {
    function ui_points(string|float|int|null $value): string
    {
        return number_format((float) $value, 2);
    }
}

if (!function_exists('ui_signed_points')) {
    function ui_signed_points(string|float|int|null $value): string
    {
        $amount = (float) $value;

        return ($amount < 0 ? '−' : '+') . number_format(abs($amount), 2);
    }
}

if (!function_exists('ui_error')) {
    function ui_error(string $name): string
    {
        $errors = session()->getFlashdata('errors');

        return is_array($errors) ? (string) ($errors[$name] ?? '') : '';
    }
}

if (!function_exists('ui_old')) {
    function ui_old(string $name, ?string $fallback): string
    {
        $value = old($name, $fallback ?? '');

        return is_string($value) ? $value : '';
    }
}

if (!function_exists('ui_attributes')) {
    function ui_attributes(array $attributes): string
    {
        $html = '';

        foreach ($attributes as $name => $value) {
            if ($value === false || $value === null) {
                continue;
            }

            $html .= $value === true ? ' ' . $name : sprintf(' %s="%s"', $name, esc((string) $value, 'attr'));
        }

        return $html;
    }
}

if (!function_exists('ui_field_frame')) {
    function ui_field_frame(string $name, string $label, string $control, string $hint, string $extraClass = ''): string
    {
        $error = ui_error($name);
        $classes = trim('field ' . $extraClass . ($error !== '' ? ' field--invalid' : ''));

        $html = sprintf('<div class="%s"><label for="f_%s">%s</label>%s', esc($classes, 'attr'), esc($name, 'attr'), esc($label), $control);

        if ($hint !== '') {
            $html .= sprintf('<p class="field__hint" id="f_%s_hint">%s</p>', esc($name, 'attr'), esc($hint));
        }

        if ($error !== '') {
            $html .= sprintf('<p class="field__error" id="f_%s_error">%s%s</p>', esc($name, 'attr'), ui_icon('alert', 'icon icon--sm'), esc($error));
        }

        return $html . '</div>';
    }
}

if (!function_exists('ui_described_by')) {
    function ui_described_by(string $name, string $hint): array
    {
        $ids = array_filter([
            $hint !== '' ? "f_{$name}_hint" : '',
            ui_error($name) !== '' ? "f_{$name}_error" : '',
        ]);

        return [
            'aria-describedby' => $ids === [] ? null : implode(' ', $ids),
            'aria-invalid' => ui_error($name) !== '' ? 'true' : null,
        ];
    }
}

if (!function_exists('ui_field')) {
    function ui_field(string $name, string $label, string $type = 'text', ?string $fallback = null, array $attributes = []): string
    {
        $hint = (string) ($attributes['hint'] ?? '');
        $extraClass = (string) ($attributes['field_class'] ?? '');
        unset($attributes['hint'], $attributes['field_class']);

        $value = $type === 'password' ? null : ui_old($name, $fallback);

        $control = $type === 'textarea'
            ? sprintf('<textarea id="f_%s" name="%s" rows="3"%s>%s</textarea>', esc($name, 'attr'), esc($name, 'attr'), ui_attributes($attributes + ui_described_by($name, $hint)), esc((string) $value))
            : sprintf('<input id="f_%s" type="%s" name="%s"%s%s>', esc($name, 'attr'), esc($type, 'attr'), esc($name, 'attr'), $value === null ? '' : sprintf(' value="%s"', esc($value, 'attr')), ui_attributes($attributes + ui_described_by($name, $hint)));

        return ui_field_frame($name, $label, $control, $hint, $extraClass);
    }
}

if (!function_exists('ui_select')) {
    function ui_select(string $name, string $label, array $options, ?string $fallback = null, array $attributes = []): string
    {
        $hint = (string) ($attributes['hint'] ?? '');
        $blank = $attributes['blank'] ?? null;
        $extraClass = (string) ($attributes['field_class'] ?? '');
        unset($attributes['hint'], $attributes['blank'], $attributes['field_class']);

        $selected = ui_old($name, $fallback);
        $choices = $blank === null ? '' : sprintf('<option value="">%s</option>', esc((string) $blank));

        foreach ($options as $value => $text) {
            $choices .= sprintf('<option value="%s"%s>%s</option>', esc((string) $value, 'attr'), $selected === (string) $value ? ' selected' : '', esc((string) $text));
        }

        $control = sprintf('<select id="f_%s" name="%s"%s>%s</select>', esc($name, 'attr'), esc($name, 'attr'), ui_attributes($attributes + ui_described_by($name, $hint)), $choices);

        return ui_field_frame($name, $label, $control, $hint, $extraClass);
    }
}

if (!function_exists('ui_switch')) {
    function ui_switch(string $name, string $label, bool $checked, string $hint = ''): string
    {
        $html = sprintf(
            '<label class="switch"><input type="checkbox" role="switch" name="%s" value="1"%s><span class="switch__track" aria-hidden="true"></span><span class="switch__text">%s',
            esc($name, 'attr'),
            $checked ? ' checked' : '',
            esc($label),
        );

        if ($hint !== '') {
            $html .= sprintf('<small>%s</small>', esc($hint));
        }

        return $html . '</span></label>';
    }
}

if (!function_exists('ui_segmented')) {
    function ui_segmented(string $name, string $legend, array $options, string $selected): string
    {
        $html = sprintf('<fieldset class="segmented"><legend>%s</legend><div class="segmented__track">', esc($legend));

        foreach ($options as $value => $label) {
            $html .= sprintf(
                '<label class="segmented__option"><input type="radio" name="%s" value="%s"%s><span>%s</span></label>',
                esc($name, 'attr'),
                esc((string) $value, 'attr'),
                $selected === (string) $value ? ' checked' : '',
                esc((string) $label),
            );
        }

        return $html . '</div></fieldset>';
    }
}

if (!function_exists('ui_sparkline')) {
    function ui_sparkline(array $values, int $width = 160, int $height = 40): string
    {
        $series = array_map(floatval(...), array_values($values));

        if (count($series) < 2) {
            return '';
        }

        $low = min($series);
        $span = max(max($series) - $low, 0.01);
        $step = $width / (count($series) - 1);
        $inset = 3;

        $points = [];

        foreach ($series as $index => $value) {
            $points[] = sprintf('%.1f,%.1f', $index * $step, $inset + ($height - 2 * $inset) * (1 - ($value - $low) / $span));
        }

        $line = implode(' ', $points);

        return sprintf(
            '<svg class="sparkline" viewBox="0 0 %1$d %2$d" width="%1$d" height="%2$d" role="img" aria-label="Balance trend"><polygon class="sparkline__area" points="0,%2$d %3$s %1$d,%2$d"/><polyline class="sparkline__line" points="%3$s"/></svg>',
            $width,
            $height,
            $line,
        );
    }
}

if (!function_exists('ui_pips')) {
    function ui_pips(int $filled, int $capacity): string
    {
        $total = max(1, min($capacity, 40));
        $html = sprintf('<span class="pips" role="img" aria-label="%d of %d seats">', $filled, $total);

        for ($seat = 1; $seat <= $total; ++$seat) {
            $html .= '<i class="pip' . ($seat <= $filled ? ' pip--filled' : '') . '"></i>';
        }

        return $html . '</span>';
    }
}

if (!function_exists('ui_nav')) {
    function ui_nav(bool $admin): array
    {
        if (!$admin) {
            return [
                ['label' => 'Overview', 'items' => [['portal', 'Overview', 'home']]],
                ['label' => 'Flying', 'items' => [['portal/reservations', 'Reservations', 'ticket'], ['portal/points', 'Points', 'star']]],
                ['label' => 'Account', 'items' => [['portal/profile', 'Profile', 'user'], ['account/password', 'Password', 'lock']]],
            ];
        }

        return [
            ['label' => 'Overview', 'items' => [['admin', 'Dashboard', 'home']]],
            ['label' => 'Club', 'items' => [['admin/members', 'Members', 'users'], ['admin/payments', 'Payments', 'card'], ['admin/points', 'Points', 'star'], ['admin/reservations', 'Reservations', 'ticket']]],
            ['label' => 'Fleet', 'items' => [['admin/aircrafts', 'Aircraft', 'plane'], ['admin/aircraft-types', 'Types', 'layers'], ['admin/maintenance', 'Maintenance', 'wrench'], ['admin/airports', 'Airports', 'pin'], ['admin/routes', 'Routes', 'route']]],
            ['label' => 'Crew', 'items' => [['admin/pilots', 'Pilots', 'user'], ['admin/pilot-documents', 'Documents', 'file'], ['admin/pilot-certifications', 'Certifications', 'award'], ['admin/flight-schedule', 'Schedule', 'clock']]],
        ];
    }
}

if (!function_exists('ui_is_current')) {
    function ui_is_current(string $path): bool
    {
        $current = uri_string();

        return $current === $path || str_starts_with($current, $path . '/');
    }
}

if (!function_exists('ui_subnav')) {
    function ui_subnav(string $uri): array
    {
        foreach (ui_nav(true) as $group) {
            if (!in_array($group['label'], ['Fleet', 'Crew'], true)) {
                continue;
            }

            foreach ($group['items'] as $item) {
                if ($uri === $item[0] || str_starts_with($uri, $item[0] . '/')) {
                    return $group;
                }
            }
        }

        return [];
    }
}

if (!function_exists('ui_nav_label')) {
    function ui_nav_label(string $path, string $fallback): string
    {
        foreach (ui_nav(true) as $group) {
            foreach ($group['items'] as $item) {
                if ($item[0] === $path) {
                    return $item[1];
                }
            }
        }

        return $fallback;
    }
}
