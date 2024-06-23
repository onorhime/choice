<?php
// src/Security/AuthenticationEntryPoint.php

namespace App\Security;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class AuthenticationEntryPoint implements AuthenticationEntryPointInterface
{
    public function start(Request $request, AuthenticationException $authException = null): Response
    {
        // Redirect to the desired route for unauthenticated users
        return new RedirectResponse('/'); // Replace '/' with the desired route
    }
}
