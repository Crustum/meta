<?php
declare(strict_types=1);

it('renders pwa head tags', function (): void {
    $head = $this->head();

    $head->pwa(
        name: 'Acme',
        manifest: '/site.webmanifest',
        themeColor: '#0f172a',
        appleTouchIcon: '/apple-touch-icon.png',
        appleWebAppStatusBarStyle: 'black',
    );

    expect($head->toHtml())
        ->toContain('<meta name="application-name" content="Acme">')
        ->toContain('<meta name="apple-mobile-web-app-title" content="Acme">')
        ->toContain('<meta name="mobile-web-app-capable" content="yes">')
        ->toContain('<meta name="apple-mobile-web-app-status-bar-style" content="black">')
        ->toContain('<meta name="theme-color" content="#0f172a">')
        ->toContain('rel="manifest" href="/site.webmanifest">')
        ->toContain('rel="apple-touch-icon" href="/apple-touch-icon.png" sizes="180x180">');
});
