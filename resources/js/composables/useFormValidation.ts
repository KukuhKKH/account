import { ref, computed, watch } from 'vue'

export interface ValidationRule {
  validator: (value: any) => boolean
  message: string
}

export interface FieldValidation {
  value: any
  rules: ValidationRule[]
  touched: boolean
}

export function useFormValidation() {
  const fields = ref<Record<string, FieldValidation>>({})

  const registerField = (name: string, value: any, rules: ValidationRule[]) => {
    fields.value[name] = {
      value,
      rules,
      touched: false
    }
  }

  const validateField = (name: string, value: any): { valid: boolean; errors: string[] } => {
    const field = fields.value[name]
    if (!field) return { valid: true, errors: [] }

    const errors: string[] = []
    field.rules.forEach(rule => {
      if (!rule.validator(value)) {
        errors.push(rule.message)
      }
    })

    return { valid: errors.length === 0, errors }
  }

  const touchField = (name: string) => {
    if (fields.value[name]) {
      fields.value[name].touched = true
    }
  }

  const isFieldValid = (name: string, value: any): boolean => {
    const result = validateField(name, value)
    return result.valid
  }

  const getFieldErrors = (name: string, value: any): string[] => {
    const field = fields.value[name]
    if (!field || !field.touched) return []

    const result = validateField(name, value)
    return result.errors
  }

  return {
    registerField,
    validateField,
    touchField,
    isFieldValid,
    getFieldErrors
  }
}

// Common validation rules
export const validationRules = {
  required: (message = 'Field ini wajib diisi'): ValidationRule => ({
    validator: (value: any) => {
      if (typeof value === 'string') return value.trim().length > 0
      return value !== null && value !== undefined && value !== ''
    },
    message
  }),

  email: (message = 'Format email tidak valid'): ValidationRule => ({
    validator: (value: string) => {
      if (!value) return true
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
      return emailRegex.test(value)
    },
    message
  }),

  minLength: (length: number, message?: string): ValidationRule => ({
    validator: (value: string) => {
      if (!value) return true
      return value.length >= length
    },
    message: message || `Minimal ${length} karakter`
  }),

  maxLength: (length: number, message?: string): ValidationRule => ({
    validator: (value: string) => {
      if (!value) return true
      return value.length <= length
    },
    message: message || `Maksimal ${length} karakter`
  }),

  matches: (otherValue: any, fieldName: string, message?: string): ValidationRule => ({
    validator: (value: string) => {
      if (!value) return true
      return value === otherValue
    },
    message: message || `Harus sama dengan ${fieldName}`
  }),

  url: (message = 'Format URL tidak valid'): ValidationRule => ({
    validator: (value: string) => {
      if (!value) return true
      try {
        new URL(value)
        return true
      } catch {
        return false
      }
    },
    message
  })
}

// Password strength calculator
export function usePasswordStrength() {
  const getPasswordStrength = (password: string): { score: number; label: string; color: string } => {
    if (!password) return { score: 0, label: 'Belum ada password', color: 'gray' }

    let score = 0

    // Length
    if (password.length >= 8) score += 1
    if (password.length >= 12) score += 1

    // Contains lowercase
    if (/[a-z]/.test(password)) score += 1

    // Contains uppercase
    if (/[A-Z]/.test(password)) score += 1

    // Contains numbers
    if (/[0-9]/.test(password)) score += 1

    // Contains special chars
    if (/[^a-zA-Z0-9]/.test(password)) score += 1

    let label = ''
    let color = ''

    if (score <= 2) {
      label = 'Lemah'
      color = 'red'
    } else if (score <= 4) {
      label = 'Sedang'
      color = 'yellow'
    } else {
      label = 'Kuat'
      color = 'green'
    }

    return { score: Math.min(score, 6), label, color }
  }

  const getPasswordRequirements = (password: string) => {
    return [
      {
        met: password.length >= 8,
        label: 'Minimal 8 karakter'
      },
      {
        met: /[a-z]/.test(password),
        label: 'Huruf kecil (a-z)'
      },
      {
        met: /[A-Z]/.test(password),
        label: 'Huruf besar (A-Z)'
      },
      {
        met: /[0-9]/.test(password),
        label: 'Angka (0-9)'
      },
      {
        met: /[^a-zA-Z0-9]/.test(password),
        label: 'Karakter khusus (!@#$%)'
      }
    ]
  }

  return {
    getPasswordStrength,
    getPasswordRequirements
  }
}
