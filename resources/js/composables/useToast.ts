import { ref } from 'vue'

export interface ToastOptions {
  type?: 'success' | 'error' | 'warning' | 'info'
  message: string
  title?: string
  duration?: number
}

const toasts = ref<(ToastOptions & { id: string })[]>([])

export function useToast() {
  const addToast = (options: ToastOptions) => {
    const id = `toast-${Date.now()}-${Math.random()}`
    const toast = { id, ...options }
    toasts.value.push(toast)

    // Auto-remove after duration
    if (options.duration !== 0) {
      setTimeout(() => {
        removeToast(id)
      }, options.duration || 5000)
    }

    return id
  }

  const removeToast = (id: string) => {
    toasts.value = toasts.value.filter(t => t.id !== id)
  }

  const success = (message: string, title?: string) => {
    return addToast({ type: 'success', message, title })
  }

  const error = (message: string, title?: string) => {
    return addToast({ type: 'error', message, title })
  }

  const warning = (message: string, title?: string) => {
    return addToast({ type: 'warning', message, title })
  }

  const info = (message: string, title?: string) => {
    return addToast({ type: 'info', message, title })
  }

  const clear = () => {
    toasts.value = []
  }

  return {
    toasts,
    addToast,
    removeToast,
    success,
    error,
    warning,
    info,
    clear,
  }
}
