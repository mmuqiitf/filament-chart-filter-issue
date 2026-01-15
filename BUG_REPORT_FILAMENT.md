# Filament Bug Report: ReflectionException in Widget HasFiltersSchema

### Package Version

v4.x

### Laravel Version

v12.x

### Livewire Version

v3.x

### PHP Version

8.4

### Problem Description

When using the `HasFiltersSchema` trait in a Widget (e.g., `ChartWidget`) and defining filters via `filtersSchema(Schema $schema)`, the application crashes with a `ReflectionException` when interacting with any `searchable()` Select component within that schema.

The error specifically states that the `filters()` method does not exist, even though the trait is designed to fall back to `filtersSchema()` if `filters()` is not defined.

### Steps to Reproduce

1. Create a `ChartWidget` and use the `HasFiltersSchema` trait.
2. Define a `filtersSchema()` method (do **not** define a `filters()` method).
3. Inside `filtersSchema()`, add a `Select` component with `searchable()`.
4. Render the widget on a page.
5. Open the filter dropdown and type into the searchable select.
6. The AJAX request fails with: `ReflectionException: Method ...::filters() does not exist`.

### Expected Behavior

The `searchable()` Select component should trigger its search logic without requiring the presence of a `filters()` method if `filtersSchema()` is provided. The `InteractsWithSchemas` trait should use the resolved method name for reflection.

### Actual Behavior

The application crashes because `InteractsWithSchemas::cacheSchema` attempts to perform reflection on the original method name (`filters`) instead of the resolved fallback name (`filtersSchema`).

### Reproduction Repository

[https://github.com/<your-username>/filament-chart-filter-issue](https://github.com/<your-username>/filament-chart-filter-issue)
_(Note: Please replace the link above with your actual repository URL after pushing)_

### Technical Analysis & Suggested Fix

The bug is located in `vendor/filament/schemas/src/Concerns/InteractsWithSchemas.php`.

In the `cacheSchema` method, the code correctly identifies `filtersSchema` as the method name to use, but it still uses the variable `$name` (which holds "filters") when creating the `ReflectionMethod`.

**Current Code:**

```php
if (method_exists($this, $name)) {
    $methodName = $name;
} elseif (method_exists($this, "{$name}Schema")) {
    $methodName = "{$name}Schema";
}

$methodReflection = new ReflectionMethod($this, $name); // <--- Problem here
```

**Suggested Fix:**

```php
$methodReflection = new ReflectionMethod($this, $methodName); // <--- Should use $methodName
```

### Relevant Log Output

```text
ReflectionException: Method App\Filament\Widgets\BugReproductionChart::filters() does not exist in vendor/filament/schemas/src/Concerns/InteractsWithSchemas.php:21
Stack trace:
#0 vendor/filament/schemas/src/Concerns/InteractsWithSchemas.php(21): ReflectionMethod->__construct('App\\Filament\\Wi...', 'filters')
#1 vendor/filament/schemas/src/Concerns/InteractsWithSchemas.php(43): App\Filament\Widgets\BugReproductionChart->cacheSchema('filters', Array, NULL)
#2 vendor/filament/schemas/src/Schema.php(108): App\Filament\Widgets\BugReproductionChart->getSchema('filters')
...
```
