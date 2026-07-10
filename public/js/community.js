/**
 * Community Page JavaScript
 * Handles: Modals, Animations, Map Filtering, Comments (AJAX), Likes, Search
 */
document.addEventListener('DOMContentLoaded', function () {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    // ==================== MODAL LOGIC ====================
    const openModalBtn = document.getElementById('open-post-modal');
    const closeModalBtn = document.getElementById('close-post-modal');
    const postModal = document.getElementById('post-modal');

    if (openModalBtn && postModal) {
        openModalBtn.addEventListener('click', () => postModal.style.display = 'flex');
        if (closeModalBtn) {
            closeModalBtn.addEventListener('click', () => postModal.style.display = 'none');
        }
        postModal.addEventListener('click', (e) => {
            if (e.target === postModal) postModal.style.display = 'none';
        });
    }

    // AJAX Post Creation
    const createPostForm = document.getElementById('create-post-form');
    if (createPostForm) {
        createPostForm.addEventListener('submit', function (e) {
            e.preventDefault();

            const submitBtn = this.querySelector('button[type="submit"]');
            const originalBtnText = submitBtn.innerHTML;

            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Posting...';

            const formData = new FormData(this);
            const actionUrl = this.getAttribute('action') || '/community/post';

            fetch(actionUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    createPostForm.reset();

                    if (postModal) {
                        postModal.style.display = 'none';
                    }

                    const postsContainer = document.querySelector('.posts');
                    if (postsContainer) {
                        const noPosts = postsContainer.querySelector('.no-posts');
                        if (noPosts) {
                            noPosts.remove();
                        }

                        postsContainer.insertAdjacentHTML('afterbegin', data.html);

                        const newPost = postsContainer.firstElementChild;
                        if (newPost) {
                            newPost.style.opacity = '0';
                            newPost.style.transform = 'translateY(25px) scale(0.98)';
                            newPost.style.transition = 'opacity 0.5s ease-out, transform 0.5s ease-out';
                            setTimeout(() => {
                                newPost.style.opacity = '1';
                                newPost.style.transform = 'translateY(0) scale(1)';
                                newPost.classList.add('animate-in');
                            }, 50);
                        }
                    }

                    const successMsgDiv = document.getElementById('post-success-message');
                    if (successMsgDiv) {
                        successMsgDiv.innerText = data.message || 'Post created successfully!';
                        successMsgDiv.style.display = 'block';

                        successMsgDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });

                        setTimeout(() => {
                            successMsgDiv.style.display = 'none';
                        }, 5000);
                    }
                } else {
                    alert(data.message || 'Something went wrong. Please try again.');
                }
            })
            .catch(err => {
                console.error('Error submitting post:', err);
                alert('An error occurred. Please try again.');
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnText;
            });
        });
    }

    // Talent Modal Logic
    const openTalentBtn = document.getElementById('open-talent-modal');
    const closeTalentBtn = document.getElementById('close-talent-modal');
    const talentModal = document.getElementById('talent-modal');

    if (openTalentBtn && talentModal) {
        openTalentBtn.addEventListener('click', () => {
            talentModal.style.display = 'flex';
            setTimeout(() => talentModal.style.opacity = '1', 10);
        });
        if (closeTalentBtn) {
            closeTalentBtn.addEventListener('click', () => {
                talentModal.style.opacity = '0';
                setTimeout(() => talentModal.style.display = 'none', 300);
            });
        }
        talentModal.addEventListener('click', (e) => {
            if (e.target === talentModal) {
                talentModal.style.opacity = '0';
                setTimeout(() => talentModal.style.display = 'none', 300);
            }
        });
    }

    // ==================== SCROLL ANIMATIONS ====================
    const sections = document.querySelectorAll(
        '.community-cards, .quick-section, .recent-posts-heading, .posts, .district-section, .trending-section, .talents-section'
    );

    function isInViewport(el) {
        const rect = el.getBoundingClientRect();
        return rect.top < window.innerHeight && rect.bottom > 0;
    }

    sections.forEach(section => {
        if (isInViewport(section)) {
            section.classList.add('animate-in');
            const children = section.querySelectorAll('.comm-card, .post, .talent, .qbox, .district-card, .id-card');
            children.forEach((child, index) => {
                setTimeout(() => {
                    child.classList.add('animate-in');
                }, index * 120);
            });
        }
    });

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate-in');
                const children = entry.target.querySelectorAll('.comm-card, .post, .talent, .qbox, .district-card, .id-card');
                children.forEach((child, index) => {
                    setTimeout(() => {
                        child.classList.add('animate-in');
                    }, index * 80);
                });
                observer.unobserve(entry.target);
            }
        });
    }, {
        threshold: 0.05,
        rootMargin: '0px 0px -50px 0px'
    });

    sections.forEach(section => {
        if (!section.classList.contains('animate-in')) {
            observer.observe(section);
        }
    });

    // ==================== MAP & DISTRICT FILTERING ====================
    const toggleMapBtn = document.getElementById('toggle-map-btn');
    const mapContainer = document.querySelector('.map-container');
    const divisionText = document.getElementById('selected-division-text');

    if (toggleMapBtn && mapContainer) {
        toggleMapBtn.addEventListener('click', function () {
            const isHidden = mapContainer.style.display === 'none';
            mapContainer.style.display = isHidden ? 'flex' : 'none';

            if (divisionText) {
                divisionText.style.display = isHidden ? 'block' : 'none';
            }

            const icon = this.querySelector('i');
            if (icon) {
                icon.style.transform = isHidden ? 'rotate(180deg)' : 'rotate(0deg)';
            }
        });
    }

    const divisions = document.querySelectorAll('.division');
    const cards = document.querySelectorAll('.district-card');
    const resetBtn = document.getElementById('reset-filter');
    const viewMoreDistrictsBtn = document.getElementById('view-more-districts');
    const districtHeadline = document.querySelector('.district-header h2');

    let showAllDistricts = false;

    function applyDistrictRowLimit() {
        const isFiltered = divisionText && divisionText.innerText.includes('Filtering');
        if (isFiltered || showAllDistricts) {
            if (viewMoreDistrictsBtn) viewMoreDistrictsBtn.style.display = 'none';
            return;
        }

        const isMobile = window.innerWidth < 768;
        const visibleLimit = isMobile ? 4 : 8;

        cards.forEach((card, index) => {
            if (index < visibleLimit) {
                card.style.display = 'block';
                card.classList.add('animate-in');
            } else {
                card.style.display = 'none';
                card.classList.remove('animate-in');
            }
        });

        if (viewMoreDistrictsBtn) {
            viewMoreDistrictsBtn.style.display = cards.length > visibleLimit ? 'flex' : 'none';
        }
    }

    if (viewMoreDistrictsBtn) {
        viewMoreDistrictsBtn.addEventListener('click', function () {
            showAllDistricts = true;
            cards.forEach(card => {
                card.style.display = 'block';
                card.classList.add('animate-in');
            });
            this.style.display = 'none';
        });
    }

    window.addEventListener('resize', applyDistrictRowLimit);
    applyDistrictRowLimit();

    if (districtHeadline) {
        districtHeadline.addEventListener('click', () => {
            if (toggleMapBtn) toggleMapBtn.click();
        });
    }

    divisions.forEach(div => {
        div.addEventListener('click', function () {
            const selectedDivision = this.id;

            if (divisionText) {
                divisionText.innerText = `Filtering by: ${selectedDivision} Division`;
                divisionText.style.color = '#00AAFF';
            }

            divisions.forEach(d => {
                d.style.opacity = '1';
                d.querySelectorAll('path').forEach(path => {
                    path.style.stroke = '';
                    path.style.strokeWidth = '';
                });
            });

            this.querySelectorAll('path').forEach(activePath => {
                activePath.style.stroke = '#00AAFF';
                activePath.style.strokeWidth = '12';
            });
            this.parentNode.appendChild(this);

            let firstMatch = null;
            cards.forEach(card => {
                if (card.dataset.division === selectedDivision) {
                    card.style.display = 'block';
                    card.classList.add('animate-in');
                    if (!firstMatch) firstMatch = card;
                } else {
                    card.style.display = 'none';
                    card.classList.remove('animate-in');
                }
            });

            if (viewMoreDistrictsBtn) viewMoreDistrictsBtn.style.display = 'none';

            if (firstMatch) {
                setTimeout(() => {
                    firstMatch.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }, 100);
            }
        });
    });

    if (resetBtn) {
        resetBtn.addEventListener('click', function (e) {
            if (e) e.preventDefault();
            if (divisionText) {
                divisionText.innerText = 'Click on a division to filter';
                divisionText.style.color = '#666';
            }

            divisions.forEach(d => {
                d.style.opacity = '1';
                d.querySelectorAll('path').forEach(path => {
                    path.style.stroke = '';
                    path.style.strokeWidth = '';
                });
            });

            const searchInput = document.getElementById('district-search');
            if (searchInput && searchInput.value !== '') {
                searchInput.value = '';
            }

            showAllDistricts = false;
            applyDistrictRowLimit();
        });
    }

    // ==================== DISTRICT SEARCH ====================
    const districtSearchInput = document.getElementById('district-search');
    if (districtSearchInput) {
        districtSearchInput.addEventListener('input', function () {
            const query = this.value.trim().toLowerCase();

            if (query === '') {
                if (resetBtn) resetBtn.click();
                return;
            }

            if (viewMoreDistrictsBtn) viewMoreDistrictsBtn.style.display = 'none';

            if (divisionText) {
                divisionText.innerText = 'Search results...';
                divisionText.style.color = '#666';
            }

            divisions.forEach(d => {
                d.style.opacity = '1';
                d.querySelectorAll('path').forEach(path => {
                    path.style.stroke = '';
                    path.style.strokeWidth = '';
                });
            });

            cards.forEach(card => {
                const cardName = card.querySelector('h3').innerText.trim().toLowerCase();
                const cardDistrict = (card.dataset.district || '').toLowerCase();
                
                if (cardName.includes(query) || cardDistrict.includes(query)) {
                    card.style.display = 'block';
                    card.classList.add('animate-in');
                } else {
                    card.style.display = 'none';
                    card.classList.remove('animate-in');
                }
            });
        });
    }

    // ==================== COMMENTS ====================
    // Comment Toggle (Delegated)
    document.addEventListener('click', function (e) {
        const trigger = e.target.closest('.comment-trigger');
        if (trigger) {
            const postId = trigger.dataset.id;
            const commentSec = document.getElementById(`comments-${postId}`);
            if (commentSec) {
                const isHidden = commentSec.style.display === 'none';
                commentSec.style.display = isHidden ? 'block' : 'none';
            }
        }
    });

    // Comment Submit (AJAX - Delegated)
    document.addEventListener('submit', function (e) {
        const form = e.target.closest('.comment-form');
        if (form) {
            e.preventDefault();

            const postId = form.dataset.id;
            const input = form.querySelector('input[name="content"]');
            const parentInput = form.querySelector('input[name="parent_id"]');
            const content = input.value;
            const parentId = parentInput ? parentInput.value : null;

            const commentsList = document.querySelector(`#comments-${postId} .comments-list`);
            const countSpan = document.querySelector(`.comment-trigger[data-id="${postId}"] .count`);

            if (!content.trim()) return;

            fetch(`/community/post/${postId}/comment`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ content: content, parent_id: parentId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    input.value = '';

                    const commentDiv = document.createElement('div');
                    commentDiv.setAttribute('id', `comment-${data.comment.id}`);
                    commentDiv.style.display = 'flex';
                    commentDiv.style.gap = '10px';
                    commentDiv.style.alignItems = 'flex-start';
                    commentDiv.style.marginBottom = '12px';

                    const avatarSrc = data.comment.user.profile_image ? `/storage/${data.comment.user.profile_image}` : '';
                    const avatarHtml = avatarSrc
                        ? `<img src="${avatarSrc}" style="width:100%; height:100%; object-fit:cover;">`
                        : `🎓`;

                    commentDiv.innerHTML = `
                        <div style="width: 32px; height: 32px; min-width: 32px; border-radius: 50%; overflow: hidden; background: #E2E8F0; display: flex; align-items: center; justify-content: center; font-size: 16px;">
                            ${avatarHtml}
                        </div>
                        <div style="flex: 1;">
                            <div style="background: #F0F2F5; padding: 10px 14px; border-radius: 18px; display: inline-block; max-width: 88%; position: relative; box-shadow: 0 1px 2px rgba(0,0,0,0.03);">
                                <span style="font-weight: 700; color: #1A202C; font-size: 13px; display: block; margin-bottom: 2px;">${data.comment.user.name}</span>
                                <span class="comment-content" style="color: #4A5568; font-size: 13px; line-height: 1.4; word-break: break-word;">${data.comment.content}</span>
                                <div style="position: absolute; top: 8px; right: -25px; display: flex; flex-direction: column; gap: 4px;">
                                    <i class="fas fa-pencil-alt edit-comment-btn" data-id="${data.comment.id}" style="cursor: pointer; color: #A0AEC0; font-size: 10px;" title="Edit"></i>
                                    <i class="fas fa-trash-alt delete-comment-btn" data-id="${data.comment.id}" style="cursor: pointer; color: #FC8181; font-size: 10px;" title="Delete"></i>
                                </div>
                            </div>
                            <div style="display: flex; gap: 12px; font-size: 11px; color: #718096; margin-top: 4px; margin-left: 10px; font-weight: 600;">
                                <span>Just now</span>
                                <span class="comment-like-btn" data-id="${data.comment.id}" style="cursor: pointer; color: #4A5568;">
                                    Like <span class="like-count"></span>
                                </span>
                                <span class="comment-reply-btn" data-id="${data.comment.id}" data-username="${data.comment.user.name}" style="cursor: pointer; color: #4A5568;">Reply</span>
                            </div>
                        </div>
                    `;

                    if (parentId) {
                        const repliesList = document.querySelector(`#replies-${parentId}`);
                        if (repliesList) {
                            commentDiv.style.marginLeft = '20px';
                            const avatarNode = commentDiv.querySelector('div[style*="width: 32px"]');
                            const bubbleNode = commentDiv.querySelector('div[style*="padding: 10px 14px"]');
                            if (avatarNode) { avatarNode.style.width = '24px'; avatarNode.style.height = '24px'; avatarNode.style.minWidth = '24px'; avatarNode.style.fontSize = '12px'; }
                            if (bubbleNode) { bubbleNode.style.padding = '8px 12px'; }
                            repliesList.appendChild(commentDiv);
                        }
                        if (parentInput) parentInput.value = '';
                        input.placeholder = 'Say something nice...';
                    } else {
                        const emptyDiv = commentsList.querySelector('div[style*="text-align: center"]');
                        if (emptyDiv) emptyDiv.remove();
                        commentsList.appendChild(commentDiv);
                        commentsList.scrollTop = commentsList.scrollHeight;
                    }

                    if (countSpan) countSpan.innerText = data.comments_count;
                }
            })
            .catch(err => console.error('Error posting comment:', err));
        }
    });

    // ==================== EDIT, DELETE, LIKE, REPLY COMMENTS ====================
    document.addEventListener('click', function (e) {
        // DELETE
        if (e.target.classList.contains('delete-comment-btn')) {
            const commentId = e.target.dataset.id;
            if (!confirm('Are you sure you want to delete this comment?')) return;

            fetch(`/community/comment/${commentId}`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const commentDiv = document.getElementById(`comment-${commentId}`);
                    const commentSec = commentDiv.closest('.comment-section');
                    const postId = commentSec.id.replace('comments-', '');
                    commentDiv.remove();
                    const countSpan = document.querySelector(`.comment-trigger[data-id="${postId}"] .count`);
                    if (countSpan) countSpan.innerText = data.comments_count;
                }
            })
            .catch(err => console.error('Error deleting comment:', err));
        }

        // EDIT
        if (e.target.classList.contains('edit-comment-btn')) {
            const commentId = e.target.dataset.id;
            const commentItem = document.getElementById(`comment-${commentId}`);
            const contentSpan = commentItem.querySelector('.comment-content');

            if (commentItem.querySelector('.edit-form')) return;

            const currentText = contentSpan.innerText;
            contentSpan.style.display = 'none';

            const formHtml = `
                <form class="edit-form" style="display: flex; gap: 8px; margin-top: 5px; width: 100%;">
                    <input type="text" value="${currentText}" style="flex: 1; padding: 6px 10px; border: 1px solid #E2E8F0; border-radius: 8px; font-size: 13px;" required>
                    <button type="submit" style="background: #00AAFF; color: white; border: none; padding: 6px 12px; border-radius: 8px; font-size: 12px; font-weight: 700; cursor: pointer;">Save</button>
                    <button type="button" class="cancel-edit" style="background: #e2e8f0; color: #4a5568; border: none; padding: 6px 12px; border-radius: 8px; font-size: 12px; cursor: pointer;">Cancel</button>
                </form>
            `;
            commentItem.insertAdjacentHTML('beforeend', formHtml);

            const form = commentItem.querySelector('.edit-form');
            const input = form.querySelector('input');

            form.addEventListener('submit', function (ev) {
                ev.preventDefault();
                const newText = input.value;

                fetch(`/community/comment/${commentId}`, {
                    method: 'PUT',
                    headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json', 'Content-Type': 'application/json' },
                    body: JSON.stringify({ content: newText })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        contentSpan.innerText = data.content;
                        contentSpan.style.display = 'block';
                        form.remove();
                    }
                })
                .catch(err => console.error('Error updating comment:', err));
            });

            form.querySelector('.cancel-edit').addEventListener('click', function () {
                contentSpan.style.display = 'block';
                form.remove();
            });
        }

        // LIKE COMMENT
        if (e.target.classList.contains('comment-like-btn') || e.target.closest('.comment-like-btn')) {
            const btn = e.target.classList.contains('comment-like-btn') ? e.target : e.target.closest('.comment-like-btn');
            const commentId = btn.dataset.id;
            const countSpan = btn.querySelector('.like-count');

            fetch(`/community/comment/${commentId}/like`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    btn.style.color = data.liked ? '#00AAFF' : '#4A5568';
                    if (countSpan) {
                        countSpan.innerText = data.likes_count > 0 ? `(${data.likes_count})` : '';
                    }
                }
            })
            .catch(err => console.error('Error liking comment:', err));
        }

        // REPLY
        if (e.target.classList.contains('comment-reply-btn')) {
            const commentId = e.target.dataset.id;
            const username = e.target.dataset.username;
            const commentSec = e.target.closest('.comment-section');
            const input = commentSec.querySelector('.comment-form input[name="content"]');
            const parentInput = commentSec.querySelector('.comment-form input[name="parent_id"]');

            if (parentInput) parentInput.value = commentId;
            if (input) {
                input.placeholder = `Reply to ${username}...`;
                input.focus();
            }
        }
    });

    // ==================== LIKE POST (Delegated) ====================
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.like-btn');
        if (btn) {
            const postId = btn.dataset.id;
            const heartIcon = btn.querySelector('i');
            const countSpan = btn.querySelector('.count');

            fetch(`/community/post/${postId}/like`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    countSpan.innerText = data.likes_count;
                    if (data.liked) {
                        heartIcon.classList.remove('far');
                        heartIcon.classList.add('fas');
                        heartIcon.style.color = '#E0245E';
                    } else {
                        heartIcon.classList.remove('fas');
                        heartIcon.classList.add('far');
                        heartIcon.style.color = '#718096';
                    }
                }
            })
            .catch(err => console.error('Error liking post:', err));
        }
    });

    // ==================== VIEW MORE POSTS ====================
    const viewMorePostsBtn = document.getElementById('view-more-posts');
    const allPosts = document.querySelectorAll('.posts .post');
    const visibleLimit = 5;

    if (viewMorePostsBtn && allPosts.length > visibleLimit) {
        allPosts.forEach((post, index) => {
            if (index >= visibleLimit) {
                post.style.display = 'none';
            }
        });
        viewMorePostsBtn.addEventListener('click', function () {
            allPosts.forEach(post => {
                post.style.display = 'block';
                post.classList.add('animate-in');
            });
            this.style.display = 'none';
        });
    } else if (viewMorePostsBtn) {
        viewMorePostsBtn.style.display = 'none';
    }

    // ==================== TALENT SEARCH ====================
    const deptSearch = document.getElementById('talent-dept-search');
    const skillSearch = document.getElementById('talent-skill-search');
    const talentCards = document.querySelectorAll('#talents-section .id-card');

    const filterTalents = () => {
        const deptQuery = deptSearch ? deptSearch.value.toLowerCase().trim() : '';
        const skillQuery = skillSearch ? skillSearch.value.toLowerCase().trim() : '';

        talentCards.forEach(card => {
            const cardDept = (card.dataset.dept || '').toLowerCase();
            const cardSkill = (card.dataset.skill || '').toLowerCase();
            const deptMatch = cardDept.includes(deptQuery);
            const skillWords = cardSkill.split(/[\s,/-]+/);
            const skillMatch = skillQuery === '' || skillWords.some(word => word.startsWith(skillQuery));
            card.style.display = (deptMatch && skillMatch) ? 'flex' : 'none';
        });
    };

    // ==================== POST ACTIONS (EDIT & DELETE) ====================
    
    // Helper to show success alerts
    function displaySuccessMessage(msg) {
        const successMsgDiv = document.getElementById('post-success-message');
        if (successMsgDiv) {
            successMsgDiv.innerText = msg;
            successMsgDiv.style.display = 'block';
            successMsgDiv.style.background = '#c6f6d5';
            successMsgDiv.style.color = '#22c55e';
            successMsgDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });
            setTimeout(() => {
                successMsgDiv.style.display = 'none';
            }, 5000);
        }
    }

    // Toggle post actions dropdown menu
    document.addEventListener('click', function (e) {
        const trigger = e.target.closest('.post-actions-trigger');
        if (trigger) {
            e.stopPropagation();
            const dropdown = trigger.nextElementSibling;
            
            // Close all other dropdowns
            document.querySelectorAll('.post-actions-menu').forEach(menu => {
                if (menu !== dropdown) menu.style.display = 'none';
            });
            
            dropdown.style.display = dropdown.style.display === 'none' ? 'block' : 'none';
            return;
        }
        
        // Close all dropdowns if clicking elsewhere
        document.querySelectorAll('.post-actions-menu').forEach(menu => {
            menu.style.display = 'none';
        });
    });

    // AJAX Post Deletion
    document.addEventListener('click', function (e) {
        const deleteBtn = e.target.closest('.post-action-delete');
        if (deleteBtn) {
            const postId = deleteBtn.dataset.id;
            if (!confirm('Are you sure you want to delete this post? This will also delete all comments/likes on it.')) return;
            
            fetch(`/community/post/${postId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const postCard = document.getElementById(`post-${postId}`);
                    if (postCard) {
                        postCard.style.opacity = '0';
                        postCard.style.transform = 'scale(0.9) translateY(20px)';
                        setTimeout(() => postCard.remove(), 300);
                    }
                    displaySuccessMessage(data.message || 'Post deleted successfully!');
                } else {
                    alert(data.message || 'Failed to delete post.');
                }
            })
            .catch(err => {
                console.error(err);
                alert('Error deleting post.');
            });
        }
    });

    // Post Edit Modal Trigger
    const editPostModal = document.getElementById('edit-post-modal');
    const closeEditPostModalBtn = document.getElementById('close-edit-post-modal');
    const editPostForm = document.getElementById('edit-post-form');
    
    if (closeEditPostModalBtn && editPostModal) {
        closeEditPostModalBtn.addEventListener('click', () => editPostModal.style.display = 'none');
        editPostModal.addEventListener('click', (e) => {
            if (e.target === editPostModal) editPostModal.style.display = 'none';
        });
    }

    document.addEventListener('click', function (e) {
        const editBtn = e.target.closest('.post-action-edit');
        if (editBtn && editPostModal && editPostForm) {
            const postId = editBtn.dataset.id;
            const content = editBtn.dataset.content;
            const type = editBtn.dataset.type;
            const actionText = editBtn.dataset.actionText;
            const actionLink = editBtn.dataset.actionLink;
            const attachment = editBtn.dataset.attachment;
            
            // Populate form fields
            document.getElementById('edit-post-id').value = postId;
            document.getElementById('edit-post-content').value = content;
            document.getElementById('edit-post-type').value = type;
            document.getElementById('edit-post-action-text').value = actionText && actionText !== 'null' ? actionText : '';
            document.getElementById('edit-post-action-link').value = actionLink && actionLink !== 'null' ? actionLink : '';
            
            // Set action URL
            editPostForm.setAttribute('action', `/community/post/${postId}`);
            
            // Attachment wrapper check
            const attachmentWrapper = document.getElementById('edit-current-attachment-wrapper');
            const attachmentLink = document.getElementById('edit-current-attachment');
            const removeCheckbox = editPostForm.querySelector('input[name="remove_attachment"]');
            
            if (removeCheckbox) removeCheckbox.checked = false;
            
            if (attachment && attachment !== 'null' && attachment !== '') {
                attachmentLink.setAttribute('href', attachment);
                attachmentWrapper.style.display = 'flex';
            } else {
                attachmentWrapper.style.display = 'none';
            }
            
            editPostModal.style.display = 'flex';
        }
    });

    // AJAX Post Update Submission
    if (editPostForm) {
        editPostForm.addEventListener('submit', function (e) {
            e.preventDefault();
            
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalBtnText = submitBtn.innerHTML;
            
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
            
            const formData = new FormData(this);
            const actionUrl = this.getAttribute('action');
            const postId = document.getElementById('edit-post-id').value;
            
            fetch(actionUrl, {
                method: 'POST', // We use POST for multipart data, PUT is spoofed inside formData
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(res => {
                if (!res.ok) throw new Error('Network error');
                return res.json();
            })
            .then(data => {
                if (data.success) {
                    editPostModal.style.display = 'none';
                    displaySuccessMessage(data.message || 'Post updated successfully!');
                    
                    // Update DOM content immediately
                    const postCard = document.getElementById(`post-${postId}`);
                    if (postCard) {
                        const contentParagraph = postCard.querySelector('.post-text-content');
                        if (contentParagraph) {
                            contentParagraph.innerText = data.post.content;
                        }
                        
                        // Update action attributes on edit button so next edits have updated values
                        const editBtn = postCard.querySelector('.post-action-edit');
                        if (editBtn) {
                            editBtn.dataset.content = data.post.content;
                            editBtn.dataset.type = data.post.type;
                            editBtn.dataset.actionText = data.post.action_text || '';
                            editBtn.dataset.actionLink = data.post.action_link || '';
                            editBtn.dataset.attachment = data.post.attachment || '';
                        }
                        
                        // Handle showing/removing attachment inside post card
                        let fileLink = postCard.querySelector('a.file');
                        if (data.post.attachment) {
                            if (fileLink) {
                                fileLink.setAttribute('href', data.post.attachment);
                                fileLink.style.display = 'inline-flex';
                            } else {
                                const newFileLink = `<a class="file" href="${data.post.attachment}" target="_blank" style="margin-top: 10px; display: inline-flex; align-items: center; gap: 6px; padding: 6px 14px; background: rgba(0,170,255,0.08); color: #00AAFF; border-radius: 10px; font-size: 13px; font-weight: 600; text-decoration: none;">
                                    <i class="fas fa-paperclip"></i> View File
                                </a>`;
                                postCard.querySelector('.post-top').insertAdjacentHTML('afterend', newFileLink);
                            }
                        } else {
                            if (fileLink) fileLink.remove();
                        }
                        
                        // Handle showing/removing action link inside post card
                        let joinLink = postCard.querySelector('a.join');
                        if (data.post.action_text) {
                            const linkHref = data.post.action_link && data.post.action_link.startsWith('http') ? data.post.action_link : 'http://' + (data.post.action_link || '#');
                            if (joinLink) {
                                joinLink.setAttribute('href', linkHref);
                                joinLink.innerHTML = `<i class="fas fa-external-link-alt"></i> ${data.post.action_text}`;
                                joinLink.style.display = 'inline-flex';
                            } else {
                                const newJoinLink = `<a class="join" href="${linkHref}" target="_blank" style="margin-top: 10px; display: inline-flex; align-items: center; gap: 6px; padding: 6px 14px; background: #fff; border: 1.5px solid #1A202C; color: #1A202C; border-radius: 10px; font-size: 13px; font-weight: 700; text-decoration: none; margin-left: 8px; transition: all 0.2s ease;">
                                    <i class="fas fa-external-link-alt"></i> ${data.post.action_text}
                                </a>`;
                                const anchorPoint = postCard.querySelector('a.file') || postCard.querySelector('.post-top');
                                anchorPoint.insertAdjacentHTML('afterend', newJoinLink);
                            }
                        } else {
                            if (joinLink) joinLink.remove();
                        }
                    }
                } else {
                    alert(data.message || 'Failed to update post.');
                }
            })
            .catch(err => {
                console.error(err);
                alert('An error occurred while saving.');
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnText;
            });
        });
    }

    if (deptSearch) deptSearch.addEventListener('input', filterTalents);
    if (skillSearch) skillSearch.addEventListener('input', filterTalents);
});
