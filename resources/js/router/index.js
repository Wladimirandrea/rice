// resources/js/router/index.js
import { createRouter, createWebHistory } from 'vue-router'

const LoginView = () => import('@/views/auth/LoginView.vue')
const ForgotPasswordView = () => import('@/views/auth/ForgotPasswordView.vue')
const ResetPasswordView  = () => import('@/views/auth/ResetPasswordView.vue')
const AdminDashboard = () => import('@/views/admin/DashboardView.vue')
const ManagerDashboard = () => import('@/views/manager/DashboardView.vue')
const ClientDashboard = () => import('@/views/client/DashboardView.vue')
const AdminLayout = () => import('@/layouts/AdminLayout.vue')
const ManagerLayout = () => import('@/layouts/ManagerLayout.vue')
const ClientLayout = () => import('@/layouts/ClientLayout.vue')

const routes = [
    {
        path: '/',
        name: 'login',
        component: LoginView,
    },
    {
        path: '/forgot-password',
        name: 'forgot-password',
        component: ForgotPasswordView,
        meta: { guest: true },
    },
    {
        path: '/reset-password',
        name: 'reset-password',
        component: ResetPasswordView,
        meta: { guest: true },
    },
    {
        path: '/admin',
        component: AdminLayout,
        children: [
            { path: 'dashboard', name: 'admin.dashboard', component: AdminDashboard },
        ],
    },
    {
        path: '/manager',
        component: ManagerLayout,
        children: [
            { path: 'dashboard', name: 'manager.dashboard', component: ManagerDashboard },
        ],
    },
    {
        path: '/client',
        component: ClientLayout,
        children: [
            { path: 'dashboard', name: 'client.dashboard', component: ClientDashboard },
        ],
    },
]

const router = createRouter({
    history: createWebHistory(import.meta.env.BASE_URL),
    routes,
})

// Guard desactivado temporalmente
// router.beforeEach(...)

export default router