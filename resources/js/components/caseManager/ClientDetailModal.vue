<!-- ClientDetailModal.vue -->
<template>
    <Teleport to="body">

        <Transition name="modal-fade">
            <div v-if="modelValue" class="cd-backdrop" @click.self="$emit('update:modelValue', false)">
                <div class="cd-modal">

                    <!-- ── LEFT Card ── -->
                    <div class="cd-card">
                        <div class="cd-card__badge">
                            <img :src="logoBadge" alt="RAISE" />
                        </div>
                        <div class="cd-card__hero">
                            <div class="cd-card__curve" />
                            <div class="cd-card__avatar-ring">
                                <img :src="client?.profile_image || defaultAvatar" :alt="client?.name"
                                    class="cd-card__avatar" />
                            </div>
                        </div>
                        <div class="cd-card__info">
                            <h2 class="cd-card__name">
                                <span class="cd-card__name--regular">{{ firstName }}</span>
                                <span class="cd-card__name--accent"> {{ lastName }}</span>
                            </h2>
                            <p class="cd-card__position">Job <span>{{ $t('roles.client') }}</span></p>
                            <div class="cd-card__details">
                                <div class="cd-card__detail-row">
                                    <span class="cd-card__detail-label">Email :</span>
                                    <span class="cd-card__detail-value">{{ client?.email }}</span>
                                </div>
                            </div>
                            <div class="cd-card__barcode">
                                <svg viewBox="0 0 200 40" xmlns="http://www.w3.org/2000/svg">
                                    <g fill="#1a2a4a" opacity="0.7">
                                        <rect v-for="(bar, i) in barcode" :key="i" :x="bar.x" y="0" :width="bar.w"
                                            height="40" />
                                    </g>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- ── RIGHT Panel ── -->
                    <div class="cd-panel">
                        <button class="cd-panel__close" @click="$emit('update:modelValue', false)">✕</button>

                        <div class="cd-panel__avatar-wrap">
                            <div class="cd-panel__avatar-ring">
                                <img :src="client?.profile_image || defaultAvatar" :alt="client?.name" />
                            </div>
                            <h3 class="cd-panel__name">{{ client?.name }}</h3>
                        </div>

                        <div class="cd-panel__actions">
                            <button class="cd-action-btn cd-action-btn--reassign" :disabled="actionLoading"
                                @click="showReassignList = !showReassignList" :title="$t('caseManagers.reassign')">
                                <span class="cd-action-btn__icon">🔄</span>
                            </button>
                            <button class="cd-action-btn cd-action-btn--release" :disabled="actionLoading"
                                @click="$emit('release')" :title="$t('caseManagers.release')">
                                <span class="cd-action-btn__icon">✕</span>
                            </button>
                        </div>

                        <div class="cd-panel__cm-label">Case Manager</div>

                        <Transition name="slide-down">
                            <div v-if="showReassignList" class="cd-reassign-list">
                                <div v-for="manager in managers" :key="manager.id" class="cd-reassign-item"
                                    :class="{ 'cd-reassign-item--loading': actionLoading }"
                                    @click="$emit('reassign', manager.id)">
                                    <div class="cd-reassign-item__avatar">
                                        <img :src="manager.profile_image || defaultAvatar" :alt="manager.name" />
                                    </div>
                                    <span class="cd-reassign-item__name">{{ manager.name }}</span>
                                </div>
                                <div v-if="managers.length === 0" class="cd-reassign-empty">
                                    {{ $t('caseManagers.noManagers') }}
                                </div>
                            </div>
                        </Transition>

                        <div v-if="actionLoading" class="cd-panel__loading">
                            <div class="cd-spinner" />
                        </div>
                    </div>

                </div>
            </div>
        </Transition>

    </Teleport>
</template>

<script setup>
import { ref, computed } from 'vue'

const logoBadge = '/images/raise-logo.png'
const defaultAvatar = 'https://ui-avatars.com/api/?name=C&background=1565c0&color=fff'

const props = defineProps({
    modelValue: Boolean,
    client: { type: Object, default: null },
    managers: { type: Array, default: () => [] },
    actionLoading: Boolean,
})

defineEmits(['update:modelValue', 'reassign', 'release'])

const showReassignList = ref(false)

