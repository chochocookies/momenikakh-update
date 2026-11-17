<?php

test('the application boots successfully', function () {
    expect(app()->isLocal() || app()->environment('testing'))->toBeTrue();
});
