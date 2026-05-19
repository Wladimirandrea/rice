// resources/js/stores/scheduleStore.js
import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/plugins/axios'

export const useScheduleStore = defineStore('schedule', () => {
    const schedules = ref([])
    const loading   = ref(false)
    const error     = ref(null)

    async function fetchSchedule() {
        loading.value = true
        error.value   = null
        try {
            const { data } = await api.get('/admin/schedule')
            schedules.value = data.data
        } catch (e) {
            error.value = e.response?.data?.message ?? 'Error loading schedule'
        } finally {
            loading.value = false
        }
    }

    async function updateDay(schedule) {
        try {
            const { data } = await api.put(`/admin/schedule/${schedule.id}`, {
                is_working: schedule.is_working,
                start_time: schedule.start_time,
                end_time:   schedule.end_time,
            })
            // Actualizar en el array local
            const idx = schedules.value.findIndex(s => s.id === schedule.id)
            if (idx !== -1) schedules.value[idx] = data.data
            return { success: true }
        } catch (e) {
            return {
                success: false,
                errors: e.response?.data?.errors ?? {},
                message: e.response?.data?.message ?? 'Error saving',
            }
        }
    }

    return { schedules, loading, error, fetchSchedule, updateDay }
})