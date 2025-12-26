<div class="border border-gray-400 rounded bg-white">
    
    <div class="d-flex pf-13">
        
        <div class="d-flex align-items-center gap-8">
            <div class="size-40 size-child">
                <img src="{{ theme_public_asset( "img/default.png" ) }}" class="align-self-center rounded-circle border cpv-avatar" alt="">
            </div>
            <div class="d-flex align-items-center flex-row-fluid flex-wrap">
                <div class="flex-grow-1 me-2 text-truncate">
                    <a href="javascript:void(0);" class="text-gray-900 text-hover-primary fs-14 fw-bold cpv-name">{{ __("Your name") }}</a>
                    <span class="text-muted fw-semibold d-block fs-12">{{ date("M j") }} <span class="position-relative t--3 fw-9 me-1">.</span><i class="fa-solid fa-earth-americas"></i></span> 
                </div>
            </div>
        </div>

    </div>

    <div class="mb-0">
        
        <div class="cpv-text fs-14 px-3 mb-3 text-truncate-5"></div>

        <div class="cpv-media">
            <div class="cpv-img w-100 cpv-linkedin-img d-none"></div>
            <div class="cpv-linkedin-img-view w-100">
                <img src="{{ theme_public_asset( "img/default.png" ) }}" class="w-100">
            </div>
        </div>

        <div class="cpv-link d-flex justify-content-start d-none border b-r-10 m-3">
            <div class="cpv-link-img img-wrap w-100 size-120 size-child b-r-10 m-3 img-wrap">
                <img src="{{ theme_public_asset( "img/default.png" ) }}" class="w-100 b-r-10">
            </div>
            <div class="d-flex flex-column justify-content-center w-100 fs-12 py-13 pe-13">
                <div class="cpv-default">
                    <div class="h-12 bg-gray-300 mb-2"></div>
                    <div class="h-12 bg-gray-300 mb-2"></div>
                    <div class="h-12 bg-gray-300 mb-1"></div>
                    <div class="h-12 bg-gray-300 mb-1 wp-50"></div>
                </div>
                <div class="cpv-link-title fw-6 text-truncate-1"></div>
                <div class="cpv-link-web fs-10 fw-3 text-truncate-1"></div>
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
                    <i class="fal fa-comment-alt-lines"></i>
                </div>
                <div class="d-flex align-items-center flex-row-fluid flex-wrap">
                    <div class="flex-grow-1 me-2 text-truncate">
                        <span class="text-gray-800 fs-12 fw-bold">{{ __("Comment") }}</span>
                    </div>
                </div>
            </div>
            <div class="d-flex flex-stack">
                <div class="symbol symbol-45px me-2">
                    <i class="fal fa-retweet"></i>
                </div>
                <div class="d-flex align-items-center flex-row-fluid flex-wrap">
                    <div class="flex-grow-1 me-2 text-truncate">
                        <span class="text-gray-800 fs-12 fw-bold">{{ __("Comment") }}</span>
                    </div>
                </div>
            </div>
            <div class="d-flex flex-stack">
                <div class="symbol symbol-45px me-2">
                    <i class="fas fa-paper-plane"></i>
                </div>
                <div class="d-flex align-items-center flex-row-fluid flex-wrap">
                    <div class="flex-grow-1 me-2 text-truncate">
                        <span class="text-gray-800 fs-12 fw-bold">{{ __("Send") }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
function linkedin_renderMediaGrid(elements) {
    var linkedin_total = elements.length;
    var linkedin_visible = elements.slice(0, 4);
    var linkedin_moreCount = linkedin_total - 4;

    let linkedin_html = '';

    if (linkedin_total === 1) {
        linkedin_html += `
            <div class="cpv-grid" style="grid-template-columns: 1fr;">
                <div class="img-wrap">${elements[0].outerHTML}</div>
            </div>
        `;
    } else if (linkedin_total === 2) {
        linkedin_html += `
            <div class="cpv-grid" style="grid-template-columns: repeat(2, 1fr);">
                ${linkedin_visible.map(el => `<div class="img-wrap">${el.outerHTML}</div>`).join('')}
            </div>
        `;
    } else if (linkedin_total === 3) {
        linkedin_html += `
            <div class="cpv-grid" style="grid-template-columns: 2fr 1fr; grid-template-rows: repeat(2, 1fr);">
                <div class="img-wrap" style="grid-row: span 2;">${elements[0].outerHTML}</div>
                <div class="img-wrap">${elements[1].outerHTML}</div>
                <div class="img-wrap">${elements[2].outerHTML}</div>
            </div>
        `;
    } else {
        linkedin_html += `<div class="cpv-grid" style="grid-template-columns: repeat(2, 1fr);">`;
        linkedin_visible.forEach((el, idx) => {
            var linkedin_isLast = idx === 3 && linkedin_moreCount > 0;
            var linkedin_overlay = linkedin_isLast ? `<div class="overlay">+${linkedin_moreCount}</div>` : '';
            linkedin_html += `<div class="img-wrap">${el.outerHTML}${linkedin_overlay}</div>`;
        });
        linkedin_html += `</div>`;
    }

    return linkedin_html;
}

function linkedin_onMediaItemsChange() {
    var linkedin_elements = document.querySelectorAll('.cpv-linkedin-img > img, .cpv-linkedin-img > div');
    if (linkedin_elements.length > 0) {
        var linkedin_mediaList = Array.from(linkedin_elements).filter(el =>
            el.tagName.toLowerCase() === 'img' || el.tagName.toLowerCase() === 'div'
        );

        var linkedin_rendered = linkedin_renderMediaGrid(linkedin_mediaList);
        document.querySelector('.cpv-linkedin-img-view').innerHTML = linkedin_rendered;
    }
}

// Setup MutationObserver
var linkedin_container = document.querySelector('.cpv-linkedin-img');
if (linkedin_container) {
    var linkedin_observer = new MutationObserver(linkedin_onMediaItemsChange);
    linkedin_observer.observe(linkedin_container, {
        childList: true,
        subtree: false,
        attributes: true,
        attributeFilter: ['src'],
    });

    linkedin_onMediaItemsChange();
}
</script>
