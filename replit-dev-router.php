<?php
declare(strict_types=1);

$root = __DIR__;
$originalUri = (string) ($_SERVER['REQUEST_URI'] ?? '/');
$uri = parse_url($originalUri, PHP_URL_PATH);
$path = rawurldecode(is_string($uri) ? $uri : '/');
$path = str_replace('\\', '/', $path);

$incomingHost = trim(explode(',', (string) ($_SERVER['HTTP_HOST'] ?? ''))[0]);
$requestHost = $incomingHost;
$proxyHost = trim(explode(',', (string) ($_SERVER['HTTP_X_FORWARDED_HOST'] ?? ''))[0]);
$devDomain = trim((string) ($_SERVER['REPLIT_DEV_DOMAIN'] ?? getenv('REPLIT_DEV_DOMAIN') ?: ''));
$knownDomains = array_filter(array_map('trim', explode(',', (string) ($_SERVER['REPLIT_DOMAINS'] ?? getenv('REPLIT_DOMAINS') ?: ''))));
if ($devDomain !== '') {
    $knownDomains[] = $devDomain;
}

$isValidHost = static fn (string $host): bool =>
    preg_match('/\A[A-Za-z0-9.-]+(?::[0-9]{1,5})?\z/', $host) === 1;
$isReplitHost = static function (string $host) use ($knownDomains): bool {
    $hostname = strtolower((string) preg_replace('/:\d+\z/', '', $host));
    $knownHostnames = array_map(
        static fn (string $domain): string => strtolower((string) preg_replace('/:\d+\z/', '', $domain)),
        $knownDomains
    );
    return in_array($hostname, $knownHostnames, true)
        || str_ends_with($hostname, '.replit.dev')
        || str_ends_with($hostname, '.replit.app');
};

$trustedProxyHost = $proxyHost !== ''
    && $isValidHost($proxyHost)
    && $isReplitHost($proxyHost);
if ($trustedProxyHost) {
    $requestHost = $proxyHost;
} elseif (!$isValidHost($requestHost) && $devDomain !== '' && $isValidHost($devDomain)) {
    $requestHost = $devDomain;
}

$prefixValue = $_SERVER['HTTP_X_FORWARDED_PREFIX'] ?? $_SERVER['BASE_PATH'] ?? '';
if ($prefixValue === '') {
    $environmentPrefix = getenv('BASE_PATH');
    $prefixValue = is_string($environmentPrefix) ? $environmentPrefix : '';
}
$prefixParts = array_values(array_filter(explode('/', trim((string) $prefixValue, '/')), static fn (string $part): bool => $part !== ''));
$proxyPrefix = '';
if (
    $prefixParts !== []
    && !in_array('.', $prefixParts, true)
    && !in_array('..', $prefixParts, true)
    && preg_match('/\A[A-Za-z0-9._~-]+(?:\/[A-Za-z0-9._~-]+)*\z/', implode('/', $prefixParts)) === 1
) {
    $proxyPrefix = '/' . implode('/', $prefixParts);
}
if ($proxyPrefix === '') {
    foreach (['/ecomm-storefront', '/ecomm'] as $knownPrefix) {
        if ($path === $knownPrefix || str_starts_with($path, $knownPrefix . '/')) {
            $proxyPrefix = $knownPrefix;
            break;
        }
    }
}

if ($proxyPrefix !== '') {
    if ($path === $proxyPrefix || str_starts_with($path, $proxyPrefix . '/')) {
        $path = substr($path, strlen($proxyPrefix));
        $path = $path === '' ? '/' : $path;
        $query = parse_url($originalUri, PHP_URL_QUERY);
        $_SERVER['REQUEST_URI'] = $path . (is_string($query) && $query !== '' ? '?' . $query : '');
    }
    $_SERVER['SCRIPT_NAME'] = $proxyPrefix . '/index.php';
    $_SERVER['PHP_SELF'] = $proxyPrefix . '/index.php' . ($path === '/' ? '' : $path);
}

$forwardedProto = strtolower(trim(explode(',', (string) ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? ''))[0]));
if (!in_array($forwardedProto, ['http', 'https'], true)) {
    $forwardedProto = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
}
if ($isReplitHost($requestHost)) {
    $forwardedProto = 'https';
}

