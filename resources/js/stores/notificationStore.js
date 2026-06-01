// resources/js/stores/notificationStore.js
import { defineStore } from 'pinia'
import { ref, computed } from 'vue'

export const useNotificationStore = defineStore('notifications', () => {
    // Cargar desde localStorage al iniciar
    const stored = localStorage.getItem('app_notifications')
    const notifications = ref(stored ? JSON.parse(stored) : [])

    const unreadCount = computed(() => notifications.value.filter(n => !n.read).length)

    function save() {
        localStorage.setItem('app_notifications', JSON.stringify(notifications.value.slice(0, 50)))
    }

    function add({ type, clientName, caseManagerName, date, time, status }) {
        notifications.value.unshift({
            id:              Date.now(),
            type,            // 'created' | 'status_changed' | 'cancelled'
            clientName,
            caseManagerName,
            date,
            time,
            status,
            read:            false,
            createdAt:       new Date().toISOString(),
        })
        save()
    }

    function markAllRead() {
        notifications.value.forEach(n => n.read = true)
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

    return { notifications, unreadCount, add, markAllRead, markRead, clear }
})