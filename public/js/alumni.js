
document.addEventListener('DOMContentLoaded', function () {
    // ==================== REGISTRATION MODAL ====================
    const openModalBtn = document.getElementById('registerTodayBtn');
    const modal = document.getElementById('registrationModal');
    const closeModalSpan = document.getElementById('closeModal');
    const approvalToast = document.getElementById('approvalToast');

    // Auto-open modal if there are validation errors (data attribute set in Blade)
    if (document.body.dataset.hasErrors === 'true') {
        if (modal) modal.style.display = 'flex';
    }

    // Hide approval toast after 3 seconds
    if (approvalToast) {
        setTimeout(() => {
            approvalToast.style.transition = 'all 0.8s cubic-bezier(0.175, 0.885, 0.32, 1.275)';
            approvalToast.style.opacity = '0';
            approvalToast.style.transform = 'translateY(-20px)';
            setTimeout(() => approvalToast.style.display = 'none', 800);
        }, 3000);
    }

    // Handle registration button status
    const registrationStatus = document.body.dataset.registrationStatus;
    if (openModalBtn) {
        if (registrationStatus === 'pending') {
            openModalBtn.style.opacity = '0.7';
            openModalBtn.style.cursor = 'not-allowed';
            openModalBtn.innerText = 'Application Pending';
            openModalBtn.classList.remove('pulse-primary');
            openModalBtn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
            });
        } else if (registrationStatus === 'approved') {
            openModalBtn.innerText = 'Manage Alumni Card';
            openModalBtn.classList.add('manage-mode');
        }
    }

    // Global function for deletion confirmation
    window.confirmDeleteAlumni = function() {
        if (confirm('Are you sure you want to delete your alumni card? This action cannot be undone.')) {
            document.getElementById('deleteAlumniForm').submit();
        }
    };

    if (openModalBtn && modal) {
        openModalBtn.addEventListener('click', function (e) {
            e.preventDefault();
            modal.style.display = 'flex';
        });

        if (closeModalSpan) {
            closeModalSpan.addEventListener('click', function () {
                modal.style.display = 'none';
            });
        }

        window.addEventListener('click', function (event) {
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        });
    }

    // ==================== CATEGORY FILTER SCROLL ====================
    const filters = document.getElementById('categoryFilters');
    const scrollPrev = document.getElementById('scrollPrev');
    const scrollNext = document.getElementById('scrollNext');

    if (filters && scrollPrev && scrollNext) {
        scrollNext.addEventListener('click', () => {
            filters.scrollBy({ left: 300, behavior: 'smooth' });
        });

        scrollPrev.addEventListener('click', () => {
            filters.scrollBy({ left: -300, behavior: 'smooth' });
        });

        const updateButtons = () => {
            scrollPrev.style.opacity = filters.scrollLeft > 0 ? '1' : '0.3';
            scrollNext.style.opacity = filters.scrollLeft < (filters.scrollWidth - filters.clientWidth) ? '1' : '0.3';
        };

        filters.addEventListener('scroll', updateButtons);
        window.addEventListener('resize', updateButtons);
        updateButtons();
    }

    // ==================== DYNAMIC FILTERING & LOAD MORE ====================
    const filterTags = document.querySelectorAll('.filter-tag');
    const alumniCards = document.querySelectorAll('.alumni-card');
    const loadMoreBtn = document.getElementById('loadMoreBtn');
    let itemsToShow = 6;

    function updateCardVisibility() {
        const activeTag = document.querySelector('.filter-tag.active');
        const activeFilter = activeTag ? activeTag.getAttribute('data-filter') : 'all';
        let visibleCount = 0;
        let totalFiltered = 0;

        alumniCards.forEach((card) => {
            const cardCategory = card.getAttribute('data-category');
            const matchesFilter = activeFilter === 'all' || cardCategory === activeFilter;

            if (matchesFilter) {
                totalFiltered++;
                if (visibleCount < itemsToShow) {
                    card.style.display = 'flex';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            } else {
                card.style.display = 'none';
            }
        });

        if (loadMoreBtn) {
            if (visibleCount >= totalFiltered) {
                loadMoreBtn.parentElement.style.display = 'none';
            } else {
                loadMoreBtn.parentElement.style.display = 'block';
            }
        }
    }

    filterTags.forEach(tag => {
        tag.addEventListener('click', function (e) {
            e.preventDefault();
            filterTags.forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            itemsToShow = 6;
            updateCardVisibility();
        });
    });

    if (loadMoreBtn) {
        loadMoreBtn.addEventListener('click', function () {
            itemsToShow += 6;
            updateCardVisibility();
        });
    }

    updateCardVisibility();

    // ==================== REVEAL ON SCROLL ====================
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const revealObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('active');
            }
        });
    }, observerOptions);

    document.querySelectorAll('.reveal').forEach(el => {
        revealObserver.observe(el);
    });
});
