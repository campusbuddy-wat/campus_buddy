// Register ScrollTrigger plugin
gsap.registerPlugin(ScrollTrigger);

document.addEventListener("DOMContentLoaded", () => {

    // 1. Initial Entry Animation for the Mascot
    // The mascot appears big and centered, then moves to its starting position for Section 1
    const tlIntro = gsap.timeline();

    // Set initial state for character container
    gsap.set(".character-container", {
        opacity: 0,
        scale: 0.5,
        xPercent: -50,
        yPercent: -50,
        left: "50%",
        top: "50%"
    });

    // Set initial states for cards (fade in from bottom)
    gsap.set(".glass-card", {
        y: 50,
        opacity: 0
    });

    // Run opening animation string
    tlIntro.to(".character-container", {
        opacity: 1,
        scale: 1.2,
        duration: 1,
        ease: "back.out(1.7)"
    })
        // Then move it to the right for the intro section, perfectly symmetric with the card
        .to(".character-container", {
            x: window.innerWidth > 768 ? 260 : 0,
            scale: 1,
            duration: 1.5,
            ease: "power3.inOut"
        }, "+=0.5")
        // Fade in the first card
        .to(".intro-content .glass-card", {
            y: 0,
            opacity: 1,
            duration: 0.8,
            ease: "power2.out"
        }, "-=1");

    // ==========================================================
    // GSAP ScrollTrigger Story Animation
    // ==========================================================

    // We want to use a master timeline linked to the scroll of the page to scrub the character back and forth

    const isDesktop = window.innerWidth > 768;

    // Only apply complex positional animations on desktop. 
    // On mobile, the CSS sets the character to the background.
    if (isDesktop) {

        let scrollTl = gsap.timeline({
            scrollTrigger: {
                trigger: ".story-container",
                start: "top top",
                end: "bottom bottom",
                scrub: 1, // Smooth scrubbing, takes 1 second to catch up
            }
        });

        // Use a 100-duration system to map exactly to scroll progress percentages
        scrollTl.add("start", 0);

        // Transition 1: Intro -> Problems (Scroll from 15% to 33%)
        // Character waits at Intro, then safely crosses to the left.
        scrollTl.fromTo(".character-container", {
            x: 260,
            top: "50%",
            rotation: 0
        }, {
            x: -260, // Left side
            top: "50%",
            rotation: -5,
            ease: "power1.inOut",
            duration: 18, // Takes 18% of total scroll
            immediateRender: false
        }, 15); // Starts moving 15% down the page

        // Transition 2: Problems -> Solutions (Scroll from 48% to 66%)
        // Character waits at Problems, then safely crosses to the right.
        scrollTl.fromTo(".character-container", {
            x: -260,
            top: "50%",
            rotation: -5
        }, {
            x: 260, // Right side
            top: "50%",
            rotation: 5,
            ease: "power1.inOut",
            duration: 18,
            immediateRender: false
        }, 48); // Starts moving 48% down the page

        // Transition 3: Solutions -> CTA (Scroll from 82% to 100%)
        // Character waits at Solutions, then safely centers for CTA.
        scrollTl.fromTo(".character-container", {
            x: 260,
            top: "50%",
            rotation: 5
        }, {
            x: 0, // Center
            top: "35%", // Moved down to avoid overlapping navbar
            scale: 0.8,
            rotation: 0,
            ease: "power2.inOut",
            duration: 18,
            immediateRender: false
        }, 82); // Starts moving 82% down the page

    }
    // ==========================================================
    // Animate Cards appearing as they enter viewport
    // ==========================================================

    // We already faded in intro. Now do the others via independent ScrollTriggers

    // We already faded in intro. Now do the others via independent ScrollTriggers

    // Problem card: Left side, slide in from the left
    gsap.set(".problem-content .glass-card", { x: "-=100", opacity: 0 });
    gsap.to(".problem-content .glass-card", {
        scrollTrigger: {
            trigger: ".problem-content",
            start: "top 70%",
            end: "bottom center",
            toggleActions: "play none none reverse"
        },
        x: "+=100", // Slide back 100px relative to its current position
        opacity: 1,
        duration: 0.8,
        ease: "power2.out"
    });

    // Solution card: Right side, slide in from the right
    gsap.set(".solution-content .glass-card", { x: "+=100", opacity: 0 });
    gsap.to(".solution-content .glass-card", {
        scrollTrigger: {
            trigger: ".solution-content",
            start: "top 70%",
            end: "bottom center",
            toggleActions: "play none none reverse"
        },
        x: "-=100", // Slide back 100px relative to its current position
        opacity: 1,
        duration: 0.8,
        ease: "power2.out"
    });

    // CTA card: Bottom, slide up softly
    gsap.set(".cta-content .glass-card", { y: "+=100", opacity: 0 });
    gsap.to(".cta-content .glass-card", {
        scrollTrigger: {
            trigger: ".cta-content",
            start: "top 80%",
            end: "bottom center",
            toggleActions: "play none none reverse"
        },
        y: "-=100", // Slide up 100px
        opacity: 1,
        duration: 0.8,
        ease: "power2.out"
    });

    // Keep aspect ratio dynamically on resize
    window.addEventListener('resize', () => {
        ScrollTrigger.refresh();
    });
});
