import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import echo from '@/plugins/echo'

export const useNotificationStore = defineStore('notifications', () => {
    const stored = localStorage.getItem('app_notifications')
    const notifications = ref(stored ? JSON.parse(stored) : [])

    const unreadCount = computed(() =>
        notifications.value.filter(n => !n.read).length
    )

    function save() {
        localStorage.setItem(
            'app_notifications',
            JSON.stringify(notifications.value.slice(0, 50))
        )
    }

    // ── Sonido ──────────────────────────────────────────────
    function playSound() {
        try {
            const audio = new Audio('/sounds/notification.mp3')
            audio.volume = 0.6
            audio.play().catch(() => {}) // silenciar error si el navegador bloquea
        } catch (_) {}
    }

    // ── Agregar notificación ────────────────────────────────
    function add({ type, clientName, caseManagerName, date, time, status }) {
        notifications.value.unshift({
            id:              Date.now(),
            type,
            clientName,
            caseManagerName,
            date,
            time,
            status,
            read:            false,
            createdAt:       new Date().toISOString(),
        })
        save()
        playSound()
    }

    function markAllRead() {
        notifications.value.forEach(n => (n.read = true))
        save()
    }

    function markRead(id) {
        const n = notifications.value.find(n => n.id === id)
        if (n) { n.read = true; save() }
    }

    function clear() {
        notifications.value = []
        save()
    }

    // ── Suscripción Reverb ──────────────────────────────────
    let subscribed = false

    function subscribeReverb() {
        if (subscribed) return
        subscribed = true
        console.log('🔔 Suscribiendo al canal appointments...')

        echo
            .channel('appointments')
            .listen('.appointment.created', (data) => {
                // El payload viene dentro de data directamente
                // broadcastAs() lo mete en el nivel raíz
                const appt = data.appointment ?? data

                add({
                    type:            'created',
                    clientName:      appt.client?.name        ?? '—',
                    caseManagerName: appt.case_manager?.name  ?? '—',
                    date:            appt.date                ?? '—',
                    time:            appt.start_time          ?? '—',
                    status:          appt.status              ?? 'pending',
                })
            })
    }

    function unsubscribeReverb() {
        echo.leaveChannel('appointments')
        subscribed = false
    }

    return {
        notifications,
        unreadCount,
        add,
        markAllRead,
        markRead,
        clear,
        subscribeReverb,
        unsubscribeReverb,
    }
})