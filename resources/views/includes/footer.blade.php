{{-- Campus Buddy Professional Universal Footer --}}
<footer class="main-footer">
    <div class="footer-container">
        <div class="footer-top">
            <div class="footer-brand">
                <a href="{{ url('/') }}" class="footer-logo">
                    <img src="{{ asset('images/eventImage/logo.png') }}" alt="Campus Buddy Logo">
                    <div class="footer-logo-text">
                        <span>Campus</span><span>Buddy</span>
                    </div>
                </a>
                <p class="brand-description">
                    Your all-in-one campus companion. Stay organized, connected, and ahead in your academic journey.
                </p>
                <div class="social-links">
                    <a href="#" class="social-icon"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="social-icon"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="social-icon"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="social-icon"><i class="fab fa-linkedin-in"></i></a>
                </div>
            </div>

            <div class="footer-nav">
                <div class="footer-col">
                    <h4>Academics</h4>
                    <ul>
                        <li><a href="{{ route('routine') }}"><i class="far fa-calendar-alt"></i> Class Routine</a></li>
                        <li><a href="{{ route('notes') }}"><i class="far fa-file-alt"></i> Study Materials</a></li>
                        <li><a href="{{ route('question-bank') }}"><i class="fas fa-university"></i> Question Bank</a></li>
                    </ul>
                </div>

                <div class="footer-col">
                    <h4>Campus Life</h4>
                    <ul>
                        <li><a href="#"><i class="fas fa-users"></i> District Association</a></li>
                        <li><a href="{{ route('clubs') }}"><i class="fas fa-flag"></i> University Clubs</a></li>
                        <li><a href="{{ route('dashboard') }}#events"><i class="far fa-star"></i> Event Calendar</a></li>
                    </ul>
                </div>

                <div class="footer-col">
                    <h4>Resources</h4>
                    <ul>
                        <li><a href="{{ route('alumni') }}"><i class="fas fa-user-friends"></i> Alumni Network</a></li>
                        <li><a href="#"><i class="fas fa-info-circle"></i> About Us</a></li>
                        <li><a href="mailto:support@campusbuddy.com"><i class="fas fa-headset"></i> Contact Support</a></li>
                    </ul>
                </div>

                <div class="footer-col">
                    <h4>Admin</h4>
                    <ul>
                        <li>
                            @if(Auth::check() && (Auth::user()->role === 'cr' || Auth::user()->role === 'admin'))
                                <a href="{{ route('cr-dashboard') }}"><i class="fas fa-user-shield"></i> CR Portal</a>
                            @else
                                <a href="javascript:void(0)" class="restricted-link" data-message="Access Restricted: Only Class Representatives and Admins can access the CR Portal."><i class="fas fa-user-shield"></i> CR Portal</a>
                            @endif
                        </li>
                        <li>
                            @if(Auth::check() && Auth::user()->role === 'admin')
                                <a href="/admin"><i class="fas fa-lock"></i> Admin Login</a>
                            @else
                                <a href="javascript:void(0)" class="restricted-link" data-message="Access Denied: Only Workspace Administrators can access the Admin Panel."><i class="fas fa-lock"></i> Admin Login</a>
                            @endif
                        </li>
                        <li><a href="#"><i class="fas fa-user-secret"></i> Privacy Policy</a></li>
                    </ul>
                </div>
            </div>
        </div>

        {{-- Access Control Toast --}}
        <div id="footer-toast" class="footer-toast">
            <i class="fas fa-shield-alt"></i>
            <span id="footer-toast-message">Access Restricted</span>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const toast = document.getElementById('footer-toast');
                const toastMsg = document.getElementById('footer-toast-message');
                let toastTimeout;

                document.querySelectorAll('.restricted-link').forEach(link => {
                    link.addEventListener('click', function(e) {
                        e.preventDefault();
                        const message = this.getAttribute('data-message');
                        
                        toastMsg.textContent = message;
                        toast.classList.add('show');

                        if (toastTimeout) clearTimeout(toastTimeout);
                        toastTimeout = setTimeout(() => {
                            toast.classList.remove('show');
                        }, 4000);
                    });
                });
            });
        </script>

        <div class="footer-bottom">
            <div class="copyright">
                &copy; {{ date('Y') }} Campus Buddy. Built for Students with <i class="fas fa-heart" style="color: #ff4757;"></i>
            </div>
            <div class="footer-bottom-links">
                <a href="#">Terms of Use</a>
                <a href="#">Cookies Policy</a>
                <a href="#">Help Center</a>
            </div>
        </div>
    </div>
</footer>
