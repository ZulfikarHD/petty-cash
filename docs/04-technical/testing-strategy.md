# Testing Strategy

## Overview

Our testing strategy ensures high-quality, reliable code through comprehensive test coverage across multiple testing levels.

## Testing Philosophy

- **Test-Driven Development (TDD)**: Write tests before or alongside code
- **Continuous Testing**: Run tests frequently during development
- **Comprehensive Coverage**: Aim for 80%+ code coverage
- **Real-World Scenarios**: Test both happy paths and edge cases

## Test Levels

### 1. Unit Tests

**Purpose**: Test individual methods and functions in isolation

**Location**: `tests/Unit/`

**Coverage**:
- Model methods
- Helper functions
- Service class methods
- Utility functions

**Example**:
```php
public function test_user_has_role(): void
{
    $user = User::factory()->create();
    $user->assignRole('Admin');
    
    $this->assertTrue($user->hasRole('Admin'));
}
```

### 2. Feature Tests

**Purpose**: Test complete features and user flows

**Location**: `tests/Feature/`

**Coverage**:
- HTTP endpoints
- Database interactions
- Form submissions
- Authentication flows
- Authorization checks

**Example**:
```php
public function test_admin_can_create_user(): void
{
    $admin = User::factory()->create();
    $admin->assignRole('Admin');

    $response = $this->actingAs($admin)->post('/users', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertRedirect('/users');
    $this->assertDatabaseHas('users', ['email' => 'test@example.com']);
}
```

### 3. Integration Tests

**Purpose**: Test interaction between multiple components

**Included in**: Feature tests

**Coverage**:
- API integrations
- Third-party services
- Database transactions
- Queue jobs

## Test Categories

### Happy Path Testing
Tests for expected, successful user flows:
- User logs in with correct credentials
- Admin creates a valid user
- User updates profile with valid data

### Edge Case Testing ✅
Tests for boundary conditions and unusual scenarios:
- **Invalid Input**: Wrong email format, weak passwords
- **Boundary Values**: Maximum length strings, zero amounts
- **Missing Data**: Required fields left empty
- **Duplicate Data**: Email already exists
- **Unauthorized Access**: Non-admin tries to access admin features
- **Self-Destructive Actions**: User tries to delete themselves
- **Permission Boundaries**: Users with insufficient permissions
- **Concurrent Operations**: Race conditions, simultaneous updates

**Examples from Sprint 1:**
```php
// Edge Case: Invalid email format
public function test_user_creation_requires_valid_email(): void
{
    $admin = User::factory()->create();
    $admin->assignRole('Admin');

    $response = $this->actingAs($admin)->post('/users', [
        'name' => 'Test User',
        'email' => 'invalid-email',  // Invalid format
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertSessionHasErrors(['email']);
}

// Edge Case: Self-deletion prevention
public function test_user_cannot_delete_themselves(): void
{
    $admin = User::factory()->create();
    $admin->assignRole('Admin');

    $response = $this->actingAs($admin)->delete("/users/{$admin->id}");

    $response->assertSessionHas('error');
    $this->assertDatabaseHas('users', ['id' => $admin->id]);
}

// Edge Case: Email uniqueness
public function test_profile_email_must_be_unique(): void
{
    $existingUser = User::factory()->create(['email' => 'existing@example.com']);
    $user = User::factory()->create();

    $response = $this->actingAs($user)->put('/profile', [
        'name' => $user->name,
        'email' => 'existing@example.com',  // Already taken
    ]);

    $response->assertSessionHasErrors(['email']);
}

// Edge Case: Incorrect current password
public function test_password_update_requires_current_password(): void
{
    $user = User::factory()->create([
        'password' => bcrypt('current-password'),
    ]);

    $response = $this->actingAs($user)->put('/profile/password', [
        'current_password' => 'wrong-password',  // Incorrect
        'password' => 'new-password',
        'password_confirmation' => 'new-password',
    ]);

    $response->assertSessionHasErrors(['current_password']);
}
```

### Error Handling Testing
Tests for error scenarios:
- Server errors
- Database failures
- Validation errors
- Authentication failures