if ($isValidHost($requestHost)) {
    $hostname = (string) preg_replace('/:\d+\z/', '', $requestHost);
    $_SERVER['HTTP_HOST'] = $requestHost;
    $_SERVER['SERVER_NAME'] = $hostname;
    $_SERVER['SERVER_PORT'] = $forwardedProto === 'https' ? '443' : '80';
    $_SERVER['REQUEST_SCHEME'] = $forwardedProto;
    if ($forwardedProto === 'https') {
        $_SERVER['HTTPS'] = 'on';
    } else {
        unset($_SERVER['HTTPS']);
    }

    $requestBaseUrl = $forwardedProto . '://' . $requestHost . $proxyPrefix;
    putenv('APP_URL=' . $requestBaseUrl);
    $_ENV['APP_URL'] = $requestBaseUrl;
    $_SERVER['APP_URL'] = $requestBaseUrl;
}

$readyFile = $root . '/.mysql-data/ready';
$errorFile = $root . '/.mysql-data/import-error';
if (!is_file($readyFile)) {
    header('Content-Type: text/html; charset=utf-8');
    header('Cache-Control: no-store, max-age=0');
    http_response_code(200);

    if (is_file($errorFile)) {
        echo '<!doctype html><html lang="en"><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">';
        echo '<title>Database setup needs attention</title><body style="font:16px system-ui;max-width:42rem;margin:12vh auto;padding:0 1.25rem;color:#172033">';
        echo '<h1>Database setup needs attention</h1><p>The app files and local MySQL data were preserved.</p>';
        echo '<p>Review <code>.mysql-data/import.log</code> in the Project files for the setup error.</p></body></html>';
    } else {
        header('Refresh: 4');
        echo '<!doctype html><html lang="en"><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">';
        echo '<meta http-equiv="refresh" content="4"><title>Starting Ecomm</title>';
        echo '<body style="font:16px system-ui;max-width:42rem;margin:12vh auto;padding:0 1.25rem;color:#172033">';
        echo '<h1>Starting Ecomm</h1><p>MySQL is setting up the local store database. This page will refresh automatically.</p>';
        echo '</body></html>';
    }
    exit;
}

$installerWindowFile = $root . '/.mysql-data/install-enabled-until';
$installerWindowValue = is_file($installerWindowFile)
    ? trim((string) file_get_contents($installerWindowFile))
    : '';
$installerWindowExpires = ctype_digit($installerWindowValue)
    ? (int) $installerWindowValue
    : 0;
$installerPath = in_array($path, ['/install', '/install/'], true);
$installerMethod = strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));
if (
    $installerPath
    && $installerWindowExpires > time()
    && in_array($installerMethod, ['GET', 'POST'], true)
    && is_file($root . '/install/index.php')
) {
    chdir($root . '/install');
    require $root . '/install/index.php';
    exit;
}

$segments = array_values(array_filter(explode('/', $path), static fn (string $part): bool => $part !== ''));

foreach ($segments as $segment) {
    if ($segment === '.' || $segment === '..' || str_starts_with($segment, '.')) {
        http_response_code(404);
        exit;
    }
}

$firstSegment = strtolower($segments[0] ?? '');
$blockedDirectories = [
    'bootstrap',
    'config',
    'core',
    'database',
    'install',
    'resources',
    'routes',
    'storage',
    'tests',
    'vendor',
];

if (in_array($firstSegment, $blockedDirectories, true)) {
    http_response_code(404);
    exit;
}

$extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
if ($path === '/assets/templates/basic/css/color.php') {
    $color = (string) ($_GET['color'] ?? '');
    if (!preg_match('/^[a-f0-9]{6}$/i', $color)) {
        $color = '336699';
    }
    $_GET['color'] = $color;
    header('Content-Type: text/css; charset=utf-8');
    require $root . '/assets/templates/basic/css/color.php';
    exit;
}

if (in_array($extension, ['bak', 'env', 'ini', 'log', 'php', 'sh', 'sql'], true)) {
    http_response_code(404);
    exit;
}

if (str_starts_with($path, '/assets/')) {
    $assetRoot = realpath($root . '/assets');
    $assetPath = realpath($root . $path);
    if (
        $assetRoot !== false
        && $assetPath !== false
        && is_file($assetPath)
        && str_starts_with($assetPath, $assetRoot . DIRECTORY_SEPARATOR)
    ) {
        if ($proxyPrefix !== '') {
            $contentType = function_exists('mime_content_type')
                ? mime_content_type($assetPath)
                : false;
            header('Content-Type: ' . ($contentType ?: 'application/octet-stream'));
            header('Cache-Control: public, max-age=3600');
            readfile($assetPath);
            exit;
        }
        return false;
    }
    http_response_code(404);
    exit;
}

