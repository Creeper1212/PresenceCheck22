<?php
// footer.php

echo '
<style>
    /* Target styles specifically to the footer with the ID */
    #my-unique-footer {
        background-color: #f0f0f0;
        padding: 1rem;
        text-align: center;
        font-size: 0.9rem;
        color: #333;
        border-top: 1px solid #ddd;
        width: 100%; /* Ensure footer spans full width */
    }

    /*  Loader styles */
    #loader-wrapper {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(255, 255, 255, 0.8);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 1000;
        transition: opacity 0.3s ease;
        opacity: 1;
        pointer-events: auto;
    }

    #loader-wrapper.hidden {
        opacity: 0;
        pointer-events: none;
    }

    .loader {
        border: 8px solid #f3f3f3;
        border-top: 8px solid #3498db;
        border-radius: 50%;
        width: 60px;
        height: 60px;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
</style>

<footer id="my-unique-footer">
    <small>
        © <span id="copyright-year"></span> Presence. All Rights Reserved.
    </small>
</footer>

<div id="loader-wrapper">
    <div class="loader"></div>
</div>

<script>
    document.getElementById("copyright-year").textContent = new Date().getFullYear();

    document.addEventListener("DOMContentLoaded", () => {
        const loader = document.getElementById("loader-wrapper");
        if (loader) {
            loader.classList.add("hidden");
            loader.addEventListener("transitionend", () => {
                loader.remove();
            });
        }
    });
</script>
';
?>