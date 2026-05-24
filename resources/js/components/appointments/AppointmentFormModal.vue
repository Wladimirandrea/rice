<!-- resources/js/components/appointments/AppointmentFormModal.vue -->
<template>
    <Teleport to="body">
        <Transition name="modal-fade">
            <div v-if="modelValue" class="af-backdrop" @click.self="$emit('update:modelValue', false)">
                <div class="af-modal">
                    <div class="af-header">
                        <h3 class="af-title">{{ $t('appointments.new') }}</h3>
                        <button class="af-close" @click="$emit('update:modelValue', false)">✕</button>
                    </div>

                    <div class="af-body">
                        <!-- Cliente -->
                        <div class="af-field">
                            <label class="af-label">{{ $t('appointments.client') }}</label>
                            <select v-model="form.client_id" class="af-select">
                                <option value="">{{ $t('appointments.selectClient') }}</option>
                                <option v-for="c in clients" :key="c.id" :value="c.id">
                                    {{ c.name }}
                                </option>
                            </select>
                        </div>

                        <!-- Case Manager -->
                        <div class="af-field">
                            <label class="af-label">{{ $t('appointments.caseManager') }}</label>
                            <select v-model="form.case_manager_id" class="af-select">
                                <option value="">{{ $t('appointments.selectCM') }}</option>
                                <option v-for="cm in caseManagers" :key="cm.id" :value="cm.id">
                                    {{ cm.name }}
                                </option>
                            </select>
                        </div>

                        <!-- Fecha -->
                        <div class="af-field">
                            <label class="af-label">{{ $t('appointments.date') }}</label>
                            <input v-model="form.date" type="date" class="af-input" />
                        </div>

                        <!-- Hora inicio — slots del schedule -->
                        <div class="af-field">
                            <label class="af-label">{{ $t('appointments.startTime') }}</label>
                            <select v-model="form.start_time" class="af-select">
                                <option v-for="slot in availableTimeSlots" :key="slot.time" :value="slot.time"
                                    :disabled="!slot.available" :class="{ 'af-option--taken': !slot.available }">
                                    {{ slot.time }} {{ !slot.available ? '— ' + $t('appointments.taken') : '' }}
                                </option>
                            </select>
                        </div>

                        <!-- Status -->
                        <div class="af-field">
                            <label class="af-label">{{ $t('appointments.status') }}</label>
                            <select v-model="form.status" class="af-select">
                                <option value="pending">{{ $t('appointments.pending') }}</option>
                                <option value="confirmed">{{ $t('appointments.confirmed') }}</option>
                            </select>
                        </div>

                        <!-- Notas -->
                        <div class="af-field">
                            <label class="af-label">{{ $t('appointments.notes') }}</label>
                            <textarea v-model="form.notes" class="af-textarea" rows="3" />
                        </div>

                        <!-- Error -->
                        <p v-if="errorMsg" class="af-error">{{ errorMsg }}</p>
                    </div>

                    <div class="af-footer">
                        <button class="af-btn af-btn--cancel" @click="$emit('update:modelValue', false)">
                            {{ $t('common.cancel') }}
                        </button>
                        <button class="af-btn af-btn--save" :disabled="saving" @click="save">
                            <span v-if="saving" class="af-spinner" />
                            <span v-else>{{ $t('common.save') }}</span>
                        </button>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'  // ← agregar watch
import { useI18n } from 'vue-i18n'
import { useAppointmentStore } from '@/stores/appointmentStore'
import api from '@/plugins/axios'

const { t } = useI18n()
const store = useAppointmentStore()

const props = defineProps({
    modelValue: Boolean,
    date: { type: String, default: '' },
})
const emit = defineEmits(['update:modelValue', 'created'])

const clients = ref([])
const caseManagers = ref([])
const saving = ref(false)
const errorMsg = ref('')

const form = ref({
    client_id: '',
    case_manager_id: '',
    date: props.date,
    start_time: '',
    status: 'pending',
    notes: '',
})

// ← Watch: cuando cambia date o case_manager_id, cargar slots
watch(
    () => [form.value.date, form.value.case_manager_id],
    ([date, managerId]) => {
        if (date && managerId) {
            store.fetchSlots(date, managerId).then(() => {
                // Preseleccionar primer slot disponible
                const first = store.formSlots.find(s => s.available)
                form.value.start_time = first?.time ?? ''
            })
        } else {
            form.value.start_time = ''
        }
    }
)

// Slots disponibles del store
const availableTimeSlots = computed(() => store.formSlots)

const loadingSlots = computed(() => store.loadingSlots)

onMounted(async () => {
    const [cl, cm] = await Promise.all([
        api.get('/admin/users', { params: { role: 'client', per_page: 100 } }),
        api.get('/admin/case-managers/all'),
    ])
    clients.value = cl.data.data
    caseManagers.value = cm.data.managers

    // Si ya hay fecha y manager precargados
    if (form.value.date && form.value.case_manager_id) {
        await store.fetchSlots(form.value.date, form.value.case_manager_id)
        const first = store.formSlots.find(s => s.available)
        form.value.start_time = first?.time ?? ''
    }
})

