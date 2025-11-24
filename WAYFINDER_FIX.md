# Wayfinder Route Conflict Fix

## Problem

When running `yarn dev`, got this error:

```
✘ [ERROR] Multiple exports with the same name "update"
  resources/js/routes/profile/index.ts:155:13
  resources/js/routes/profile/index.ts:89:13
```

## Root Cause

**Duplicate route names** causing Wayfinder to generate conflicting TypeScript exports:

```php
// Our new routes
Route::put('/profile', ...)->name('profile.update');

// Existing routes from settings
Route::patch('/settings/profile', ...)->name('profile.update');
```

Both routes named `profile.update` → Wayfinder generated duplicate `update` exports.

## Solution

Renamed our profile routes to `my-profile.*` to avoid conflicts:

### Routes Changed

**Before:**
```php
Route::get('/profile', ...)->name('profile.show');
Route::put('/profile', ...)->name('profile.update');
Route::put('/profile/password', ...)->name('profile.password.update');
```

**After:**
```php
Route::get('/my-profile', ...)->name('my-profile.show');
Route::put('/my-profile', ...)->name('my-profile.update');
Route::put('/my-profile/password', ...)->name('my-profile.password.update');
```

### Files Updated

1. ✅ **routes/web.php** - Route names and URIs changed
2. ✅ **resources/js/pages/Profile/Show.vue** - Updated form submission URLs
3. ✅ **resources/js/components/UserMenuContent.vue** - Updated profile link
4. ✅ **tests/Feature/Feature/Profile/ProfileManagementTest.php** - Updated test URLs

### Verification

```bash
# Regenerate Wayfinder routes
php artisan wayfinder:generate

# Run tests
php artisan test tests/Feature/Feature/Profile/ProfileManagementTest.php
# ✅ All 10 tests passing

# Check routes
php artisan route:list --name=profile
```

**Result - No conflicts:**
```
GET  my-profile .............. my-profile.show
PUT  my-profile .......... my-profile.update
PUT  my-profile/password ... my-profile.password.update
GET  settings/profile ...... profile.edit
PATCH settings/profile ..... profile.update
DELETE settings/profile .... profile.destroy
```

## Current Status

✅ **Fixed** - No more duplicate route names
✅ **Tests Passing** - All 10 profile tests pass
✅ **Wayfinder Generated** - Clean TypeScript exports

Now `yarn dev` should work without errors!

## How to Prevent This in Future

1. **Check for existing routes** before creating new ones:
   ```bash
   php artisan route:list --name=profile
   ```

2. **Use unique route name prefixes** for different features:
   - `my-profile.*` for user's own profile
   - `profile.*` for settings profile
   - `admin.users.*` for admin user management

3. **Regenerate Wayfinder** after adding routes:
   ```bash
   php artisan wayfinder:generate
   ```

4. **Check for TypeScript errors** before committing:
   ```bash
   yarn dev
   ```

---

**Fixed By**: Route renaming and consistency updates  
**Date**: November 24, 2024  
**Issue**: Wayfinder duplicate exports  
**Status**: ✅ Resolved

