/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

import './bootstrap';
// import { Livewire, Alpine } from '../../vendor/livewire/livewire/dist/livewire.esm';
// import Clipboard from '@ryangjchandler/alpine-clipboard';
//
// Alpine.plugin(Clipboard)
//
// Livewire.start()

import {livewire_hot_reload} from 'virtual:livewire-hot-reload'

livewire_hot_reload();
document.addEventListener("DOMContentLoaded", () =>{
    // Select the button
    const backToTopBtn = document.getElementById("backToTop");

    // Show or hide the button based on scroll position
    window.addEventListener("scroll", () => {
        if (window.scrollY > 300) {
            backToTopBtn.style.display = "block";
        } else {
            backToTopBtn.style.display = "none";
        }
    });

    // Scroll back to the top when the button is clicked
    backToTopBtn.addEventListener("click", () => {
        window.scrollTo({
            top: 0,
            behavior: "smooth" // Smooth scrolling
        });
    });
});
document.addEventListener("DOMContentLoaded", () =>{
    const responsiveDiv = document.getElementById("sidebarOffcanvas");
    function manageOffcanvasClass(){
        if (window.innerWidth > 768) {
            // Remove 'offcanvas' class if screen width > 768px
            responsiveDiv?.classList.remove("offcanvas");
        } else {
            // Add 'offcanvas' class if screen width <= 768px
            responsiveDiv?.classList.add("offcanvas");
        }
    }
    // Check the size on page load
    window.addEventListener("load", manageOffcanvasClass);

    // Also check on window resize to handle dynamic changes
    window.addEventListener("resize", manageOffcanvasClass);
});
