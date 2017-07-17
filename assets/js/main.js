"use strict";
! function() {
    function e(e) {
        for (r = 0; r < n.length; r++) n[r].classList.remove("popover-open")
    }

    function t(t) {
        t.preventDefault(), document.querySelector(this.getAttribute("href")).classList.contains("popover-open") ? document.querySelector(this.getAttribute("href")).classList.remove("popover-open") : (e(), document.querySelector(this.getAttribute("href")).classList.add("popover-open")), t.stopImmediatePropagation()
    }
    var o = document.querySelectorAll("[data-popover]"),
        n = document.querySelectorAll(".popover"),
        r = void 0;
    for (r = 0; r < o.length; r++) o[r].addEventListener("click", t);
    document.addEventListener("click", e)
}();
