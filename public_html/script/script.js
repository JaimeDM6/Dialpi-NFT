/* HEADER */

var dropdown = document.querySelector('.dropdown');
if (dropdown) {
    var dropdownMenu = dropdown.querySelector('.dropdown-menu');
    var dropdownIcon = dropdown.querySelector('i');

    dropdown.addEventListener('click', function(e) {
        e.stopPropagation();
        if (dropdownMenu.style.display === '' || dropdownMenu.style.display === 'none') {
            dropdownMenu.style.display = 'block';
            dropdownMenu.style.animation = 'fadeIn 1s forwards';
            dropdownIcon.classList.add('rotate-icon');
        } else {
            dropdownMenu.style.animation = 'fadeOut 1s forwards';
            dropdownIcon.classList.remove('rotate-icon');
            setTimeout(function() {
                dropdownMenu.style.display = 'none';
            }, 1000);
        }
    });

    document.addEventListener('click', function(e) {
        if (!dropdown.contains(e.target)) {
            dropdownMenu.style.animation = 'fadeOut 1s forwards';
            dropdownIcon.classList.remove('rotate-icon');
            setTimeout(function() {
                dropdownMenu.style.display = 'none';
            }, 1000);
        }
    });
}

var dropdownButton = document.querySelector('.dropdown-menu-button');
var containerHeaderMovil = document.querySelector('.container-header-movil');
var dropdownMenuMobile = document.querySelector('.dropdown-menu-mobile');

window.addEventListener('DOMContentLoaded', (event) => {
    var containerHeaderMovil = document.querySelector('.container-header-movil');
    window.requestAnimationFrame(function() {
        containerHeaderMovil.style.height = '4.8em';
    });
});

var dropdownMobile = document.querySelector('.dropdown-mobile');
var dropdownToggle = document.querySelector('.dropdown-toggle');
var dropdownMenuMobile2 = document.querySelector('.dropdown-menu-mobile-2');
if (dropdownToggle) {
    var dropdownIcon2 = dropdownToggle.querySelector('i');
}

dropdownButton.addEventListener('click', function() {
    if (containerHeaderMovil.style.height === '4.8em') {
        containerHeaderMovil.style.height = '18.75em';
        setTimeout(function() {
            dropdownMenuMobile.style.display = 'flex';
            setTimeout(function() {
                dropdownMenuMobile.style.opacity = '1';
            }, 100);
        }, 100);
    } else {
        if (dropdownMenuMobile2) {
            if (dropdownMenuMobile2.style.display === 'flex') {
                dropdownIcon2.classList.remove('rotate-icon');
                dropdownMenuMobile2.style.animation = 'fadeOut 0.5s forwards';
                setTimeout(function() {
                    dropdownMenuMobile2.style.display = 'none';
                    containerHeaderMovil.style.height = '18.75em';
                    dropdownMobile.style.height = '1.25em';
                    dropdownMenuMobile.style.opacity = '0';
                    setTimeout(function() {
                        dropdownMenuMobile.style.display = 'none';
                        containerHeaderMovil.style.height = '4.8em';
                    }, 100);
                }, 100);
            } else {
                dropdownMenuMobile.style.opacity = '0';
                setTimeout(function() {
                    dropdownMenuMobile.style.display = 'none';
                    containerHeaderMovil.style.height = '4.8em';
                }, 100);
            }
        } else {
            dropdownMenuMobile.style.opacity = '0';
            setTimeout(function() {
                dropdownMenuMobile.style.display = 'none';
                containerHeaderMovil.style.height = '4.8em';
            }, 100);
        }
    }
});

if (dropdownToggle) {
    dropdownToggle.addEventListener('click', function(e) {
        e.preventDefault();
        $.ajax({
            url: '/get-session',
            method: 'POST',
            success: function(data) {
                var isAdmin = data.administrador;
                if (dropdownMenuMobile2.style.display === 'none' || dropdownMenuMobile2.style.display === '') {
                    dropdownIcon2.classList.add('rotate-icon');
                    if (isAdmin) {
                        containerHeaderMovil.style.height = '29em';
                        dropdownMobile.style.height = '12em';
                    } else {
                        containerHeaderMovil.style.height = '26em';
                        dropdownMobile.style.height = '9.375em';
                    }
                    setTimeout(function() {
                        dropdownMenuMobile2.style.display = 'flex';
                        dropdownMenuMobile2.style.animation = 'fadeIn 1s forwards';
                    }, 300);
                } else {
                    dropdownIcon2.classList.remove('rotate-icon');
                    dropdownMenuMobile2.style.animation = 'fadeOut 1s forwards';
                    setTimeout(function() {
                        dropdownMenuMobile2.style.display = 'none';
                        containerHeaderMovil.style.height = '18.75em';
                        dropdownMobile.style.height = '1.25em';
                    }, 100);
                }
            }
        });
    });
}

var searchIcon = document.querySelector('.search-icon');
var searchForm = document.querySelector('.search-form');
var navMenu = document.querySelector('nav');
var containerHeader = document.querySelector('.container-header');

searchIcon.addEventListener('click', function() {
    searchIcon.style.animation = 'fadeOut 0.5s forwards';
    navMenu.style.animation = 'fadeOut 0.5s forwards';
    setTimeout(function() {
        searchIcon.style.display = 'none';
        navMenu.style.display = 'none';
        searchForm.style.display = 'flex';
        searchForm.style.setProperty('justify-content', 'center');
        searchForm.style.animation = 'slideInRight 0.5s forwards';
    }, 500);
});

containerHeader.addEventListener('click', function() {
    if (window.getComputedStyle(navMenu).display === 'none') {
        searchForm.style.animation = 'slideOutRight 0.5s forwards';
        setTimeout(function() {
            searchForm.style.display = 'none';
            searchIcon.style.display = 'inline-block';
            navMenu.style.display = 'inline-block';
            searchIcon.style.animation = 'fadeIn 0.5s forwards';
            navMenu.style.animation = 'fadeIn 0.5s forwards';
        }, 500);
    }
});

window.addEventListener('resize', function() {
    var width = window.innerWidth;

    if (width > 1331 && width < 1367 || width > 1599) {
        searchForm.style.display = 'block';
        searchIcon.style.display = 'none';
        navMenu.style.display = 'block';
        navMenu.style.animation = 'none';
        searchForm.style.animation = 'none';
    }

    if (width > 972 && width < 1332 || width > 1366 && width < 1600) {
        searchForm.style.display = 'none';
        searchIcon.style.display = 'inline-block';
        navMenu.style.display = 'block';
        searchIcon.style.animation = 'none';
    }
});

document.addEventListener('DOMContentLoaded', function() {
    var cartIcon = document.getElementById('cart-icon');
    var dropdown = document.querySelector('.cart-dropdown');

    if (cartIcon) {
        cartIcon.addEventListener('mouseover', function() {
            if (dropdown) {
                dropdown.classList.add('show');
            }
        });

        cartIcon.addEventListener('mouseout', function() {
            if (dropdown) {
                dropdown.classList.remove('show');
            }
        });
    }
});