async function save() {
    errorMsg.value = ''
    if (!form.value.client_id || !form.value.case_manager_id || !form.value.date || !form.value.start_time) {
        errorMsg.value = t('appointments.requiredFields')
        return
    }
    saving.value = true
    const result = await store.createAppointment({ ...form.value })
    saving.value = false

    if (result.success) {
        form.value = {
            client_id: '', case_manager_id: '',
            date: props.date, start_time: '', status: 'pending', notes: ''
        }
        emit('created')
    } else {
        errorMsg.value = result.message ?? t('appointments.error')
    }
}
</script>

<style>
.af-backdrop {
    position: fixed;
    inset: 0;
    z-index: 1200;
    background: rgba(15, 23, 42, 0.75);
    backdrop-filter: blur(6px);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 16px;
}

.af-modal {
    background: #1a2a4a;
    border-radius: 20px;
    width: 100%;
    max-width: 480px;
    max-height: 90vh;
    display: flex;
    flex-direction: column;
    box-shadow: 0 24px 64px rgba(0, 0, 0, 0.5);
    border: 1px solid rgba(255, 255, 255, 0.1);
    overflow: hidden;
}

.af-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 18px 22px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.08);
}

.af-title {
    font-size: 1.1rem;
    font-weight: 700;
    color: #fff;
    margin: 0;
}

.af-close {
    background: rgba(255, 255, 255, 0.1);
    border: none;
    color: #fff;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    cursor: pointer;
    font-size: 0.85rem;
    transition: background 0.2s;
}

.af-close:hover {
    background: rgba(255, 255, 255, 0.2);
}

.af-body {
    flex: 1;
    overflow-y: auto;
    padding: 20px 22px;
    display: flex;
    flex-direction: column;
    gap: 14px;
}

.af-field {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.af-label {
    font-size: 0.78rem;
    font-weight: 600;
    color: rgba(255, 255, 255, 0.6);
    text-transform: uppercase;
    letter-spacing: 0.04em;
}

.af-select,
.af-input,
.af-textarea {
    background: rgba(255, 255, 255, 0.07);
    border: 1px solid rgba(255, 255, 255, 0.12);
    border-radius: 10px;
    color: #fff;
    font-size: 0.9rem;
    padding: 10px 14px;
    outline: none;
    font-family: 'Segoe UI', sans-serif;
    transition: border-color 0.2s;
    width: 100%;
}

.af-select:focus,
.af-input:focus,
.af-textarea:focus {
    border-color: #3b82f6;
}

.af-select option {
    background: #1a2a4a;
}

.af-option--taken {
    color: rgba(255, 255, 255, 0.3);
}

.af-textarea {
    resize: vertical;
    min-height: 80px;
}

.af-input::-webkit-calendar-picker-indicator {
    filter: invert(1);
}

.af-error {
    color: #ff8a80;
    font-size: 0.78rem;
    text-align: center;
    margin: 0;
}

.af-footer {
    display: flex;
    gap: 10px;
    padding: 16px 22px;
    border-top: 1px solid rgba(255, 255, 255, 0.08);
}

.af-btn {
    flex: 1;
    padding: 11px;
    border-radius: 10px;
    border: none;
    font-size: 0.9rem;
    font-weight: 700;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    transition: filter 0.2s;
}

.af-btn--cancel {
    background: rgba(255, 255, 255, 0.08);
    color: rgba(255, 255, 255, 0.7);
}

.af-btn--save {
    background: #3b82f6;
    color: #fff;
}

.af-btn--save:hover:not(:disabled) {
    filter: brightness(1.1);
}

.af-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.af-spinner {
    width: 16px;
    height: 16px;
    border: 2px solid rgba(255, 255, 255, 0.3);
    border-top-color: #fff;
    border-radius: 50%;
    animation: af-spin 0.7s linear infinite;
    display: inline-block;
}

@keyframes af-spin {
    to {
        transform: rotate(360deg);
    }
}

.modal-fade-enter-active,
.modal-fade-leave-active {
    transition: opacity 0.25s, transform 0.25s;
}

.modal-fade-enter-from,
.modal-fade-leave-to {
    opacity: 0;
    transform: scale(0.96);
}

.af-hint {
    font-size: 0.82rem;
    color: rgba(255,255,255,0.4);
    padding: 10px 14px;
    border-radius: 10px;
    border: 1px dashed rgba(255,255,255,0.15);
    margin: 0;
}
.af-hint--warn { color: #f59e0b; border-color: rgba(245,158,11,0.3); }
.af-slots-loading {
    display: flex; align-items: center; gap: 8px;
    color: rgba(255,255,255,0.5); font-size: 0.85rem;
    padding: 10px 14px;
}
</style>