<!-- resources/js/components/appointments/AppointmentDetailModal.vue -->
<template>
    <Teleport to="body">
        <Transition name="modal-fade">
            <div v-if="modelValue" class="ad-backdrop" @click.self="$emit('update:modelValue', false)">
                <div class="ad-modal">
                    <div class="ad-header" :class="`ad-header--${appointment?.status}`">
                        <div class="ad-header__info">
                            <span class="ad-header__status">{{ $t(`appointments.${appointment?.status}`) }}</span>
                            <span class="ad-header__time">{{ appointment?.start_time }} — {{ appointment?.end_time }}</span>
                        </div>
                        <button class="ad-close" @click="$emit('update:modelValue', false)">✕</button>
                    </div>

                    <div class="ad-body">
                        <!-- Cliente -->
                        <div class="ad-person">
                            <img :src="appointment?.client?.profile_image || defaultAvatar" class="ad-person__avatar" />
                            <div class="ad-person__info">
                                <span class="ad-person__label">{{ $t('appointments.client') }}</span>
                                <span class="ad-person__name">{{ appointment?.client?.name }}</span>
                            </div>
                        </div>

                        <!-- Case Manager -->
                        <div class="ad-person">
                            <img :src="appointment?.case_manager?.profile_image || defaultAvatar" class="ad-person__avatar" />
                            <div class="ad-person__info">
                                <span class="ad-person__label">Case Manager</span>
                                <span class="ad-person__name">{{ appointment?.case_manager?.name }}</span>
                            </div>
                        </div>

                        <!-- Notas -->
                        <div v-if="appointment?.notes" class="ad-notes">
                            <span class="ad-notes__label">{{ $t('appointments.notes') }}</span>
                            <p class="ad-notes__text">{{ appointment.notes }}</p>
                        </div>

                        <!-- Cambiar status -->
                        <div class="ad-status-btns">
                            <span class="ad-status__label">{{ $t('appointments.changeStatus') }}</span>
                            <div class="ad-status-row">
                                <button
                                    v-for="s in statuses"
                                    :key="s"
                                    class="ad-status-btn"
                                    :class="[`ad-status-btn--${s}`, { 'ad-status-btn--active': appointment?.status === s }]"
                                    :disabled="appointment?.status === s || saving"
                                    @click="changeStatus(s)"
                                >
                                    {{ $t(`appointments.${s}`) }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<script setup>
import { ref } from 'vue'
import { useAppointmentStore } from '@/stores/appointmentStore'

const store = useAppointmentStore()
const defaultAvatar = 'https://ui-avatars.com/api/?name=C&background=1565c0&color=fff'
const saving  = ref(false)
const statuses = ['pending', 'confirmed', 'completed', 'cancelled']

const props = defineProps({
    modelValue:  Boolean,
    appointment: { type: Object, default: null },
})
const emit = defineEmits(['update:modelValue', 'status-changed'])

async function changeStatus(status) {
    saving.value = true
    await store.updateStatus(props.appointment.id, status)
    saving.value = false
    emit('status-changed')
}
</script>

<style>
.ad-backdrop {
    position: fixed; inset: 0; z-index: 1200;
    background: rgba(15,23,42,0.75);
    backdrop-filter: blur(6px);
    display: flex; align-items: center; justify-content: center;
    padding: 16px;
}
.ad-modal {
    background: #1a2a4a;
    border-radius: 20px;
    width: 100%; max-width: 420px;
    box-shadow: 0 24px 64px rgba(0,0,0,0.5);
    border: 1px solid rgba(255,255,255,0.1);
    overflow: hidden;
}
.ad-header {
    padding: 18px 22px;
    display: flex; align-items: center; justify-content: space-between;
}
.ad-header--pending   { background: rgba(245,158,11,0.25); border-bottom: 2px solid #f59e0b; }
.ad-header--confirmed { background: rgba(59,130,246,0.25); border-bottom: 2px solid #3b82f6; }
.ad-header--completed { background: rgba(34,197,94,0.25);  border-bottom: 2px solid #22c55e; }
.ad-header--cancelled { background: rgba(239,68,68,0.25);  border-bottom: 2px solid #ef4444; }
.ad-header__info { display: flex; flex-direction: column; gap: 2px; }
.ad-header__status { font-size: 0.75rem; font-weight: 700; color: rgba(255,255,255,0.7); text-transform: uppercase; letter-spacing: 0.06em; }
.ad-header__time   { font-size: 1rem; font-weight: 700; color: #fff; }
.ad-close {
    background: rgba(255,255,255,0.1); border: none; color: #fff;
    width: 30px; height: 30px; border-radius: 50%; cursor: pointer; font-size: 0.85rem;
}
.ad-body { padding: 20px 22px; display: flex; flex-direction: column; gap: 16px; }
.ad-person {
    display: flex; align-items: center; gap: 14px;
    padding: 12px; border-radius: 12px;
    background: rgba(255,255,255,0.05);
    border: 1px solid rgba(255,255,255,0.08);
}
.ad-person__avatar { width: 44px; height: 44px; border-radius: 50%; object-fit: cover; border: 2px solid rgba(255,255,255,0.2); }
.ad-person__info { display: flex; flex-direction: column; gap: 2px; }
.ad-person__label { font-size: 0.7rem; color: rgba(255,255,255,0.5); text-transform: uppercase; letter-spacing: 0.04em; }
.ad-person__name  { font-size: 0.95rem; font-weight: 700; color: #fff; }
.ad-notes { background: rgba(255,255,255,0.05); border-radius: 12px; padding: 12px; border: 1px solid rgba(255,255,255,0.08); }
.ad-notes__label { font-size: 0.7rem; color: rgba(255,255,255,0.5); text-transform: uppercase; display: block; margin-bottom: 6px; }
.ad-notes__text  { margin: 0; font-size: 0.9rem; color: rgba(255,255,255,0.8); line-height: 1.5; }
.ad-status-btns  { display: flex; flex-direction: column; gap: 8px; }
.ad-status__label { font-size: 0.7rem; color: rgba(255,255,255,0.5); text-transform: uppercase; letter-spacing: 0.04em; }
.ad-status-row   { display: flex; gap: 8px; flex-wrap: wrap; }
.ad-status-btn {
    flex: 1; padding: 8px 4px; border-radius: 8px; border: none;
    font-size: 0.78rem; font-weight: 700; cursor: pointer;
    opacity: 0.5; transition: opacity 0.2s, transform 0.15s;
    min-width: 80px;
}
.ad-status-btn--active  { opacity: 1; }
.ad-status-btn:hover:not(:disabled):not(.ad-status-btn--active) { opacity: 0.8; transform: scale(1.03); }
.ad-status-btn:disabled { cursor: not-allowed; }
.ad-status-btn--pending   { background: #f59e0b; color: #fff; }
.ad-status-btn--confirmed { background: #3b82f6; color: #fff; }
.ad-status-btn--completed { background: #22c55e; color: #fff; }
.ad-status-btn--cancelled { background: #ef4444; color: #fff; }
</style>