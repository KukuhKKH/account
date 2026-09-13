export type UserRoleType = 'Superadmin' | 'Admin Account' | 'User'

export interface AuthUser {
  id: string
  name: string
  email: string
  role: UserRoleType
  avatarUrl?: string
  lastLoginAt: string
  phone?: string
  mfaEnabled: boolean
}

export interface LoginCredentials {
  email: string
  password?: string
  role?: UserRoleType
}

export interface UserAccountItem {
  id: string
  name: string
  email: string
  role: UserRoleType
  status: 'active' | 'suspended' | 'pending'
  lastActive: string
  authMethod: 'BFF Session' | 'SSO Passkey' | 'Password'
}

export interface ApiUserData {
  id: number | string
  name: string
  email: string
  avatar?: string | null
  phone?: string | null
  address?: string | null
  roles?: string[]
  custom_data?: Record<string, unknown> | null
  last_login_at?: string | null
  created_at?: string | null
  updated_at?: string | null
}

export interface ApiMeResponse {
  authenticated: boolean
  user: ApiUserData | null
}

