<script setup lang="ts">
import { useAuthStore } from '~/stores/auth'
import { useClusterStore } from '~/stores/cluster'
import {
  Server,
  Lock,
  X,
  Copy,
  Check,
  Terminal,
  ShieldCheck,
  Layers,
  Network,
  Cpu,
  Zap,
  Tag as TagIcon
} from 'lucide-vue-next'

const authStore = useAuthStore()
const clusterStore = useClusterStore()

const isOpen = computed({
  get: () => clusterStore.selectedNodeModal !== null,
  set: (val) => {
    if (!val) clusterStore.closeNodeModal()
  }
})

const copiedIp = ref(false)
const copiedSsh = ref(false)

// Computed SSH connection string based on node reference
const sshCommand = computed(() => {
  if (!clusterStore.selectedNodeModal) return ''
  const node = clusterStore.selectedNodeModal
  const ip = authStore.canAccessSubnets && clusterStore.showDevDetails ? node.internalIp : node.maskedIp

  if (node.id === 'dns' || node.id === 'nfs') {
    return `ssh root@${ip}`
  } else if (node.id === 'karina') {
    return `ssh hermes@${ip}`
  } else {
    return `ssh webmaster@${ip}`
  }
})

function copyToClipboard(text: string, type: 'ip' | 'ssh') {
  if (typeof navigator !== 'undefined' && navigator.clipboard) {
    navigator.clipboard.writeText(text)
    if (type === 'ip') {
      copiedIp.value = true
      setTimeout(() => copiedIp.value = false, 2000)
    } else {
      copiedSsh.value = true
      setTimeout(() => copiedSsh.value = false, 2000)
    }
  }
}
</script>

