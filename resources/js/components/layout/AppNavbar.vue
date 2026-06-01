<script setup>
import { ref, computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { useLocale } from '@/i18n'
import { useAuthStore } from '@/stores/auth'
import { useRouter } from 'vue-router'

const emit = defineEmits(['toggle'])
const { t } = useI18n()
const { locale, setLocale } = useLocale()
const authStore = useAuthStore()
const router = useRouter()
const langOpen = ref(false)
const profileOpen = ref(false)

function toggleLocale(lang) {
    setLocale(lang)
    langOpen.value = false
}

async function logout() {
    profileOpen.value = false
    await authStore.logout()
    router.push({ name: 'login' })
}

const initials = computed(() => {
    const name = authStore.user?.name || ''
    return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2)
})
</script>

<template>
    <header class="navbar">
        <button class="toggle-btn" @click="emit('toggle')">
            <i class="fa-solid fa-bars"></i>
        </button>
        <div class="nav-right">

            <!-- Language selector -->
            <div class="lang-selector">
                <button class="lang-btn" @click="langOpen = !langOpen">
                    <span class="lang-flag">{{ locale === 'en' ? '🇺🇸' : '🇲🇽' }}</span>
                    <span class="lang-code">{{ locale.toUpperCase() }}</span>
                    <i class="fa fa-chevron-down lang-arrow" :class="{ rotated: langOpen }"></i>
                </button>
                <div class="lang-dropdown" :class="{ open: langOpen }">
                    <button class="lang-option" :class="{ active: locale === 'en' }" @click="toggleLocale('en')">
                        <span>English (EN)</span><span>🇺🇸</span>
                    </button>
                    <button class="lang-option" :class="{ active: locale === 'es' }" @click="toggleLocale('es')">
                        <span>Español (ES)</span><span>🇲🇽</span>
                    </button>
                </div>
            </div>

            <div class="nav-divider"></div>

            <!-- Notifications -->
            <div class="nav-icon-btn" title="Notifications">
                <i class="fa fa-bell"></i>
                <span class="nav-badge">2</span>
            </div>

            <div class="nav-divider"></div>

            <!-- Profile dropdown -->
            <div class="profile-selector">
                <div class="nav-profile" @click="profileOpen = !profileOpen">
                    <div class="nav-avatar">{{ initials }}</div>
                    <div class="nav-profile-info">
                        <span class="nav-profile-name">{{ authStore.user?.name }}</span>
                        <span class="nav-profile-role">{{ authStore.user?.role ?? 'Admin' }}</span>
                    </div>
                    <i class="fa fa-chevron-down nav-chevron" :class="{ rotated: profileOpen }"></i>
                </div>

                <div class="profile-dropdown" :class="{ open: profileOpen }">
                    <!-- Info header -->
                    <div class="profile-dropdown__header">
                        <div class="profile-dropdown__avatar">{{ initials }}</div>
                        <div class="profile-dropdown__meta">
                            <span class="profile-dropdown__name">{{ authStore.user?.name }}</span>
                            <span class="profile-dropdown__email">{{ authStore.user?.email }}</span>
                        </div>
                    </div>

                    <div class="profile-dropdown__divider" />

                    <!-- Logout -->
                    <button class="profile-dropdown__logout" @click="logout">
                        <i class="fa-solid fa-right-from-bracket" />
                        <span>{{ $t('nav.logout') }}</span>
                    </button>
                </div>

                <!-- Overlay para cerrar al hacer click fuera -->
                <div v-if="profileOpen" class="profile-overlay" @click="profileOpen = false" />
            </div>

        </div>
    </header>
</template>

<style scoped>
.navbar {
    grid-area: navbar;
    background: rgb(2 41 36);
    border-bottom: 1px solid #1e2a3a;
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 0 8px;
    z-index: 100;
}

.toggle-btn {
    background: none;
    border: none;
    color: white;
    font-size: 1.2rem;
    cursor: pointer;
    padding: 0 1rem;
    height: 100%;
    display: flex;
    align-items: center;
}
.toggle-btn:hover { background: rgba(255,255,255,0.08); }

.nav-right {
    display: flex;
    align-items: center;
    gap: 2px;
    margin-left: auto;
}

