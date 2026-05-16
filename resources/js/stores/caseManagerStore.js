// resources/js/stores/caseManagerStore.js
import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/plugins/axios'

export const useCaseManagerStore = defineStore('caseManager', () => {
  const managers = ref([])
  const meta = ref({})
  const loading = ref(false)
  const error = ref(null)

  // Modal principal
  const modalOpen = ref(false)
  const modalManager = ref(null)
  const modalClients = ref([])
  const modalUnassigned = ref([])
  const modalLoading = ref(false)
  const activeTab = ref('assigned')

  // Modal cliente detalle
  const clientModalOpen = ref(false)
  const selectedClient = ref(null)
  const allManagers = ref([])
  const showReassignList = ref(false)
  const actionLoading = ref(false)

  // ── Fetch managers ──────────────────────────────────────
  async function fetchManagers(params = {}) {
    loading.value = true
    error.value = null
    try {
      const { data } = await api.get('/admin/case-managers', { params })
      managers.value = data.data
      meta.value = data.meta
    } catch (e) {
      error.value = e.response?.data?.message ?? 'Error loading case managers'
    } finally {
      loading.value = false
    }
  }

  // ── Abrir modal principal ────────────────────────────────
  async function openClientsModal(manager) {
    modalManager.value = manager
    modalClients.value = []
    modalUnassigned.value = []
    modalOpen.value = true
    modalLoading.value = true
    activeTab.value = 'assigned'

    try {
      const [assigned, unassigned] = await Promise.all([
        api.get(`/admin/case-managers/${manager.id}/clients`),
        api.get('/admin/case-managers/unassigned-clients'),
      ])
      modalClients.value = assigned.data.clients
      modalUnassigned.value = unassigned.data.clients
    } catch (e) {
      modalClients.value = []
      modalUnassigned.value = []
    } finally {
      modalLoading.value = false
    }
  }

  function closeModal() {
    modalOpen.value = false
    modalManager.value = null
    modalClients.value = []
    modalUnassigned.value = []
    activeTab.value = 'assigned'
  }

  // ── Abrir modal cliente ──────────────────────────────────
  async function openClientDetail(client) {
    selectedClient.value = client
    showReassignList.value = false
    clientModalOpen.value = true

    // Cargar lista de managers para reasignar
    try {
      const { data } = await api.get('/admin/case-managers/all')
      allManagers.value = data.managers
    } catch {
      allManagers.value = []
    }
  }

  function closeClientModal() {
    clientModalOpen.value = false
    selectedClient.value = null
    showReassignList.value = false
  }

  // ── Reasignar cliente ────────────────────────────────────
  async function reassignClient(newManagerId) {
    if (!selectedClient.value) return
    actionLoading.value = true
    try {
      await api.post('/admin/case-managers/reassign', {
        client_id: selectedClient.value.id,
        case_manager_id: newManagerId,
      })

      // Actualizar listas en tiempo real
      const newManager = allManagers.value.find(m => m.id === newManagerId)

      // Quitar de asignados del manager actual
      modalClients.value = modalClients.value.filter(
        c => c.id !== selectedClient.value.id
      )
      // Quitar de sin asignar si estaba ahí
      modalUnassigned.value = modalUnassigned.value.filter(
        c => c.id !== selectedClient.value.id
      )

      // Actualizar contador en la tarjeta del manager actual
      const managerInList = managers.value.find(m => m.id === modalManager.value?.id)
      if (managerInList) managerInList.clients_count = modalClients.value.length

      // Si el nuevo manager es el mismo que estamos viendo, agregar a su lista
      if (newManagerId === modalManager.value?.id) {
        modalClients.value.push(selectedClient.value)
      }

      closeClientModal()
    } catch (e) {
      console.error('Error reassigning:', e)
    } finally {
      actionLoading.value = false
    }
  }

  // ── Liberar cliente ──────────────────────────────────────
  async function releaseClient() {
    if (!selectedClient.value) return
    actionLoading.value = true
    try {
      await api.delete(`/admin/case-managers/release/${selectedClient.value.id}`)

      // Quitar de la lista de asignados
      modalClients.value = modalClients.value.filter(
        c => c.id !== selectedClient.value.id
      )
      // Agregar a sin asignar
      modalUnassigned.value.push(selectedClient.value)

      // Actualizar contador en tarjeta
      const managerInList = managers.value.find(m => m.id === modalManager.value?.id)
      if (managerInList) managerInList.clients_count = modalClients.value.length

      closeClientModal()
    } catch (e) {
      console.error('Error releasing:', e)
    } finally {
      actionLoading.value = false
    }
  }

  return {
    managers, meta, loading, error,
    modalOpen, modalManager, modalClients, modalUnassigned, modalLoading, activeTab,
    clientModalOpen, selectedClient, allManagers, showReassignList, actionLoading,
    fetchManagers, openClientsModal, closeModal,
    openClientDetail, closeClientModal, reassignClient, releaseClient,
  }
})