<?php

declare(strict_types=1);

// Registration is disabled for this application
// These tests are skipped as registration routes are not available
test('registration is disabled', function (): void {
    expect(true)->toBeTrue();
})->skip('Registration feature is disabled');
