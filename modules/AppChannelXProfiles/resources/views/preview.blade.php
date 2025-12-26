<div class="border border-gray-400 rounded bg-white">
    
    <div class="pf-13">
        
        <div class="d-flex gap-8">
            <div class="size-40 size-child">
                <img src="{{ theme_public_asset( "img/default.png" ) }}" class="align-self-center rounded-circle border cpv-avatar" alt="">
            </div>
            <div class="d-flex flex-column wp-100">
                <div class="d-flex align-items-center gap-8">
                    <span class="text-gray-900 text-hover-primary fs-14 fw-bold cpv-name"></span>
                    <span class="text-gray-600 fw-5 d-block fs-12">@<span class="cpv-username">{{ __("username") }}</span></span>
                    <span class="text-gray-600 fs-10 d-flex align-items-center m-t--7"><i class="fa-solid fa-period"></i></span>
                    <span class="text-gray-600 fw-semibold d-block fs-12">{{ date("M j") }}</span>
                </div>
                <div class="mt-2 mb-2">
                    <div class="cpv-text fs-14 mb-3 text-truncate-5"></div>

                    <div class="cpv-media">
                        <div class="cpv-img w-100 cpv-x-img d-none"></div>
                        <div class="cpv-x-img-view w-100">
                            <img src="{{ theme_public_asset( "img/default.png" ) }}" class="w-100">
                        </div>
                    </div>

                    <div class="cpv-link d-flex justify-content-start w-100 d-none">
                        <div class="cpv-link-img img-wrap w-100 size-120 size-child border-end btl-r-10 bbl-r-10 border-start border-top border-bottom">
                            <img src="{{ theme_public_asset( "img/default.png" ) }}" class="w-100">
                        </div>
                        <div class="d-flex flex-column justify-content-center w-100 bg-gray-100 fs-12 pf-13 btr-r-10 bbr-r-10 border-end border-top border-bottom">
                            <div class="cpv-default">
                                <div class="h-12 bg-gray-300 mb-2"></div>
                                <div class="h-12 bg-gray-300 mb-2"></div>
                                <div class="h-12 bg-gray-300 mb-1"></div>
                                <div class="h-12 bg-gray-300 mb-1 wp-50"></div>
                            </div>
                            <div class="cpv-link-web fs-10 fw-3 text-truncate-1">
                                
                            </div>
                            <div class="cpv-link-title fw-6 text-truncate-1">
                                
                            </div>
                            <div class="cpv-link-desc text-gray-700 text-truncate-2">
                                
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-start gap-64 text-gray-600">
                    <div class="d-flex flex-stack">
                        <div class="symbol symbol-45px me-2">
                            <i class="fal fa-comment"></i>
                        </div>
                    </div>
                    <div class="d-flex flex-stack">
                        <div class="symbol symbol-45px me-2">
                            <i class="fal fa-retweet"></i>
                        </div>
                    </div>
                    <div class="d-flex flex-stack">
                        <div class="symbol symbol-45px me-2">
                            <i class="fal fa-heart"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function renderXMediaGrid(elements) {
    var x_total = elements.length;
    var x_visible = elements.slice(0, 4);
    var x_more = x_total - 4;

    let x_html = '';

    if (x_total === 1) {
        x_html += `
            <div class="cpv-grid" style="grid-template-columns: 1fr;">
                <div class="img-wrap b-r-10" style="aspect-ratio: 16 / 9;">
                    ${elements[0].outerHTML}
                </div>
            </div>
        `;
    } else if (x_total === 2) {
        x_html += `
            <div class="cpv-grid" style="grid-template-columns: repeat(2, 1fr);">
                <div class="img-wrap btl-r-10 bbl-r-10">${elements[0].outerHTML}</div>
                <div class="img-wrap btr-r-10 bbr-r-10">${elements[1].outerHTML}</div>
            </div>
        `;
    } else if (x_total === 3) {
        x_html += `
            <div class="cpv-grid" style="grid-template-columns: 2fr 1fr; grid-template-rows: repeat(2, 1fr);">
                <div class="img-wrap btl-r-10 bbl-r-10" style="grid-row: span 2;">${elements[0].outerHTML}</div>
                <div class="img-wrap btr-r-10">${elements[1].outerHTML}</div>
                <div class="img-wrap bbr-r-10">${elements[2].outerHTML}</div>
            </div>
        `;
    } else {
        x_html += `<div class="cpv-grid" style="grid-template-columns: repeat(2, 1fr);">`;

        x_visible.forEach((el, idx) => {
            var isLast = idx === 3 && x_more > 0;
            var overlay = isLast ? `<div class="overlay">+${x_more}</div>` : '';

            let radiusClass = '';
            if (idx === 0) radiusClass = 'btl-r-10';
            else if (idx === 1) radiusClass = 'btr-r-10';
            else if (idx === 2) radiusClass = 'bbl-r-10';
            else if (idx === 3) radiusClass = 'bbr-r-10';

            x_html += `<div class="img-wrap ${radiusClass}">${el.outerHTML}${overlay}</div>`;
        });

        x_html += `</div>`;
    }

    return x_html;
}

function onXMediaItemsChange() {
    var x_elements = document.querySelectorAll('.cpv-x-img > img, .cpv-x-img > div');
    if (x_elements.length > 0) {
        var x_mediaList = Array.from(x_elements).filter(el =>
            el.tagName.toLowerCase() === 'img' || el.tagName.toLowerCase() === 'div'
        );

        var x_rendered = renderXMediaGrid(x_mediaList);
        document.querySelector('.cpv-x-img-view').innerHTML = x_rendered;
    }
}

var x_container = document.querySelector('.cpv-x-img');
if (x_container) {
    var x_observer = new MutationObserver(onXMediaItemsChange);
    x_observer.observe(x_container, {
        childList: true,
        subtree: false,
        attributes: true,
        attributeFilter: ['src'],
    });

    onXMediaItemsChange();
}
</script>