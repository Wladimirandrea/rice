<script setup>
import { useRouter } from 'vue-router'

const router = useRouter()

const props = defineProps({
    title:   { type: String, required: true },
    crumbs:  { type: Array, default: () => [] },
    actions: { type: Array, default: () => [] },
})

const emit = defineEmits(['action'])
</script>

<template>
    <div class="topbar">
        <div class="titleRow">
            <h1 class="pageTitle">{{ title }}</h1>
        </div>
        <div class="crumbRow">
            <div class="crumb">
                <template v-for="(crumb, index) in crumbs" :key="index">
                    <a class="crumbItem" :class="{ isActive: index === crumbs.length - 1 }" href="#" @click.prevent="crumb.route ? router.push({ name: crumb.route }) : null">
                        <i :class="['fa', crumb.icon]"></i>
                        <span>{{ crumb.label }}</span>
                    </a>
                    <span v-if="index < crumbs.length - 1" class="crumbSep">/</span>
                </template>
            </div>
            <div class="actions">
                <button v-for="action in actions" :key="action.emit" class="btn" :class="{ btnPrimary: action.type === 'primary', btnDanger: action.type === 'danger', btnGhost: action.type === 'ghost' }" @click="emit('action', action.emit)">
                    <i v-if="action.icon" :class="['fa-solid', action.icon]"></i>
                    {{ action.label }}
                </button>
            </div>
        </div>
    </div>
</template>

<style scoped>
.topbar { padding: 10px 16px; border-bottom: 1px solid #1e2a3a; background: rgba(255,255,255,0.03); backdrop-filter: blur(10px); }
.titleRow { display: flex; align-items: flex-end; justify-content: space-between; gap: 14px; flex-wrap: wrap; margin-bottom: 12px; }
.pageTitle { margin: 0; font-size: 22px; font-weight: 600; color: white; letter-spacing: 0.2px; font-family: 'Segoe UI', sans-serif; }
.crumbRow { display: flex; align-items: center; justify-content: space-between; gap: 14px; flex-wrap: wrap; }
.crumb { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
.crumbItem { display: inline-flex; align-items: center; gap: 8px; padding: 7px 12px; border: 1px solid transparent; border-radius: 999px; background: rgba(255,255,255,0.03); color: #7a8aaa; font-size: 13px; cursor: pointer; text-decoration: none; transition: transform 0.15s ease, background 0.15s ease, border-color 0.15s ease; font-family: 'Segoe UI', sans-serif; }
.crumbItem i { color: #7a8aaa; font-size: 13px; }
.crumbItem:hover { transform: translateY(-1px); background: rgba(255,255,255,0.06); border-color: rgba(255,255,255,0.10); color: white; }
.crumbItem.isActive { border-color: #2a3a55; background: rgba(255,255,255,0.06); color: white; }
.crumbItem.isActive i { color: white; }
.crumbSep { color: rgba(255,255,255,0.30); user-select: none; font-size: 13px; }
.actions { display: flex; gap: 8px; flex-wrap: wrap; }
.btn { display: inline-flex; align-items: center; gap: 8px; padding: 7px 12px; border: 1px solid rgba(255,255,255,0.12); border-radius: 10px; background: rgba(255,255,255,0.04); color: white; font-size: 13px; cursor: pointer; transition: transform 0.15s ease, background 0.15s ease, border-color 0.15s ease; font-family: 'Segoe UI', sans-serif; }
.btn i { color: #7a8aaa; }
.btn:hover { transform: translateY(-1px); background: rgba(255,255,255,0.06); border-color: rgba(255,255,255,0.18); }
.btnPrimary { background: linear-gradient(180deg, rgba(45,212,191,0.18), rgba(45,212,191,0.10)); border-color: rgba(45,212,191,0.35); }
.btnPrimary i { color: rgba(45,212,191,0.95); }
.btnGhost { background: rgba(255,255,255,0.03); }
.btnDanger { background: linear-gradient(180deg, rgba(255,77,79,0.16), rgba(255,77,79,0.08)); border-color: rgba(255,77,79,0.35); }
.btnDanger i { color: rgba(255,77,79,0.95); }
.btnDanger:hover { background: linear-gradient(180deg, rgba(255,77,79,0.22), rgba(255,77,79,0.14)); }
</style>
