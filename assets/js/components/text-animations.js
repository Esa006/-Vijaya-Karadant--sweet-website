/**
 * Sweets Website - Text Animations Helper
 * =============================================================
 * File: text-animations.js
 * Description: Splits text into animated spans for reveal effects
 * =============================================================
 */

document.addEventListener('DOMContentLoaded', () => {
    const initTextAnimations = () => {
        // Elastic Pop
        const elasticHeads = document.querySelectorAll('.ani-word-elastic');
        elasticHeads.forEach(head => splitLetters(head, 'ani-letter'));

        // Mask Slant Reveal
        const slantHeads = document.querySelectorAll('.ani-mask-slant');
        slantHeads.forEach(head => splitLetters(head, 'ani-slant-char'));

        function splitLetters(head, charClass) {
            const text = head.textContent.trim();
            const words = text.split(' ');
            head.innerHTML = ''; 

            words.forEach((word, wordIdx) => {
                const wordSpan = document.createElement('span');
                wordSpan.style.display = 'inline-block';
                wordSpan.style.whiteSpace = 'nowrap';
                if (charClass === 'ani-slant-char') {
                    wordSpan.style.overflow = 'hidden';
                    wordSpan.style.verticalAlign = 'bottom';
                }
                
                const letters = [...word];
                letters.forEach((char, charIdx) => {
                    const span = document.createElement('span');
                    span.textContent = char;
                    span.className = charClass;
                    
                    const delay = (wordIdx * 0.1) + (charIdx * 0.03);
                    span.style.animationDelay = `${delay}s`;
                    
                    wordSpan.appendChild(span);
                });

                head.appendChild(wordSpan);

                if (wordIdx < words.length - 1) {
                    const space = document.createElement('span');
                    space.className = 'is-space';
                    space.innerHTML = '&nbsp;';
                    head.appendChild(space);
                }
            });
        }

        // Intersection Observer to trigger animation
        const observerOptions = { threshold: 0.1 };
        const aniObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-animated', 'in-view');
                    aniObserver.unobserve(entry.target);
                }
            });
        }, observerOptions);

        document.querySelectorAll('.ani-word-elastic, .ani-mask-slant').forEach(el => aniObserver.observe(el));
    };

    initTextAnimations();
});
