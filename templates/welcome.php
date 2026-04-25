<?php $this->layout('layout', ['title' => 'Welcome to Strux']) ?>

<div class="container">
    <section class="welcome-box">
        <h1>Welcome to Strux</h1>
        <p>The page you are looking at is being generated dynamically.</p>

        <div class="info-section">
            <div class="info-label">Controller for this page</div>
            <code><?= $this->e($controller_path) ?></code>
        </div>

        <div class="info-section">
            <div class="info-label">View for this page</div>
            <code><?= $this->e($view_path) ?></code>
        </div>

        <div class="links">
            <a href="/docs">Documentation</a>
            <a href="/guide">User Guide</a>
            <a href="https://github.com/jbstrap/strux-framework" target="_blank">GitHub</a>
        </div>
    </section>