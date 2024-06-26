(function (jtd, undefined) {

    // Event handling

    jtd.addEvent = function (el, type, handler) {
        if (el.attachEvent) el.attachEvent('on' + type, handler); else el.addEventListener(type, handler);
    }
    jtd.removeEvent = function (el, type, handler) {
        if (el.detachEvent) el.detachEvent('on' + type, handler); else el.removeEventListener(type, handler);
    }
    jtd.onReady = function (ready) {
        // in case the document is already rendered
        if (document.readyState != 'loading') ready();
        // modern browsers
        else if (document.addEventListener) document.addEventListener('DOMContentLoaded', ready);
        // IE <= 8
        else document.attachEvent('onreadystatechange', function () {
            if (document.readyState == 'complete') ready();
        });
    }

    // Show/hide mobile menu

    function initNav() {
        jtd.addEvent(document, 'click', function (e) {
            var target = e.target;
            while (target && !(target.classList && target.classList.contains('nav-list-expander'))) {
                target = target.parentNode;
            }
            if (target) {
                e.preventDefault();
                target.parentNode.classList.toggle('active');
            }
        });

        const siteNav = document.getElementById('site-nav');
        const mainHeader = document.getElementById('main-header');
        const menuButton = document.getElementById('menu-button');

        jtd.addEvent(menuButton, 'click', function (e) {
            e.preventDefault();

            if (menuButton.classList.toggle('nav-open')) {
                siteNav.classList.add('nav-open');
                mainHeader.classList.add('nav-open');
            } else {
                siteNav.classList.remove('nav-open');
                mainHeader.classList.remove('nav-open');
            }
        });
    }

    // Scroll site-nav to ensure the link to the current page is visible

    function scrollNav() {
        const href = document.location.pathname;
        const siteNav = document.getElementById('site-nav');
        const targetLink = siteNav.querySelector('a[href="' + href + '"], a[href="' + href + '/"]');
        if (targetLink) {
            const rect = targetLink.getBoundingClientRect();
            siteNav.scrollBy(0, rect.top - 3 * rect.height);
        }
    }

    // Document ready

    jtd.onReady(function () {
        initNav();
        scrollNav();
    });

})(window.jtd = window.jtd || {});

