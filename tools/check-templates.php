<?php

declare(strict_types=1);

const ROOT = __DIR__ . '/..';
const COMMENT_FREE_ROOTS = ['app', 'tests'];
const COMMENT_EXEMPT = ['app/Config/', 'app/Language/', 'app/Views/errors/', 'app/Common.php', 'tests/phpstan-bootstrap.php'];
const TEMPLATE_EXEMPT = ['app/Views/errors/'];
const BARE_VARIABLE_OUTPUT = '/^\$[A-Za-z_]\w*(\[[^\]]+\])*$/';
const INLINE_STYLE_ALLOWED = ['app/Views/partials/icons.php'];

function phpFiles(string $root): array
{
    $files = [];
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(ROOT . '/' . $root, FilesystemIterator::SKIP_DOTS));

    foreach ($iterator as $file) {
        if ($file->getExtension() === 'php') {
            $files[] = ltrim(str_replace(ROOT, '', $file->getPathname()), '/');
        }
    }

    sort($files);

    return $files;
}

function exempt(string $path, array $prefixes): bool
{
    foreach ($prefixes as $prefix) {
        if (str_starts_with($path, $prefix)) {
            return true;
        }
    }

    return false;
}

function commentViolations(string $path): array
{
    $violations = [];

    foreach (token_get_all((string) file_get_contents(ROOT . '/' . $path)) as $token) {
        if (!is_array($token)) {
            continue;
        }

        if (in_array($token[0], [T_COMMENT, T_DOC_COMMENT], true)) {
            $violations[] = [$token[2], 'PHP comment'];
        }

        if ($token[0] === T_INLINE_HTML && str_contains($token[1], '<!--')) {
            $violations[] = [$token[2], 'HTML comment'];
        }
    }

    return $violations;
}

function templateViolations(string $path): array
{
    $source = (string) file_get_contents(ROOT . '/' . $path);
    $violations = [];

    foreach (explode("\n", $source) as $index => $line) {
        $number = $index + 1;

        if (preg_match('/\$this->include\([^)]*,/', $line)) {
            $violations[] = [$number, 'include() ignores its data argument; use view()'];
        }

        if (preg_match('/\son[a-z]+\s*=\s*"/i', $line)) {
            $violations[] = [$number, 'inline event handler attribute'];
        }

        if (preg_match('/\sstyle\s*=\s*"/i', $line) && !in_array($path, INLINE_STYLE_ALLOWED, true)) {
            $violations[] = [$number, 'inline style attribute'];
        }

        if (preg_match('/#[0-9a-f]{3,8}\b/i', $line) && !str_contains($line, 'href="#') && !str_contains($line, "'#")) {
            $violations[] = [$number, 'hex colour in a template; use a token'];
        }

        if (preg_match_all('/<\?=\s*(.+?)\s*\?>/', $line, $matches)) {
            foreach ($matches[1] as $expression) {
                if (preg_match(BARE_VARIABLE_OUTPUT, $expression)) {
                    $violations[] = [$number, "bare variable echoed without esc(): <?= {$expression} ?>"];
                }
            }
        }
    }

    return $violations;
}

$failures = [];

foreach (COMMENT_FREE_ROOTS as $root) {
    foreach (phpFiles($root) as $path) {
        if (exempt($path, COMMENT_EXEMPT)) {
            continue;
        }

        foreach (commentViolations($path) as [$line, $message]) {
            $failures[] = "{$path}:{$line}: {$message}";
        }
    }
}

foreach (phpFiles('app/Views') as $path) {
    if (exempt($path, TEMPLATE_EXEMPT)) {
        continue;
    }

    foreach (templateViolations($path) as [$line, $message]) {
        $failures[] = "{$path}:{$line}: {$message}";
    }
}

foreach ($failures as $failure) {
    fwrite(STDERR, $failure . "\n");
}

printf("%d template/comment violation(s)\n", count($failures));
exit($failures === [] ? 0 : 1);
