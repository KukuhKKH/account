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
