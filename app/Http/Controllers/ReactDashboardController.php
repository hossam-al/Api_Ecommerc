<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ReactDashboardController extends Controller
{
    private function dashboardStaticUrl(string $path): string
    {
        $url = route('dashboard.static', ['path' => $path]);
        $file = $this->resolveDistFile($path);

        if ($file === null) {
            return $url;
        }

        return $url . '?v=' . filemtime($file);
    }

    private function contentTypeFor(string $path): string
    {
        return match (strtolower(pathinfo($path, PATHINFO_EXTENSION))) {
            'js' => 'application/javascript; charset=UTF-8',
            'css' => 'text/css; charset=UTF-8',
            'svg' => 'image/svg+xml',
            'json' => 'application/json; charset=UTF-8',
            'map' => 'application/json; charset=UTF-8',
            'html' => 'text/html; charset=UTF-8',
            default => mime_content_type($this->dashboardDistPath($path)) ?: 'application/octet-stream',
        };
    }

    private function dashboardDistPath(string $path = ''): string
    {
        $distPath = base_path('..' . DIRECTORY_SEPARATOR . 'dacsboard' . DIRECTORY_SEPARATOR . 'dist');

        if ($path === '') {
            return $distPath;
        }

        return $distPath . DIRECTORY_SEPARATOR . ltrim($path, '\\/');
    }

    private function resolveDistFile(string $path): ?string
    {
        $distRoot = realpath($this->dashboardDistPath());

        if ($distRoot === false) {
            return null;
        }

        $requestedFile = realpath($this->dashboardDistPath($path));

        if ($requestedFile === false || !is_file($requestedFile)) {
            return null;
        }

        if ($requestedFile !== $distRoot && !str_starts_with($requestedFile, $distRoot . DIRECTORY_SEPARATOR)) {
            return null;
        }

        return $requestedFile;
    }

    public function index(): Response
    {
        $indexFile = $this->resolveDistFile('index.html');

        abort_if($indexFile === null, 404, 'React dashboard build was not found.');

        $html = (string) file_get_contents($indexFile);
        $html = preg_replace_callback(
            '/(?<attribute>href|src)="\/(?<path>[^"]+)"/',
            function (array $matches): string {
                $attribute = $matches['attribute'];
                $path = ltrim($matches['path'], '/');

                return sprintf(
                    '%s="%s"',
                    $attribute,
                    $this->dashboardStaticUrl($path)
                );
            },
            $html
        ) ?? $html;

        return response($html, 200, [
            'Content-Type' => 'text/html; charset=UTF-8',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
        ]);
    }

    public function static(string $path): BinaryFileResponse
    {
        $file = $this->resolveDistFile($path);

        abort_if($file === null, 404);

        $cacheControl = str_starts_with(str_replace('\\', '/', $path), 'assets/')
            ? 'public, max-age=3600'
            : 'public, max-age=3600';

        return response()->file($file, [
            'Cache-Control' => $cacheControl,
            'Content-Type' => $this->contentTypeFor($path),
        ]);
    }
}
