<div class="border border-gray-400 rounded bg-white">
    
    <div class="d-flex pf-13">
        
        <div class="d-flex align-items-center gap-8">
            <div class="size-40 size-child">
                <img src="{{ theme_public_asset( "img/default.png" ) }}" class="align-self-center rounded-circle border cpv-avatar" alt="">
            </div>
            <div class="d-flex align-items-center flex-row-fluid flex-wrap">
                <div class="flex-grow-1 me-2 text-truncate">
                    <a href="javascript:void(0);" class="text-gray-800 text-hover-primary fs-14 fw-bold cpv-name">{{ __("Your name") }}</a>
                    <span class="text-muted fw-semibold d-block fs-12">{{ date("M j") }}</span>
                </div>
            </div>
        </div>

    </div>

    <div class="mb-0">
        
        <div class="cpv-text fs-14 px-3 mb-3 text-truncate-5"></div>

        <div class="cpv-media">
            <div class="cpv-img w-100 cpv-fb-img d-none"></div>
            <div class="cpv-fb-img-view w-100">
                <img src="{{ theme_public_asset( "img/default.png" ) }}" class="w-100">
            </div>
        </div>

        <div class="cpv-link d-flex justify-content-start w-100 d-none border-top">
            <div class="cpv-link-img img-wrap w-100 size-120 size-child border-end">
                <img src="{{ theme_public_asset( "img/default.png" ) }}" class="w-100">
            </div>
            <div class="d-flex flex-column justify-content-center w-100 bg-gray-100 fs-12 pf-13">
                <div class="cpv-default">
                    <div class="h-12 bg-gray-300 mb-2"></div>
                    <div class="h-12 bg-gray-300 mb-2"></div>
                    <div class="h-12 bg-gray-300 mb-1"></div>
                    <div class="h-12 bg-gray-300 mb-1 wp-50"></div>
                </div>
                <div class="cpv-link-web text-uppercase fs-10 fw-3 text-truncate-1">
                    
                </div>
                <div class="cpv-link-title fw-6 text-truncate-1">
                    
                </div>
                <div class="cpv-link-desc text-gray-700 text-truncate-2">
                    
                </div>
            </div>
        </div>
    </div>

    <div class="border-top px-3 py-2">
        <div class="d-flex justify-content-between">
            <div class="d-flex flex-stack">
                <div class="symbol symbol-45px me-2">
                    <i class="fal fa-thumbs-up"></i>
                </div>
                <div class="d-flex align-items-center flex-row-fluid flex-wrap">
                    <div class="flex-grow-1 me-2 text-truncate">
                        <span class="text-gray-800 fs-12 fw-bold">{{ __("Like") }}</span>
                    </div>
                </div>
            </div>
            <div class="d-flex flex-stack">
                <div class="symbol symbol-45px me-2">
                    <i class="fal fa-comment-alt"></i>
                </div>
                <div class="d-flex align-items-center flex-row-fluid flex-wrap">
                    <div class="flex-grow-1 me-2 text-truncate">
                        <span class="text-gray-800 fs-12 fw-bold">{{ __("Comment") }}</span>
                    </div>
                </div>
            </div>
            <div class="d-flex flex-stack">
                <div class="symbol symbol-45px me-2">
                    <i class="fal fa-share"></i>
                </div>
                <div class="d-flex align-items-center flex-row-fluid flex-wrap">
                    <div class="flex-grow-1 me-2 text-truncate">
                        <span class="text-gray-800 fs-12 fw-bold">{{ __("Share") }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
function fb_renderMediaGrid(elements) {
    var fb_total = elements.length;
    var fb_visible = elements.slice(0, 4);
    var fb_moreCount = fb_total - 4;

    let fb_html = '';

    if (fb_total === 1) {
        fb_html += `
            <div class="cpv-grid" style="grid-template-columns: 1fr;">
                <div class="img-wrap">${elements[0].outerHTML}</div>
            </div>
        `;
    } else if (fb_total === 2) {
        fb_html += `
            <div class="cpv-grid" style="grid-template-columns: repeat(2, 1fr);">
                ${fb_visible.map(el => `<div class="img-wrap">${el.outerHTML}</div>`).join('')}
            </div>
        `;
    } else if (fb_total === 3) {
        fb_html += `
            <div class="cpv-grid" style="grid-template-columns: 2fr 1fr; grid-template-rows: repeat(2, 1fr);">
                <div class="img-wrap" style="grid-row: span 2;">${elements[0].outerHTML}</div>
                <div class="img-wrap">${elements[1].outerHTML}</div>
                <div class="img-wrap">${elements[2].outerHTML}</div>
            </div>
        `;
    } else {
        fb_html += `<div class="cpv-grid" style="grid-template-columns: repeat(2, 1fr);">`;
        fb_visible.forEach((el, idx) => {
            var fb_isLast = idx === 3 && fb_moreCount > 0;
            var fb_overlay = fb_isLast ? `<div class="overlay">+${fb_moreCount}</div>` : '';
            fb_html += `<div class="img-wrap">${el.outerHTML}${fb_overlay}</div>`;
        });
        fb_html += `</div>`;
    }

    return fb_html;
}

function fb_onMediaItemsChange() {
    var fb_elements = document.querySelectorAll('.cpv-fb-img > img, .cpv-fb-img > div');
    if (fb_elements.length > 0) {
        var fb_mediaList = Array.from(fb_elements).filter(el =>
            el.tagName.toLowerCase() === 'img' || el.tagName.toLowerCase() === 'div'
        );

        var fb_rendered = fb_renderMediaGrid(fb_mediaList);
        document.querySelector('.cpv-fb-img-view').innerHTML = fb_rendered;
    }
}

// Setup MutationObserver
var fb_container = document.querySelector('.cpv-fb-img');
if (fb_container) {
    var fb_observer = new MutationObserver(fb_onMediaItemsChange);
    fb_observer.observe(fb_container, {
        childList: true,
        subtree: false,
        attributes: true,
        attributeFilter: ['src'],
    });

    fb_onMediaItemsChange();
}
</script>
