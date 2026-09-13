import type { UserRoleType } from '~/types/auth'

export interface UserRecord {
  id: string
  logtoId?: string | null
  name: string
  email: string
  role: UserRoleType
  roles?: string[]
  status: 'active' | 'suspended'
  isSuspended: boolean
  phone?: string | null
  address?: string | null
  avatar?: string | null
  lastActive: string
  lastLoginAt?: string | null
  authMethod: 'BFF Session' | 'SSO Passkey' | 'Password'
  customData?: Record<string, unknown>
  createdAt?: string | null
}

export interface UserListMeta {
  currentPage: number
  perPage: number
  total: number
  lastPage: number
}

export interface UserListResponse {
  success: boolean
  data: UserRecord[]
  meta: UserListMeta
  message?: string
}

export interface UserMutationResponse {
  success: boolean
  message: string
  data?: UserRecord
}

export function useUsers() {
  const config = useRuntimeConfig()
  const apiBase = config.public.apiBase || 'https://api-identity.home.test'

  const users = ref<UserRecord[]>([])
  const meta = ref<UserListMeta>({
    currentPage: 1,
    perPage: 15,
    total: 0,
    lastPage: 1
  })

  const isLoading = ref(false)
  const error = ref<string | null>(null)

  // Current filter state
  const searchQuery = ref('')
  const selectedRole = ref<string>('all')
  const selectedStatus = ref<string>('all')
  const currentPage = ref(1)
  const perPage = ref(15)

  /**
   * Fetch users from backend BFF /users endpoint
   */
  async function fetchUsers(params?: {
    search?: string
    role?: string
    status?: string
    page?: number
    perPage?: number
  }) {
    isLoading.value = true
    error.value = null

    const query: Record<string, string | number> = {
      page: params?.page ?? currentPage.value,
      per_page: params?.perPage ?? perPage.value
    }

    const search = params?.search !== undefined ? params.search : searchQuery.value
    if (search && search.trim()) {
      query.search = search.trim()
    }

    const role = params?.role !== undefined ? params.role : selectedRole.value
    if (role && role !== 'all') {
      query.role = role
    }

    const status = params?.status !== undefined ? params.status : selectedStatus.value
    if (status && status !== 'all') {
      query.status = status
    }

    try {
      const res = await $fetch<UserListResponse>(`${apiBase}/users`, {
        method: 'GET',
        credentials: 'include',
        headers: {
          'Accept': 'application/json'
        },
        query
      })

      if (res && res.success) {
        users.value = res.data
        meta.value = res.meta
        currentPage.value = res.meta.currentPage
        perPage.value = res.meta.perPage
      }
    } catch (err: unknown) {
      const fetchError = err as { data?: { message?: string } }
      const errMsg = fetchError?.data?.message || 'Gagal memuat data pengguna dari server.'
      error.value = errMsg
    } finally {
      isLoading.value = false
    }
  }

  /**
   * Create a new user via POST /users
   */
  async function createUser(payload: {
    name: string
    email: string
    role: UserRoleType
    password?: string
    phone?: string
    address?: string
  }): Promise<UserMutationResponse> {
    isLoading.value = true
    error.value = null

    try {
      const res = await $fetch<UserMutationResponse>(`${apiBase}/users`, {
        method: 'POST',
        credentials: 'include',
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json'
        },
        body: payload
      })

      // Re-fetch current user list
      await fetchUsers()
      return res
    } catch (err: unknown) {
      const fetchError = err as { data?: { message?: string }, status?: number }
      const errMsg = fetchError?.data?.message || 'Gagal membuat pengguna baru.'
      error.value = errMsg
      throw new Error(errMsg)
    } finally {
      isLoading.value = false
    }
  }

  /**
   * Update user via PUT /users/{id}
   */
  async function updateUser(id: string | number, payload: {
    name?: string
    email?: string
    role?: UserRoleType
    phone?: string
    address?: string
  }): Promise<UserMutationResponse> {
    isLoading.value = true
    error.value = null

    try {
      const res = await $fetch<UserMutationResponse>(`${apiBase}/users/${id}`, {
        method: 'PUT',
        credentials: 'include',
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json'
        },
        body: payload
      })

      // Update in local array optimistically
      const idx = users.value.findIndex(u => String(u.id) === String(id))
      if (idx !== -1 && res.data) {
        users.value[idx] = { ...users.value[idx], ...res.data }
      } else {
        await fetchUsers()
      }

      return res
    } catch (err: unknown) {
      const fetchError = err as { data?: { message?: string } }
      const errMsg = fetchError?.data?.message || 'Gagal memperbarui data pengguna.'
      error.value = errMsg
      throw new Error(errMsg)
    } finally {
      isLoading.value = false
    }
  }

  /**
   * Delete user via DELETE /users/{id}
   */
  async function deleteUser(id: string | number): Promise<UserMutationResponse> {
    isLoading.value = true
    error.value = null

    try {
      const res = await $fetch<UserMutationResponse>(`${apiBase}/users/${id}`, {
        method: 'DELETE',
        credentials: 'include',
        headers: {
          'Accept': 'application/json'
        }
      })

      // Remove from local array
      users.value = users.value.filter(u => String(u.id) !== String(id))
      meta.value.total = Math.max(0, meta.value.total - 1)

      return res
    } catch (err: unknown) {
      const fetchError = err as { data?: { message?: string } }
      const errMsg = fetchError?.data?.message || 'Gagal menghapus pengguna.'
      error.value = errMsg
      throw new Error(errMsg)
    } finally {
      isLoading.value = false
    }
  }

  /**
   * Change user status explicitly (active | suspended) via PATCH /users/{id}/status
   */
  async function changeStatus(id: string | number, status: 'active' | 'suspended', reason?: string): Promise<UserMutationResponse> {
    error.value = null

    try {
      const res = await $fetch<UserMutationResponse>(`${apiBase}/users/${id}/status`, {
        method: 'PATCH',
        credentials: 'include',
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json'
        },
        body: { status, reason }
      })

      // Update local array immutably for full Vue 3 / PrimeVue reactivity
      if (res.data) {
        const updated = res.data
        users.value = users.value.map(u => {
          if (String(u.id) === String(id)) {
            return {
              ...u,
              ...updated,
              status: updated.status,
              isSuspended: updated.isSuspended
            }
          }
          return u
        })
      }

      return res
    } catch (err: unknown) {
      const fetchError = err as { data?: { message?: string } }
      const errMsg = fetchError?.data?.message || 'Gagal mengubah status akun.'
      error.value = errMsg
      throw new Error(errMsg)
    }
  }

  /**
   * Legacy wrapper for toggling status by inverting current state.
   */
  async function toggleStatus(id: string | number, currentStatus?: 'active' | 'suspended'): Promise<UserMutationResponse> {
    const existing = users.value.find(u => String(u.id) === String(id))
    const statusToSet: 'active' | 'suspended' = currentStatus
      ? (currentStatus === 'active' ? 'suspended' : 'active')
      : (existing?.status === 'active' ? 'suspended' : 'active')

    return await changeStatus(id, statusToSet)
  }

  /**
   * Reset target user's password via POST /users/{id}/reset-password
   */
  async function resetPassword(id: string | number, payload: {
    password: string
    reason?: string
  }): Promise<UserMutationResponse> {
    isLoading.value = true
    error.value = null

    try {
      const res = await $fetch<UserMutationResponse>(`${apiBase}/users/${id}/reset-password`, {
        method: 'POST',
        credentials: 'include',
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json'
        },
        body: payload
      })

      return res
    } catch (err: unknown) {
      const fetchError = err as { data?: { message?: string } }
      const errMsg = fetchError?.data?.message || 'Gagal mengubah kata sandi pengguna.'
      error.value = errMsg
      throw new Error(errMsg)
    } finally {
      isLoading.value = false
    }
  }

  return {
    users,
    meta,
    isLoading,
    error,
    searchQuery,
    selectedRole,
    selectedStatus,
    currentPage,
    perPage,
    fetchUsers,
    createUser,
    updateUser,
    deleteUser,
    changeStatus,
    toggleStatus,
    resetPassword
  }
}
