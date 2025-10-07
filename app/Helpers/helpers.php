<?php

use Illuminate\Support\Facades\Route;

if (!function_exists('generate_breadcrumbs')) {
    /**
     * @param array $customLabels An associative array mapping route segments to custom labels.
     * @return array
     */
    function generate_breadcrumbs(array $customLabels = []): array
    {
        $breadcrumbs = []; // Start with an empty array
        $routeName = Route::currentRouteName();

        if (!$routeName) {
            return []; // Return empty if there's no current route
        }

        $segments = explode('.', $routeName);
        $path = '';

        foreach ($segments as $i => $segment) {
            // Build the route path segment by segment
            $path .= ($i > 0 ? '.' : '') . $segment;
            $isLast = $i === count($segments) - 1;

            // Use the custom label if it exists, otherwise generate one automatically.
            $label = $customLabels[$segment] ?? ucwords(str_replace(['-', '_'], ' ', $segment));

            // Skip adding 'index' as the final breadcrumb link.
            if (strtolower($segment) === 'index' && $isLast) {
                continue;
            }

            if ($isLast) {
                // The last segment is the current page and shouldn't be a link.
                $url = '#';
            } else {
                // Parent segments should link to their own 'index' page if one exists.
                $parentRoute = $path . '.index';
                $url = Route::has($parentRoute) ? route($parentRoute) : '#';
            }

            $breadcrumbs[] = [
                'label' => $label,
                'url' => $url,
            ];
        }

        return $breadcrumbs;
    }
}
