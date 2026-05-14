<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Campus Buddy')</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/topbar.css') }}">
    <link rel="stylesheet" href="{{ asset('css/base-ui.css') }}">
    <link rel="stylesheet" href="{{ asset('css/menu.css') }}">
    <link rel="stylesheet" href="{{ asset('css/footer.css') }}">
    <link rel="stylesheet" href="{{ asset('css/hero-common.css') }}">
    @stack('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    @include('includes.menu')

    <div class="layout">
        <main class="main">
            @yield('content')
        </main>
    </div>

    @include('includes.footer')
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Function to handle highlighting based on URL hash
            const handleHashHighlight = () => {
                const hash = window.location.hash;
                if (hash) {
                    const element = document.querySelector(hash);
                    if (element) {
                        // Smooth scroll after a tiny delay for other animations
                        setTimeout(() => {
                            element.scrollIntoView({ behavior: 'smooth', block: 'center' });
                            
                            // Apply custom highlight styles
                            const originalTransition = element.style.transition;
                            const originalTransform = element.style.transform;
                            const originalBoxShadow = element.style.boxShadow;
                            const originalBorderColor = element.style.borderColor;

                            element.style.transition = 'all 0.6s cubic-bezier(0.175, 0.885, 0.32, 1.275)';
                            element.style.transform = 'scale(1.03)';
                            element.style.boxShadow = '0 0 30px rgba(0, 170, 255, 0.6)';
                            element.style.borderColor = '#00AAFF';
                            element.style.zIndex = '101'; // Ensure it pops

                            // If it's an announcement in the dashboard, trigger the modal click
                            if (hash.startsWith('#announcement-')) {
                                element.click();
                            }

                            // Revert after 3 seconds
                            setTimeout(() => {
                                element.style.transform = originalTransform;
                                element.style.boxShadow = originalBoxShadow;
                                element.style.borderColor = originalBorderColor;
                                setTimeout(() => { element.style.transition = originalTransition; }, 600);
                            }, 3000);
                        }, 100);
                    }
                }
            };

            handleHashHighlight();
            // Also watch for hash changes while on the same page
            window.addEventListener('hashchange', handleHashHighlight);
        });
    </script>
    
    @stack('scripts')
</body>
</html>
