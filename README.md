# Filament v4 Widget Reflection Bug Reproduction

This repository is a minimal reproduction of a `ReflectionException` bug in Filament v4 when using the `HasFiltersSchema` trait in Widgets with `searchable()` Select components.

## The Bug

When a `ChartWidget` uses the `HasFiltersSchema` trait and includes `searchable()` select components, interacting with the search input triggers an error:
`ReflectionException: Method ...::filters() does not exist`.

## Reproduction Steps

1. Clone this repository.
2. Install dependencies: `composer install`.
3. Set up the database: `php artisan migrate`.
4. Create an admin user: `php artisan make:filament-user`.
5. Start the server: `php artisan serve`.
6. Access the dashboard at `/admin`.
7. Locate the **"Bug Reproduction Chart"**.
8. Click the filter icon and type anything into the **Category**, **Subcategory**, or **Item** searchable selects.
9. Verify the crash in the browser/logs.

## Technical Analysis

The issue lies in `Filament\Schemas\Concerns\InteractsWithSchemas::cacheSchema()`.

The trait correctly identifies that `filtersSchema()` should be used as a fallback when `filters()` is missing. However, it then attempts to instantiate a `ReflectionMethod` using the original name (`filters`) instead of the resolved method name (`filtersSchema`).

**File:** `vendor/filament/schemas/src/Concerns/InteractsWithSchemas.php`

```php
// ...
if (method_exists($this, $name)) { // $name is "filters"
    $methodName = $name;
} elseif (method_exists($this, "{$name}Schema")) {
    $methodName = "{$name}Schema"; // This correctly sets $methodName to "filtersSchema"
} else {
    // ...
}

// BUG: It uses $name ("filters") instead of $methodName ("filtersSchema")
$methodReflection = new ReflectionMethod($this, $name);
// ...
```

## Suggested Fix

Update the `ReflectionMethod` instantiation to use the resolved `$methodName`:

```diff
- $methodReflection = new ReflectionMethod($this, $name);
+ $methodReflection = new ReflectionMethod($this, $methodName);
```

## Temporary Workaround

Add a proxy method to your Widget:

```php
public function filters(Schema $schema): Schema
{
    return $this->filtersSchema($schema);
}
```
