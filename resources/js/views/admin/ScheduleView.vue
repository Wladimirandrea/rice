<!-- resources/js/views/admin/ScheduleView.vue -->
<template>
    <div class="sc-view">
        <AppTopbar
            :title="$t('schedule.title')"
            :crumbs="[
                { label: $t('users.crumbs.dashboard'), icon: 'fa-house',    route: 'admin.dashboard' },
                { label: $t('schedule.title'),          icon: 'fa-calendar' },
            ]"
            :actions="[]"
        />

        <div class="sc-view__body">
            <!-- Loading -->
            <div v-if="store.loading" class="sc-view__loading">
                <div class="sc-spinner" />
                <span>{{ $t('common.loading') }}</span>
            </div>

            <!-- Error -->
            <div v-else-if="store.error" class="sc-view__error">
                {{ store.error }}
            </div>

            <!-- Grid de cards -->
            <div v-else class="sc-view__grid">
                <ScheduleCard
                    v-for="day in store.schedules"
                    :key="day.day_of_week"
                    :day="day"
                />
            </div>
        </div>
    </div>
</template>

<script setup>
import { onMounted } from 'vue'
import { useScheduleStore } from '@/stores/scheduleStore'
import ScheduleCard from '@/components/schedule/ScheduleCard.vue'
import AppTopbar from '@/components/layout/AppTopbar.vue'

const store = useScheduleStore()
onMounted(() => store.fetchSchedule())
</script>

<style scoped>
.sc-view {
    display: flex;
    flex-direction: column;
    height: 100%;
}
.sc-view__body {
    padding: 28px 24px;
    flex: 1;
    overflow-y: auto;
}
.sc-view__loading {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 12px;
    padding: 48px 0;
    color: #90a4ae;
}
.sc-spinner {
    width: 40px; height: 40px;
    border: 3px solid #e3eaf4;
    border-top-color: #1565c0;
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }
.sc-view__error {
    text-align: center;
    color: #e53935;
    padding: 48px 0;
}
.sc-view__grid {
    display: flex;
    flex-wrap: wrap;
    gap: 24px;
    justify-content: flex-start;
}

@media (max-width: 767px) {
    .sc-view__body { padding: 16px; }
    .sc-view__grid { justify-content: center; }
}
</style>