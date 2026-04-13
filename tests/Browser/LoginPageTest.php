<?php

test('login page renders main auth copy', function () {
    visit(route('login'))
        ->assertTitle('Log in - Workshop Manager')
        ->assertSee('Email address')
        ->assertNoJavaScriptErrors();
});