header_register_callback(static function () use ($incomingHost, $isReplitHost, $proxyPrefix): void {
    $incomingHostname = strtolower((string) preg_replace('/:\d+\z/', '', $incomingHost));
    foreach (headers_list() as $header) {
        if (stripos($header, 'Location:') !== 0) {
            continue;
        }

        $location = trim(substr($header, strlen('Location:')));
        $parts = parse_url($location);
        if (!is_array($parts)) {
            continue;
        }

        $path = (string) ($parts['path'] ?? '/');
        $hasPrefix = $proxyPrefix !== ''
            && ($path === $proxyPrefix || str_starts_with($path, $proxyPrefix . '/'));
        if (isset($parts['host'])) {
            $locationHost = strtolower((string) $parts['host']);
            $isLoopback = in_array($locationHost, ['localhost', '127.0.0.1', '0.0.0.0'], true);
            $isInternalHost = $incomingHostname !== ''
                && $locationHost === $incomingHostname
                && !$isReplitHost($incomingHostname);
            if ($isLoopback || $isInternalHost) {
                if ($proxyPrefix !== '' && !$hasPrefix) {
                    $path = $proxyPrefix . $path;
                }
                $target = $path;
                if (isset($parts['query'])) {
                    $target .= '?' . $parts['query'];
                }
                if (isset($parts['fragment'])) {
                    $target .= '#' . $parts['fragment'];
                }
                header_remove('Location');
                header('Location: ' . $target, true);
                break;
            }

            if (!$isReplitHost($locationHost) || $proxyPrefix === '' || $hasPrefix) {
                continue;
            }

            $path = $proxyPrefix . $path;
            $scheme = 'https';
            $target = $scheme . '://' . $parts['host'];
            if (isset($parts['port'])) {
                $target .= ':' . $parts['port'];
            }
            $target .= $path;
            if (isset($parts['query'])) {
                $target .= '?' . $parts['query'];
            }
            if (isset($parts['fragment'])) {
                $target .= '#' . $parts['fragment'];
            }
            header_remove('Location');
            header('Location: ' . $target, true);
            break;
        }

        if ($proxyPrefix === '' || $path === '' || $hasPrefix) {
            continue;
        }
        $path = $proxyPrefix . $path;
        $target = $path;
        if (isset($parts['query'])) {
            $target .= '?' . $parts['query'];
        }
        if (isset($parts['fragment'])) {
            $target .= '#' . $parts['fragment'];
        }

        header_remove('Location');
        header('Location: ' . $target, true);
        break;
    }
});

ob_start(static function (string $html) use ($incomingHost, $requestHost, $isReplitHost, $proxyPrefix): string {
    if ($html === '' || stripos($html, '<html') === false) {
        return $html;
    }

    $incomingHostname = strtolower((string) preg_replace('/:\d+\z/', '', $incomingHost));
    $requestHostname = strtolower((string) preg_replace('/:\d+\z/', '', $requestHost));

    return (string) preg_replace_callback(
        '/\b(href|src|action|poster)\s*=\s*(["\'])([^"\']*)\2/i',
        static function (array $match) use ($incomingHostname, $requestHostname, $isReplitHost, $proxyPrefix): string {
            $url = $match[3];
            $parts = parse_url($url);
            if (!is_array($parts) || !isset($parts['path'])) {
                return $match[0];
            }

            $path = (string) $parts['path'];
            $hasPrefix = $proxyPrefix !== ''
                && ($path === $proxyPrefix || str_starts_with($path, $proxyPrefix . '/'));
            $host = strtolower((string) ($parts['host'] ?? ''));
            $isSameHost = $host !== ''
                && in_array($host, [$incomingHostname, $requestHostname], true);

            if ($host !== '' && !$isSameHost) {
                return $match[0];
            }

            if ($host === '' && !str_starts_with($path, '/')) {
                return $match[0];
            }

            if ($proxyPrefix !== '' && !$hasPrefix) {
                $path = $proxyPrefix . $path;
            }

            $internalOrigin = $host !== '' && !$isReplitHost($host);
            if ($internalOrigin) {
                $rewrittenUrl = $path;
            } elseif ($host !== '') {
                $scheme = (string) ($parts['scheme'] ?? 'https');
                $rewrittenUrl = $scheme . '://' . $parts['host'];
                if (isset($parts['port'])) {
                    $rewrittenUrl .= ':' . $parts['port'];
                }
                $rewrittenUrl .= $path;
            } else {
                $rewrittenUrl = $path;
            }

            if (isset($parts['query'])) {
                $rewrittenUrl .= '?' . $parts['query'];
            }
            if (isset($parts['fragment'])) {
                $rewrittenUrl .= '#' . $parts['fragment'];
            }

            return $match[1] . '=' . $match[2] . $rewrittenUrl . $match[2];
        },
        $html
    );
});

require $root . '/index.php';