### Security Testing
Tests for security vulnerabilities:
- SQL injection attempts
- XSS attacks
- CSRF protection
- Authorization bypasses
- Mass assignment vulnerabilities

## Testing Standards

### Test Naming Convention

```php
// Format: test_<what>_<condition>_<expected_result>
public function test_admin_can_delete_user(): void
public function test_guest_cannot_access_dashboard(): void
public function test_validation_fails_with_invalid_email(): void
```

### Test Structure (Arrange-Act-Assert)

```php
public function test_example(): void
{
    // Arrange: Set up test data
    $user = User::factory()->create();
    $user->assignRole('Admin');
    
    // Act: Perform the action
    $response = $this->actingAs($user)->get('/users');
    
    // Assert: Verify the outcome
    $response->assertStatus(200);
}
```

### Test Data

**Factories**: Use factories for model creation
```php
$user = User::factory()->create(['name' => 'Specific Name']);
$users = User::factory()->count(10)->create();
```

**Database Transactions**: Tests run in transactions (rolled back automatically)
```php
use RefreshDatabase;  // Trait handles this
```

## Running Tests

### All Tests
```bash
php artisan test
```

### Specific Test Suite
```bash
php artisan test --testsuite=Feature
php artisan test --testsuite=Unit
```

### Specific Test File
```bash
php artisan test tests/Feature/Users/UserManagementTest.php
```

### Specific Test Method
```bash
php artisan test --filter=test_admin_can_create_user
```

### With Coverage
```bash
php artisan test --coverage
php artisan test --coverage-html coverage
```

### Stop on First Failure
```bash
php artisan test --stop-on-failure
```

## Code Coverage Goals

| Component | Target Coverage |
|-----------|----------------|
| Controllers | 90% |
| Models | 85% |
| Services | 90% |
| Middleware | 100% |
| Helpers | 95% |
| **Overall** | **80%+** |

## Current Test Status

### Sprint 1 Coverage
- **Total Tests**: 27
- **Passing**: 27 ✅
- **Total Assertions**: 66
- **Edge Cases Covered**: Yes ✅

### Test Breakdown
- UserManagementTest: 9 tests
  - Happy paths: 5
  - Edge cases: 4 (invalid email, self-deletion, unauthorized access, guest access)
- RoleAssignmentTest: 8 tests
  - Happy paths: 4
  - Permission verification: 4
- ProfileManagementTest: 10 tests
  - Happy paths: 2
  - Edge cases: 5 (invalid email, duplicate email, wrong password, validation)
  - Guest protection: 3

## CI/CD Integration

Tests run automatically on:
- Every commit (pre-commit hook)
- Pull requests
- Before deployment
- Scheduled nightly builds

## Best Practices

### DO ✅
- Write tests for all new features
- Test both success and failure scenarios
- Use factories for test data
- Keep tests independent (no test depends on another)
- Use descriptive test names
- Test edge cases and boundary conditions
- Mock external services
- Clean up test data (automatic with RefreshDatabase)

### DON'T ❌
- Skip writing tests
- Test only happy paths
- Share state between tests
- Use production data in tests
- Hardcode test data
- Leave commented-out tests
- Ignore failing tests

## Continuous Improvement

### Sprint Reviews
- Review test coverage after each sprint
- Identify gaps in testing
- Add tests for discovered bugs
- Update testing strategy as needed

### Quality Metrics
- Track test count over time
- Monitor coverage trends
- Measure test execution time
- Review flaky tests

## Tools & Libraries

- **PHPUnit**: Testing framework
- **Laravel Testing**: Laravel's testing helpers
- **Factory**: Model factories for test data
- **Faker**: Generate fake data
- **Mockery**: Mocking objects (when needed)
- **Xdebug**: Code coverage (development)

## Future Enhancements

- [ ] Add browser testing (Laravel Dusk)
- [ ] Implement mutation testing
- [ ] Add performance benchmarking tests
- [ ] Create load testing scenarios
- [ ] Implement visual regression testing
- [ ] Add API contract testing

---

**Document Version**: 1.0  
**Last Updated**: November 24, 2024  
**Next Review**: After Sprint 3