<template>
  <Dialog
    v-model:visible="isOpen"
    modal
    :dismissableMask="true"
    :showHeader="false"
    :closable="false"
    :style="{ width: '92vw', maxWidth: '480px' }"
    :pt="{
      root: { class: '!rounded-2xl !border !border-slate-200/80 dark:!border-slate-800/80 !bg-white/95 dark:!bg-slate-950/95 !backdrop-blur-2xl !p-0 !overflow-hidden !shadow-2xl' },
      mask: { class: '!backdrop-blur-md !bg-slate-950/60 dark:!bg-slate-950/75' },
      header: { class: '!hidden' },
      closeButton: { class: '!hidden' },
      content: { class: '!p-0' }
    }"
  >
    <div v-if="clusterStore.selectedNodeModal" class="p-5 sm:p-6 space-y-4">
      
      <!-- Sleek Compact Header Bar -->
      <div class="flex items-center justify-between pb-3 border-b border-slate-200/80 dark:border-slate-800/80">
        <div class="flex items-center gap-2.5 min-w-0">
          <div class="w-8 h-8 rounded-lg bg-indigo-50 dark:bg-slate-900 border border-indigo-100 dark:border-indigo-900/50 flex items-center justify-center shrink-0">
            <component :is="clusterStore.selectedNodeModal.icon || Server" class="w-4 h-4 text-indigo-600 dark:text-sky-400" />
          </div>
          <div class="min-w-0">
            <div class="flex items-center gap-2">
              <h3 class="text-sm font-bold text-slate-900 dark:text-white truncate">
                {{ clusterStore.selectedNodeModal.name }}
              </h3>
              <span class="inline-flex items-center gap-1 text-[9px] font-mono px-1.5 py-0.5 rounded bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 font-bold border border-emerald-500/20">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                <span>ONLINE</span>
              </span>
            </div>
            <p class="text-[11px] font-mono text-slate-400 truncate">
              {{ clusterStore.selectedNodeModal.type }} • {{ clusterStore.selectedNodeModal.zone }}
            </p>
          </div>
        </div>

        <!-- Single Clean Close Button -->
        <button
          @click="clusterStore.closeNodeModal()"
          class="p-1.5 rounded-lg text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-900 transition-colors cursor-pointer shrink-0 ml-2"
          aria-label="Tutup Dialog"
        >
          <X class="w-4 h-4" />
        </button>
      </div>

      <!-- Node Role & Function Description -->
      <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-900/80 border border-slate-200/80 dark:border-slate-800/80 text-xs text-slate-700 dark:text-slate-300 leading-relaxed">
        <span class="font-bold text-slate-900 dark:text-white text-[11px] block mb-0.5">Peran & Tanggung Jawab:</span>
        {{ clusterStore.selectedNodeModal.role }}
      </div>

      <!-- Real Infrastructure Architecture Specifications Grid (Unique per Node) -->
      <div class="grid grid-cols-2 gap-2 text-xs font-mono">
        <div class="p-2.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800/80">
          <span class="text-[10px] text-slate-400 block font-sans">SOFTWARE STACK</span>
          <span class="font-bold text-slate-900 dark:text-white text-xs truncate block">
            {{ clusterStore.selectedNodeModal.stack }}
          </span>
        </div>

        <div class="p-2.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800/80">
          <span class="text-[10px] text-slate-400 block font-sans">PORT / PROTOKOL</span>
          <span class="font-bold text-slate-900 dark:text-white text-xs truncate block">
            {{ clusterStore.selectedNodeModal.ports }}
          </span>
        </div>

        <div class="p-2.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800/80 col-span-2">
          <span class="text-[10px] text-slate-400 block font-sans">INTEGRASI KLASTER</span>
          <span class="font-bold text-slate-900 dark:text-white text-xs truncate block">
            {{ clusterStore.selectedNodeModal.integration }}
          </span>
        </div>
      </div>

      <!-- Capabilities Tags -->
      <div class="space-y-1.5">
        <span class="text-[10px] font-mono text-slate-400 uppercase tracking-wider block font-sans">KAPABILITAS & FITUR NODE</span>
        <div class="flex flex-wrap gap-1.5">
          <span
            v-for="(cap, idx) in clusterStore.selectedNodeModal.capabilities"
            :key="idx"
            class="text-[10px] font-mono px-2 py-0.5 rounded-md bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 border border-indigo-200/80 dark:border-indigo-800/60"
          >
            {{ cap }}
          </span>
        </div>
      </div>

      <!-- Private Subnet IP & Terminal Access (Guarded) -->
      <div class="p-3.5 rounded-xl bg-indigo-50/70 dark:bg-indigo-950/40 border border-indigo-100 dark:border-indigo-900/50 space-y-2.5">
        
        <!-- IP Address Row -->
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-1.5 text-xs font-mono">
            <span class="text-slate-500 dark:text-slate-400 text-[11px]">IP INTERNAL:</span>
            <span class="font-bold" :class="authStore.canAccessSubnets && clusterStore.showDevDetails ? 'text-indigo-600 dark:text-indigo-300' : 'text-slate-400 dark:text-slate-500'">
              {{ authStore.canAccessSubnets && clusterStore.showDevDetails ? clusterStore.selectedNodeModal.internalIp : clusterStore.selectedNodeModal.maskedIp }}
            </span>
          </div>

          <!-- Copy IP Button (Enabled for Superadmin only when revealed) -->
          <button
            v-if="authStore.canAccessSubnets && clusterStore.showDevDetails"
            @click="copyToClipboard(clusterStore.selectedNodeModal.internalIp, 'ip')"
            class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-white dark:bg-slate-900 border border-indigo-200 dark:border-indigo-800 text-[10px] font-mono text-indigo-600 dark:text-indigo-300 hover:bg-indigo-50 dark:hover:bg-indigo-950 cursor-pointer transition-colors"
          >
            <Check v-if="copiedIp" class="w-3 h-3 text-emerald-500" />
            <Copy v-else class="w-3 h-3" />
            <span>{{ copiedIp ? 'Tersalin' : 'Salin' }}</span>
          </button>
        </div>

        <!-- Superadmin SSH Command Helper -->
        <div v-if="authStore.isSuperadmin" class="pt-2 border-t border-indigo-100 dark:border-indigo-900/60 space-y-1">
          <div class="flex items-center justify-between text-[10px] font-mono">
            <span class="text-slate-500 dark:text-slate-400 flex items-center gap-1">
              <Terminal class="w-3 h-3 text-indigo-500" />
              <span>SSH Direct Command:</span>
            </span>
            <span class="text-rose-500 font-bold text-[9px]">Superadmin</span>
          </div>

          <div class="flex items-center justify-between p-2 rounded-lg bg-slate-900 text-slate-100 font-mono text-xs">
            <span class="text-sky-300 truncate select-all text-[11px]">{{ sshCommand }}</span>
            <button
              @click="copyToClipboard(sshCommand, 'ssh')"
              class="p-1 rounded bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white transition-colors cursor-pointer shrink-0 ml-2"
              title="Salin perintah SSH"
            >
              <Check v-if="copiedSsh" class="w-3 h-3 text-emerald-400" />
              <Copy v-else class="w-3 h-3" />
            </button>
          </div>
        </div>

        <!-- Non-Superadmin Policy Notice -->
        <div v-else class="flex items-center gap-2 text-[11px] text-slate-500 dark:text-slate-400">
          <Lock class="w-3.5 h-3.5 text-amber-500 shrink-0" />
          <span>IP privat & perintah SSH diamankan di bawah proteksi Zero-Trust RBAC.</span>
        </div>

      </div>

      <!-- Modal Footer -->
      <div class="flex items-center justify-between pt-1 text-[11px] text-slate-400 font-mono">
        <span>Node ID: {{ clusterStore.selectedNodeModal.id }}</span>
        <button
          @click="clusterStore.closeNodeModal()"
          class="px-3.5 py-1.5 rounded-xl bg-slate-100 dark:bg-slate-900 hover:bg-slate-200 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 font-sans font-semibold text-xs cursor-pointer transition-colors"
        >
          Tutup
        </button>
      </div>

    </div>
  </Dialog>
</template>
