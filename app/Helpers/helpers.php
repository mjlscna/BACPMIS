<?php

use Illuminate\Support\Facades\Route;

if (!function_exists('generate_breadcrumbs')) {
    function generate_breadcrumbs(): array
    {
        $routeName = Route::currentRouteName(); // e.g., procurements.create
        $segments = explode('.', $routeName);

        $breadcrumbs = [];
        $path = '';

        foreach ($segments as $i => $segment) {
            $path .= ($i ? '.' : '') . $segment;

            if ($i < count($segments) - 1) {
                // Parent segments point to index route
                $parentRoute = $path . '.index';
                $url = Route::has($parentRoute) ? route($parentRoute) : '#';
                $label = ucfirst(str_replace(['-', '_'], ' ', $segment));
            } else {
                // Current page
                $url = '#';
                $label = ucfirst(str_replace(['-', '_'], ' ', $segment));
                
                // If current segment is "index", hide label
                if (strtolower($label) === 'index' && $i > 0) {
                    continue;
                }
            }

            $breadcrumbs[] = [
                'label' => $label,
                'url' => $url,
            ];
        }

        return $breadcrumbs;
    }
}

