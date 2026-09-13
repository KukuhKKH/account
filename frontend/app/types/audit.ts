export interface PasswordChangeLogItem {
  id: string
  userId: string
  userName: string
  userEmail: string
  userRole: string
  changedByUserId?: string | null
  changedByName?: string | null
  changedByEmail?: string | null
  changedByRole?: string | null
  changeType: 'self_change' | 'admin_reset' | 'system_reset' | 'webhook_sync'
  changeTypeLabel: string
  isSelf: boolean
  isAdmin: boolean
  isSystem: boolean
  ipAddress?: string | null
  userAgent?: string | null
  reason?: string | null
  description: string
  viaLogtoApi: boolean
  metadata?: Record<string, unknown> | null
  createdAt?: string | null
  timeAgo: string
}

export interface SignInLogItem {
  id: string
  userId: string
  userName: string
  userEmail: string
  userRole: string
  ipAddress?: string | null
  userAgent?: string | null
  deviceInfo?: {
    browser?: string
    os?: string
    user_agent?: string
  } | null
  browser: string
  os: string
  signedInAt?: string | null
  timeAgo: string
  logtoEventId?: string | null
}

export interface SecurityStats {
  defenseScore: number
  signInsLast24h: number
  passwordRotations: number
  adminResetsTotal: number
  selfChangesTotal: number
  activeIpsLast24h: number
  totalAccounts: number
  encryptionStandard: string
  auditDigestStatus: string
}

export interface AuditListMeta {
  currentPage: number
  perPage: number
  total: number
  lastPage: number
}

export interface PasswordLogsResponse {
  success: boolean
  data: PasswordChangeLogItem[]
  meta: AuditListMeta
}

export interface SignInLogsResponse {
  success: boolean
  data: SignInLogItem[]
  meta: AuditListMeta
}

export interface SecurityStatsResponse {
  success: boolean
  data: SecurityStats
}
