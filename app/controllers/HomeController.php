<?php
// FILE: /app/controllers/HomeController.php

/**
 * Home Controller
 * Handles public homepage
 */
class HomeController extends Controller
{
    /**
     * Homepage
     */
    public function index()
    {
        // If already authenticated, redirect to dashboard
        if ($this->isAuthenticated()) {
            $this->redirect('/dashboard');
            return;
        }

        $this->view('home/index');
    }
}
