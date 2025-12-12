document.addEventListener('DOMContentLoaded', function () {

    document.querySelectorAll('.svgallery-slider').forEach(function (sliderEl) {

        const wrapper = sliderEl.closest('.svgallery-wrapper');
        const prevBtn = wrapper.querySelector('.svgallery-arrow-prev');
        const nextBtn = wrapper.querySelector('.svgallery-arrow-next');

        const swiper = new Swiper(sliderEl, {
            slidesPerView: 2,
            spaceBetween: 16,
            loop: true,
            loopAdditionalSlides: 7,
            autoplay: {
                delay: 5000,
                disableOnInteraction: false
            },
            navigation: {
                prevEl: prevBtn,
                nextEl: nextBtn
            },
            breakpoints: {
                768: { slidesPerView: 3 },
                1024: { slidesPerView: 5 }
            },
            preventClicks: false,
            preventClicksPropagation: false
        });

        const videos = Array.from(
            sliderEl.querySelectorAll('.swiper-slide:not(.swiper-slide-duplicate) .svgallery-video')
        );

        videos.forEach(video => {
            const thumb = video.closest('.play-thumb');

            thumb.addEventListener('click', () => {
                if (video.paused) {
                    videos.forEach(v => v.pause());
                    video.play();
                } else {
                    video.pause();
                }
            });

            video.addEventListener('play', () => {
                thumb.classList.add('is-playing');
                swiper.autoplay.stop();
            });

            function resumeIfNonePlaying() {
                const anyPlaying = videos.some(v => !v.paused && !v.ended);
                if (!anyPlaying) swiper.autoplay.start();
            }

            video.addEventListener('pause', () => {
                thumb.classList.remove('is-playing');
                resumeIfNonePlaying();
            });

            video.addEventListener('ended', () => {
                thumb.classList.remove('is-playing');
                resumeIfNonePlaying();
            });

            thumb.querySelector('.svgallery-mute').onclick = e => {
                e.stopPropagation();
                video.muted = !video.muted;
                e.target.textContent = video.muted ? '🔇' : '🔊';
            };

            thumb.querySelector('.svgallery-fullscreen').onclick = e => {
                e.stopPropagation();
                if (video.requestFullscreen) video.requestFullscreen();
                else if (video.webkitEnterFullscreen) video.webkitEnterFullscreen();
            };
        });
    });
});