const nameParts = computed(() => props.client?.name?.trim().split(' ') ?? [])
const firstName = computed(() => nameParts.value[0] ?? '')
const lastName = computed(() => nameParts.value.slice(1).join(' ') || '')

const barcode = computed(() => {
    const bars = []; let x = 0
    for (let i = 0; i < 40; i++) {
        const w = Math.random() > 0.5 ? 3 : 2
        if (i % 2 === 0) bars.push({ x, w })
        x += w + (Math.random() > 0.6 ? 2 : 1)
    }
    return bars
})
</script>

<style scoped>
/* Backdrop */
.cd-backdrop {
    position: fixed;
    inset: 0;
    z-index: 1100;
    background: rgba(15, 23, 42, 0.75);
    backdrop-filter: blur(6px);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 16px;
}

/* Modal */
.cd-modal {
    display: flex;
    border-radius: 22px;
    overflow: hidden;
    width: 100%;
    max-width: 720px;
    max-height: 90vh;
    box-shadow: 0 32px 80px rgba(0, 0, 0, 0.45);
}

/* ── LEFT Card ── */
.cd-card {
    position: relative;
    width: 260px;
    min-width: 260px;
    background: linear-gradient(160deg, #1a2a4a 0%, #1565c0 50%, #e8edf5 50%);
    display: flex;
    flex-direction: column;
    align-items: center;
    overflow: hidden;
}

.cd-card__badge {
    position: absolute;
    top: 12px;
    left: 14px;
    z-index: 10;
    width: 58px;
    height: 58px;
}

.cd-card__badge img {
    width: 100%;
    height: 100%;
    object-fit: contain;
    filter: drop-shadow(0 2px 8px rgba(0, 0, 0, 0.5));
}

.cd-card__hero {
    position: relative;
    width: 100%;
    height: 190px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.cd-card__curve {
    position: absolute;
    right: -20px;
    top: 10px;
    width: 115px;
    height: 155px;
    background: #1565c0;
    border-radius: 50% 0 0 50%;
    z-index: 0;
}

.cd-card__avatar-ring {
    position: relative;
    z-index: 2;
    width: 130px;
    height: 130px;
    border-radius: 50%;
    background: conic-gradient(#1565c0 0deg, #42a5f5 120deg, #1565c0 360deg);
    padding: 4px;
    box-shadow: 0 4px 24px rgba(21, 101, 192, 0.5);
}

.cd-card__avatar {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid #fff;
    background: #fff;
}

.cd-card__info {
    flex: 1;
    width: 100%;
    background: #e8edf5;
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 14px 16px 18px;
}

.cd-card__name {
    font-size: 1.1rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    text-align: center;
    margin: 0 0 4px;
}

.cd-card__name--regular {
    color: #1a2a4a;
}

.cd-card__name--accent {
    color: #43a047;
}

.cd-card__position {
    font-size: 0.8rem;
    color: #546e7a;
    margin: 0 0 12px;
}

.cd-card__position span {
    color: #43a047;
    font-weight: 600;
}

.cd-card__details {
    width: 100%;
    margin-bottom: 12px;
}

.cd-card__detail-row {
    display: flex;
    gap: 6px;
    font-size: 0.76rem;
    justify-content: center;
    flex-wrap: wrap;
    text-align: center;
}

.cd-card__detail-label {
    color: #546e7a;
    font-weight: 600;
}

.cd-card__detail-value {
    color: #1a2a4a;
}

.cd-card__barcode {
    width: 85%;
    margin-top: auto;
}

.cd-card__barcode svg {
    width: 100%;
    height: 34px;
}

/* ── RIGHT Panel ── */
.cd-panel {
    position: relative;
    flex: 1;
    background: linear-gradient(135deg, #cfd8dc 0%, #b0bec5 100%);
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 20px 20px 16px;
    overflow-y: auto;
}

.cd-panel__close {
    position: absolute;
    top: 12px;
    right: 14px;
    background: rgba(255, 255, 255, 0.25);
    backdrop-filter: blur(8px);
    border: 1px solid rgba(255, 255, 255, 0.4);
    color: #1a2a4a;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    font-size: 0.85rem;
    cursor: pointer;
    transition: background 0.2s;
    z-index: 5;
}

.cd-panel__close:hover {
    background: rgba(255, 255, 255, 0.45);
}

/* Avatar grande */
.cd-panel__avatar-wrap {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 10px;
    margin-bottom: 16px;
    margin-top: 8px;
}

.cd-panel__avatar-ring {
    width: 140px;
    height: 140px;
    border-radius: 50%;
    background: conic-gradient(#c0c0c0 0deg, #e8e8e8 60deg, #a0a0a0 120deg,
            #d8d8d8 180deg, #c0c0c0 240deg, #e8e8e8 300deg, #c0c0c0 360deg);
    padding: 5px;
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3), inset 0 1px 3px rgba(255, 255, 255, 0.6);
}

.cd-panel__avatar-ring img {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid rgba(255, 255, 255, 0.85);
}

.cd-panel__name {
    font-size: 1rem;
    font-weight: 700;
    color: #1a2a4a;
    text-align: center;
}

/* Acciones */
.cd-panel__actions {
    display: flex;
    gap: 16px;
    margin-bottom: 10px;
}

.cd-action-btn {
    width: 48px;
    height: 48px;
    border-radius: 10px;
    border: none;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    cursor: pointer;
    transition: all 0.2s;
    background: rgba(255, 255, 255, 0.35);
    backdrop-filter: blur(8px);
    border: 1.5px solid rgba(255, 255, 255, 0.5);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
}

.cd-action-btn:hover:not(:disabled) {
    transform: scale(1.08);
    background: rgba(255, 255, 255, 0.55);
}

.cd-action-btn:disabled {
    opacity: 0.4;
    cursor: not-allowed;
}

.cd-action-btn--release {
    border-color: rgba(229, 57, 53, 0.4);
}

.cd-action-btn--release:hover:not(:disabled) {
    background: rgba(229, 57, 53, 0.15);
}

/* Label CM */
.cd-panel__cm-label {
    font-size: 0.75rem;
    font-weight: 800;
    letter-spacing: 0.1em;
    color: #37474f;
    text-transform: uppercase;
    border-top: 2px solid rgba(0, 0, 0, 0.15);
    border-bottom: 2px solid rgba(0, 0, 0, 0.15);
    padding: 6px 24px;
    margin-bottom: 14px;
    width: 100%;
    text-align: center;
    background: rgba(0, 0, 0, 0.06);
}

/* Lista reasignación */
.cd-reassign-list {
    width: 100%;
    background: rgba(255, 255, 255, 0.3);
    backdrop-filter: blur(12px);
    border-radius: 14px;
    border: 1px solid rgba(255, 255, 255, 0.5);
    padding: 10px;
    display: flex;
    flex-direction: column;
    gap: 8px;
    max-height: 220px;
    overflow-y: auto;
}

.cd-reassign-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 8px 12px;
    border-radius: 10px;
    background: rgba(255, 255, 255, 0.35);
    cursor: pointer;
    transition: background 0.15s;
}

.cd-reassign-item:hover {
    background: rgba(255, 255, 255, 0.6);
}

.cd-reassign-item--loading {
    pointer-events: none;
    opacity: 0.5;
}

.cd-reassign-item__avatar {
    width: 38px;
    height: 38px;
    border-radius: 50%;
    overflow: hidden;
    border: 2px solid #1565c0;
    flex-shrink: 0;
}

.cd-reassign-item__avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.cd-reassign-item__name {
    font-size: 0.85rem;
    font-weight: 600;
    color: #1a2a4a;
}

.cd-reassign-empty {
    text-align: center;
    color: #78909c;
    font-size: 0.82rem;
    padding: 12px 0;
}

/* Loading overlay */
.cd-panel__loading {
    position: absolute;
    inset: 0;
    background: rgba(255, 255, 255, 0.4);
    backdrop-filter: blur(4px);
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 0 22px 22px 0;
}

.cd-spinner {
    width: 40px;
    height: 40px;
    border: 3px solid rgba(255, 255, 255, 0.4);
    border-top-color: #1565c0;
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
}

@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

/* Transitions */
.modal-fade-enter-active,
.modal-fade-leave-active {
    transition: opacity 0.25s, transform 0.25s;
}

.modal-fade-enter-from,
.modal-fade-leave-to {
    opacity: 0;
    transform: scale(0.95);
}

.slide-down-enter-active,
.slide-down-leave-active {
    transition: opacity 0.2s, transform 0.2s;
}

.slide-down-enter-from,
.slide-down-leave-to {
    opacity: 0;
    transform: translateY(-10px);
}
</style>