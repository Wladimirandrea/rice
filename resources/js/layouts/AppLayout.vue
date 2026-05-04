<script setup>
import { ref } from 'vue'
import AppSidebar from '@/components/layout/AppSidebar.vue'
import AppNavbar from '@/components/layout/AppNavbar.vue'
import AppBottomNav from '@/components/layout/AppBottomNav.vue'

const collapsed = ref(false)
const navHidden = ref(false)

function handleToggle() {
    if (window.innerWidth < 768) {
        navHidden.value = !navHidden.value
    } else {
        collapsed.value = !collapsed.value
    }
}
</script>

<template>
    <div class="layout" :class="{ collapsed, 'nav-hidden': navHidden }">
        <AppNavbar @toggle="handleToggle" />
        <AppSidebar :collapsed="collapsed" />
        <main class="main-content">
            <RouterView />
        </main>
        <AppBottomNav />
    </div>
</template>

<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

/* ── MOBILE ── */
.layout {
    display: grid;
    height: 100dvh;
    overflow: hidden;
    grid-template-columns: 1fr;
    grid-template-rows: 60px 1fr auto;
    grid-template-areas:
        "navbar"
        "main-content"
        "bottom-nav";
}

.main-content {
    grid-area: main-content;
    min-height: 0;
    overflow-y: auto;
    overflow-x: hidden;
    background: linear-gradient(to bottom,
        #00152b 0%, #054894 35%, #10448b 70%, #00152b 100%);
}

.layout.nav-hidden {
    grid-template-rows: 60px 1fr 0px;
}

.layout.nav-hidden .bottom-nav {
    transform: translateY(100%);
    opacity: 0;
    pointer-events: none;
    overflow: hidden;
}

/* ── DESKTOP ── */
@media (min-width: 768px) {
    .layout {
        grid-template-columns: 260px 1fr !important;
        grid-template-rows: 60px 1fr !important;
        grid-template-areas:
            "sidebar navbar"
            "sidebar main-content" !important;
        transition: grid-template-columns 0.3s ease;
    }

    .layout.collapsed {
        grid-template-columns: 80px 1fr !important;
    }

    .layout.nav-hidden {
        grid-template-rows: 60px 1fr !important;
    }

    .bottom-nav {
        display: none !important;
    }

    .layout.collapsed .sidebar-profile-info,
    .layout.collapsed .sidebar-text,
    .layout.collapsed .sidebar-badge {
        opacity: 0;
        width: 0;
        overflow: hidden;
        pointer-events: none;
    }

    .layout.collapsed .sidebar-item:hover::after {
        content: attr(data-tooltip);
        position: absolute;
        left: calc(100% + 10px);
        top: 50%;
        transform: translateY(-50%);
        background: #4a90e2;
        color: white;
        font-size: 12px;
        padding: 5px 10px;
        border-radius: 6px;
        white-space: nowrap;
        pointer-events: none;
        z-index: 9999;
    }
}
</style>
