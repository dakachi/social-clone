<div class="instagram-preview instagram-post border border-gray-400 rounded bg-white">
    
    <div class="d-flex pf-13">
        
        <div class="d-flex align-items-center gap-8">
            <div class="size-40 size-child">
                <img src="{{ theme_public_asset( "img/default.png" ) }}" class="align-self-center rounded-circle border cpv-avatar" alt="">
            </div>
            <div class="d-flex align-items-center flex-row-fluid flex-wrap">
                <div class="flex-grow-1 me-2 text-truncate">
                    <a href="javascript:void(0);" class="text-gray-800 text-hover-primary fs-14 fw-bold cpv-name">{{ __("Your name") }}</a>
                    <span class="text-gray-400 d-block fs-12">{{ date("M j") }}</span>
                </div>
            </div>
        </div>

    </div>

    <div class="mb-0">
        <div class="cpv-media mb-3">
            <div class="cpv-img wp-100 cpv-instagram-img d-none"></div>
            <div class="cpv-instagram-img-view wp-100">
                <img src="{{ theme_public_asset( "img/default.png" ) }}" class="wp-100">
            </div>
        </div>

        <div class="cpv-text fs-14 px-3 mb-3 text-truncate-5"></div>
    </div>

    <div class="px-3 py-2 d-flex justify-content-between text-gray-800 align-items-center fs-22">
        <div class="d-flex justify-content-between gap-16">
            <div class="d-flex flex-stack">
                <div class="symbol symbol-45px me-2">
                    <i class="fa-regular fa-comment"></i>
                </div>
            </div>
            <div class="d-flex flex-stack">
                <div class="symbol symbol-45px me-2">
                    <i class="fa-regular fa-heart"></i>
                </div>
            </div>
            <div class="d-flex flex-stack">
                <div class="symbol symbol-45px me-2">
                    <i class="fa-regular fa-share"></i>
                </div>
            </div>
        </div>
    </div>

</div>

<div class="instagram-preview instagram-stories d-none">
    <div class="cpv-media mb-3 bg-gray-900 h-800">
        <div class="cpv-instagram-stories-img-view img-wrap wp-100 hp-100">
            <img src="{{ theme_public_asset( "img/default.png" ) }}" class="wp-100">
        </div>
    </div>
</div>

<script>
$(document).on('change', 'input[name="type"]', function () {
    var isLink = $(this).val() === 'link';
    $('.instagram-preview .cpv-link').toggleClass('d-none', !isLink);
    $('.instagram-preview .cpv-media').removeClass('d-none');
});

$(document).on('change', 'input[name="options[ig_type]"]', function () {
    var isStory = $(this).val() === 'stories';
    $('.instagram-preview .instagram-post').toggleClass('d-none', isStory);
    $('.instagram-preview .instagram-stories').toggleClass('d-none', !isStory);
});

$(document).ready(function () {
    $('input[name="options[ig_type]"]:checked').trigger('change');
});


function instagram_renderMediaCarousel(elements) {
    if (elements.length === 0) return '';

    var id = 'instagram-carousel-' + Math.random().toString(36).substr(2, 8);

    let items = '';
    elements.forEach((el, idx) => {
        var isActive = idx === 0 ? 'active' : '';
        items += `
            <div class="carousel-item ${isActive}">
                <div class="img-wrap">${el.outerHTML}</div>
            </div>
        `;
    });

    return `
        <div id="${id}" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-inner">${items}</div>
            ${elements.length > 1 ? `
                <button class="carousel-control-prev" type="button" data-bs-target="#${id}" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Previous</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#${id}" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Next</span>
                </button>` : ''}
        </div>
    `;
}

function instagram_renderStoryImage(elements) {
    if (elements.length === 0) return '';
    var first = elements[0];
    return `<div class="img-wrap hp-100 wp-100">${first.outerHTML}</div>`;
}

function updateInstagramPreviews() {
    var elements = document.querySelectorAll('.cpv-instagram-img > img, .cpv-instagram-img > div');
    var mediaList = Array.from(elements).filter(el =>
        el.tagName.toLowerCase() === 'img' || el.tagName.toLowerCase() === 'div'
    );

    if (mediaList.length > 0) {
        // Render carousel for post
        var postView = document.querySelector('.cpv-instagram-img-view');
        if (postView) {
            postView.innerHTML = instagram_renderMediaCarousel(mediaList);
        }

        // Render 1 image for story
        var storyView = document.querySelector('.cpv-instagram-stories-img-view');
        if (storyView) {
            storyView.innerHTML = instagram_renderStoryImage(mediaList);
        }
    }

}

// MutationObserver
var container = document.querySelector('.cpv-instagram-img');
if (container) {
    var observer = new MutationObserver(updateInstagramPreviews);
    observer.observe(container, {
        childList: true,
        subtree: false,
        attributes: true,
        attributeFilter: ['src']
    });

    document.addEventListener('DOMContentLoaded', updateInstagramPreviews);
    updateInstagramPreviews(); // also trigger on load
}
</script>