/* ── Notifications ── */
.nav-icon-btn {
    position: relative;
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    cursor: pointer;
    transition: background 0.2s;
    color: #c9d4e8;
    font-size: 16px;
}
.nav-icon-btn:hover { background: #1e2a3a; }
.nav-badge {
    position: absolute;
    top: 2px;
    right: 2px;
    background: #e24b4a;
    color: white;
    font-size: 9px;
    font-weight: 700;
    border-radius: 20px;
    min-width: 14px;
    height: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0 3px;
    border: 1.5px solid #131c2e;
}

.nav-divider {
    width: 1px;
    height: 24px;
    background: #2a3a55;
    margin: 0 4px;
}

/* ── Language ── */
.lang-selector { position: relative; }
.lang-btn {
    display: flex;
    align-items: center;
    gap: 6px;
    background: #1e2a3a;
    border: 1px solid #2a3a55;
    border-radius: 8px;
    color: white;
    font-size: 13px;
    padding: 6px 10px;
    cursor: pointer;
    transition: background 0.2s;
}
.lang-btn:hover { background: #2a3a55; }
.lang-arrow { font-size: 10px; color: #7a8aaa; transition: transform 0.2s; }
.lang-arrow.rotated { transform: rotate(180deg); }
.lang-dropdown {
    position: absolute;
    top: calc(100% + 8px);
    right: 0;
    background: #111827;
    border: 1px solid #1e2a3a;
    border-radius: 10px;
    overflow: hidden;
    min-width: 170px;
    opacity: 0;
    pointer-events: none;
    transform: translateY(-6px);
    transition: opacity 0.2s ease, transform 0.2s ease;
    z-index: 999;
}
.lang-dropdown.open { opacity: 1; pointer-events: all; transform: translateY(0); }
.lang-option {
    display: flex;
    align-items: center;
    justify-content: space-between;
    width: 100%;
    padding: 10px 14px;
    color: #7a8aaa;
    font-size: 13px;
    cursor: pointer;
    transition: background 0.2s, color 0.2s;
    background: none;
    border: none;
    font-family: 'Segoe UI', sans-serif;
}
.lang-option:hover { background: #1e2a3a; color: white; }
.lang-option.active { color: white; }

/* ── Profile ── */
.profile-selector { position: relative; }

.nav-profile {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 0 8px 0 4px;
    height: 36px;
    border-radius: 8px;
    cursor: pointer;
    transition: background 0.2s;
}
.nav-profile:hover { background: #1e2a3a; }

.nav-avatar {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background: #4a90e2;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 11px;
    font-weight: 700;
    color: white;
    flex-shrink: 0;
}

.nav-profile-info {
    display: flex;
    flex-direction: column;
    line-height: 1.2;
}

.nav-profile-name {
    font-size: 13px;
    color: white;
    font-weight: 500;
    white-space: nowrap;
}

.nav-profile-role {
    font-size: 10px;
    color: #7a8aaa;
    text-transform: capitalize;
}

.nav-chevron {
    color: #7a8aaa;
    font-size: 11px;
    transition: transform 0.2s;
}
.nav-chevron.rotated { transform: rotate(180deg); }

/* ── Profile dropdown ── */
.profile-dropdown {
    position: absolute;
    top: calc(100% + 8px);
    right: 0;
    background: #111827;
    border: 1px solid #1e2a3a;
    border-radius: 12px;
    min-width: 220px;
    opacity: 0;
    pointer-events: none;
    transform: translateY(-6px);
    transition: opacity 0.2s ease, transform 0.2s ease;
    z-index: 1000;
    overflow: hidden;
}
.profile-dropdown.open {
    opacity: 1;
    pointer-events: all;
    transform: translateY(0);
}

.profile-dropdown__header {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 14px 16px;
}

.profile-dropdown__avatar {
    width: 38px;
    height: 38px;
    border-radius: 50%;
    background: #4a90e2;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 13px;
    font-weight: 700;
    color: white;
    flex-shrink: 0;
}

.profile-dropdown__meta {
    display: flex;
    flex-direction: column;
    gap: 2px;
    overflow: hidden;
}

.profile-dropdown__name {
    font-size: 13px;
    font-weight: 600;
    color: white;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.profile-dropdown__email {
    font-size: 11px;
    color: #7a8aaa;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.profile-dropdown__divider {
    height: 1px;
    background: #1e2a3a;
    margin: 0;
}

.profile-dropdown__logout {
    display: flex;
    align-items: center;
    gap: 10px;
    width: 100%;
    padding: 12px 16px;
    background: none;
    border: none;
    color: #f87171;
    font-size: 13px;
    font-family: 'Segoe UI', sans-serif;
    cursor: pointer;
    transition: background 0.2s, color 0.2s;
    text-align: left;
}
.profile-dropdown__logout:hover {
    background: rgba(248, 113, 113, 0.1);
    color: #fca5a5;
}
.profile-dropdown__logout i {
    font-size: 14px;
}

/* Overlay para cerrar */
.profile-overlay {
    position: fixed;
    inset: 0;
    z-index: 999;
}
</style>