import type {
  PasswordChangeLogItem,
  SignInLogItem,
  SecurityStats,
  AuditListMeta,
  PasswordLogsResponse,
  SignInLogsResponse,
  SecurityStatsResponse
} from '~/types/audit'

export function useAudit() {
  const config = useRuntimeConfig()
  const apiBase = config.public.apiBase || 'https://api-identity.home.test'

  const passwordLogs = ref<PasswordChangeLogItem[]>([])
  const signInLogs = ref<SignInLogItem[]>([])
  const stats = ref<SecurityStats>({
    defenseScore: 99.8,
    signInsLast24h: 0,
    passwordRotations: 0,
    adminResetsTotal: 0,
    selfChangesTotal: 0,
    activeIpsLast24h: 0,
    totalAccounts: 0,
    encryptionStandard: 'ChaCha20-Poly1305 / HMAC-SHA256',
    auditDigestStatus: 'Verified & Immutable'
  })

  const passwordMeta = ref<AuditListMeta>({
    currentPage: 1,
    perPage: 15,
    total: 0,
    lastPage: 1
  })

  const signInMeta = ref<AuditListMeta>({
    currentPage: 1,
    perPage: 15,
    total: 0,
    lastPage: 1
  })

  const isLoading = ref(false)
  const isRefreshing = ref(false)
  const error = ref<string | null>(null)

  // Filter state
  const searchQuery = ref('')
  const selectedChangeType = ref('all')
  const currentPage = ref(1)
  const perPage = ref(15)

  /**
   * Fetch password change audit logs from GET /audit-logs/passwords
   */
  async function fetchPasswordLogs(params?: {
    search?: string
    changeType?: string
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

    const changeType = params?.changeType !== undefined ? params.changeType : selectedChangeType.value
    if (changeType && changeType !== 'all') {
      query.change_type = changeType
    }

    try {
      const res = await $fetch<PasswordLogsResponse>(`${apiBase}/audit-logs/passwords`, {
        method: 'GET',
        credentials: 'include',
        headers: {
          Accept: 'application/json'
        },
        query
      })

      if (res && res.success) {
        passwordLogs.value = res.data
        passwordMeta.value = res.meta
      }
    } catch (err: unknown) {
      const fetchError = err as { data?: { message?: string } }
      const errMsg = fetchError?.data?.message || 'Gagal memuat log audit kata sandi.'
      error.value = errMsg
    } finally {
      isLoading.value = false
    }
  }

  /**
   * Fetch sign-in and access logs from GET /audit-logs/sign-ins
   */
  async function fetchSignInLogs(params?: {
    search?: string
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

    try {
      const res = await $fetch<SignInLogsResponse>(`${apiBase}/audit-logs/sign-ins`, {
        method: 'GET',
        credentials: 'include',
        headers: {
          Accept: 'application/json'
        },
        query
      })

      if (res && res.success) {
        signInLogs.value = res.data
        signInMeta.value = res.meta
      }
    } catch (err: unknown) {
      const fetchError = err as { data?: { message?: string } }
      const errMsg = fetchError?.data?.message || 'Gagal memuat log aktivitas login.'
      error.value = errMsg
    } finally {
      isLoading.value = false
    }
  }

  /**
   * Fetch real-time security statistics from GET /audit-logs/stats
   */
  async function fetchSecurityStats() {
    try {
      const res = await $fetch<SecurityStatsResponse>(`${apiBase}/audit-logs/stats`, {
        method: 'GET',
        credentials: 'include',
        headers: {
          Accept: 'application/json'
        }
      })

      if (res && res.success && res.data) {
        stats.value = res.data
      }
    } catch (err: unknown) {
      console.warn('Failed to fetch security stats telemetry:', err)
    }
  }

  /**
   * Refresh all active logs and telemetry simultaneously
   */
  async function refreshAll() {
    isRefreshing.value = true
    try {
      await Promise.all([
        fetchPasswordLogs(),
        fetchSignInLogs(),
        fetchSecurityStats()
      ])
    } finally {
      isRefreshing.value = false
    }
  }

  return {
    passwordLogs,
    signInLogs,
    stats,
    passwordMeta,
    signInMeta,
    isLoading,
    isRefreshing,
    error,
    searchQuery,
    selectedChangeType,
    currentPage,
    perPage,
    fetchPasswordLogs,
    fetchSignInLogs,
    fetchSecurityStats,
    refreshAll
  }
}
