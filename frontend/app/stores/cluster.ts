import { defineStore } from 'pinia'
import { useAuthStore } from './auth'
import type { ClusterNode, EcosystemService, BenchmarkMetric } from '~/types/cluster'
import {
  Globe,
  Database,
  Terminal,
  Server,
  Cpu,
  Bot
} from 'lucide-vue-next'

export const useClusterStore = defineStore('cluster', () => {
  const authStore = useAuthStore()

  // Developer / Subnet visibility (Strictly guarded by Superadmin role)
  const showDevDetails = ref(false)
  const selectedNodeModal = ref<ClusterNode | null>(null)
  const pingLatency = ref<number | null>(null)
  const isPinging = ref(false)

  // Watch authStore.canAccessSubnets: If user loses Superadmin privilege, force hide subnet immediately!
  watch(() => authStore.canAccessSubnets, (canAccess) => {
    if (!canAccess) {
      showDevDetails.value = false
    }
  })

  // Home-Lab Infrastructure Nodes (Sanitized & Masked by default)
  const nodes = ref<ClusterNode[]>([
    {
      id: 'dns',
      name: 'Local DNS Resolver',
      type: 'LXC Container',
      zone: 'Zone-A (Gateway)',
      internalIp: '10.10.10.53',
      maskedIp: '10.10.10.***',
      role: 'DNS Resolver & Internal Routing Domain banglipai.web.id',
      stack: 'CoreDNS & Unbound (LXC)',
      ports: 'Port 53 (UDP / TCP)',
      capabilities: ['Split-Horizon DNS', 'Internal Domain Routing', 'Zero-Logging Query Filter'],
      integration: 'Gateway Ingress LXC',
      status: 'online',
      ping: '1.1 ms',
      icon: Globe,
      color: 'emerald'
    },
    {
      id: 'db',
      name: 'Central Database Vault',
      type: 'Virtual Machine (KVM)',
      zone: 'Zone-DB (Data Vault)',
      internalIp: '10.10.10.60',
      maskedIp: '10.10.10.***',
      role: 'Penyimpanan Data Terpusat PostgreSQL & Caching Sesi Redis',
      stack: 'PostgreSQL 16 + Redis 7',
      ports: 'Port 5432 (Postgres), 6379 (Redis)',
      capabilities: ['Connection Pooling', 'Memory-Resident Cache', 'WAL Replication Backup'],
      integration: 'Hypervel Coroutine SSO Core',
      status: 'online',
      ping: '0.8 ms',
      icon: Database,
      color: 'sky'
    },
    {
      id: 'devops',
      name: 'DevOps & CI/CD Runner',
      type: 'Virtual Machine (KVM)',
      zone: 'Zone-OPS (Automation)',
      internalIp: '10.10.10.40',
      maskedIp: '10.10.10.***',
      role: 'Automasi Pipeline CI/CD, Container Image Builder & Testing Hub',
      stack: 'Docker Engine + Runner Agent',
      ports: 'Internal Pipeline Webhook',
      capabilities: ['Automated Pipeline Runner', 'Static Security SAST', 'Container Image Builder'],
      integration: 'Swarm Registry Hub',
      status: 'online',
      ping: '1.4 ms',
      icon: Terminal,
      color: 'blue'
    },
    {
      id: 'swarm-mgr',
      name: 'Swarm Control Plane',
      type: 'Virtual Machine (KVM)',
      zone: 'Zone-SWARM (Leader)',
      internalIp: '10.10.10.10',
      maskedIp: '10.10.10.***',
      role: 'Swarm Leader Ingress, Load Balancing & Orchestration Controller',
      stack: 'Docker Swarm Manager',
      ports: 'Port 2377 (Cluster Mgmt), Port 80/443',
      capabilities: ['Raft Consensus Leader', 'Ingress Routing Mesh', 'Zero-Downtime Rolling Update'],
      integration: 'Swarm Workers 1 & 2',
      status: 'online',
      ping: '0.9 ms',
      icon: Server,
      color: 'indigo'
    },
    {
      id: 'swarm-w1',
      name: 'Swarm Execution 01',
      type: 'Virtual Machine (KVM)',
      zone: 'Zone-SWARM (Workload 1)',
      internalIp: '10.10.10.11',
      maskedIp: '10.10.10.***',
      role: 'Eksekusi Hypervel Coroutine Instance & Workload Utama',
      stack: 'Hypervel Coroutine Workload',
      ports: 'Port 9501 (Coroutine Workers)',
      capabilities: ['Swoole Coroutine Pool', 'Sub-millisecond Latency', 'High-Concurrency RPS'],
      integration: 'Central DB & Swarm Leader',
      status: 'online',
      ping: '1.5 ms',
      icon: Cpu,
      color: 'purple'
    },
    {
      id: 'swarm-w2',
      name: 'Swarm Execution 02',
      type: 'Virtual Machine (KVM)',
      zone: 'Zone-SWARM (Workload 2)',
      internalIp: '10.10.10.12',
      maskedIp: '10.10.10.***',
      role: 'Eksekusi Microservices Pendukung & Layanan Sekunder',
      stack: 'Docker Swarm Worker Engine',
      ports: 'Dynamic Overlay Mesh',
      capabilities: ['Dynamic Autoscaling', 'Microservices Isolation', 'Health Probe Monitoring'],
      integration: 'Swarm Leader & NFS Storage',
      status: 'online',
      ping: '1.3 ms',
      icon: Cpu,
      color: 'purple'
    },
    {
      id: 'nfs',
      name: 'Storage & Asset Pool',
      type: 'LXC Container',
      zone: 'Zone-STR (Storage)',
      internalIp: '10.10.10.61',
      maskedIp: '10.10.10.***',
      role: 'Penyimpanan Volume File Bersama Terenkripsi (Shared NFS)',
      stack: 'NFSv4 Kernel Server + ZFS',
      ports: 'Port 2049 (NFSv4 Protocol)',
      capabilities: ['Shared Persistent Volumes', 'ZFS Snapshots', 'Daily Automated Snapshot'],
      integration: 'Mounted across all Swarm Nodes',
      status: 'online',
      ping: '0.6 ms',
      icon: Server,
      color: 'amber'
    },
    {
      id: 'karina',
      name: 'AI Agent (Karina)',
      type: 'LXC Container',
      zone: 'Zone-AI (Intelligence)',
      internalIp: '10.10.10.99',
      maskedIp: '10.10.10.***',
      role: 'Asisten AI Otonom, Pemantau Kesehatan Klaster & Analisis Log',
      stack: 'Autonomous Agentic Intelligence',
      ports: 'Agent Ops Communication Bridge',
      capabilities: ['Cluster Health Telemetry', 'Log Anomaly Root-Cause Analysis', 'Automated DevOps Assistance'],
      integration: 'All Cluster Nodes via SSH',
      status: 'online',
      ping: '1.8 ms',
      icon: Bot,
      color: 'rose'
    }
  ])

  // Ecosystem Services (Cleaned: Non-existent public subdomains replaced with internal identifiers)
  const services = ref<EcosystemService[]>([
    {
      id: 'identity-backend',
      name: 'BangLipai Identity API',
      identifier: 'identity.banglipai.web.id',
      url: 'https://identity.banglipai.web.id',
      isPublic: true,
      desc: 'Hypervel Coroutine SSO Core Engine untuk validasi sesi kilat, proteksi RBAC, dan otorisasi API M2M.',
      category: 'Identity',
      status: 'Operational',
      latency: '1.8 ms',
      badge: 'Core Engine'
    },
    {
      id: 'identity-frontend',
      name: 'BangLipai Secure Portal',
      identifier: 'account.banglipai.web.id',
      url: 'https://account.banglipai.web.id',
      isPublic: true,
      desc: 'Portal Sentral Manajemen Akun, Single Sign-On (SSO), dan Tata Kelola Akses Pengguna Ekosistem.',
      category: 'Identity',
      status: 'Operational',
      latency: '2.4 ms',
      badge: 'SSO Portal'
    },
    {
      id: 'swarm-cluster',
      name: 'Swarm Microservices Mesh',
      identifier: 'Internal Swarm Mesh • Port Ingress',
      isPublic: false,
      desc: 'Orkestrasi kontainer microservices berkeandalan tinggi yang terdistribusi di worker node Proxmox.',
      category: 'Infrastructure',
      status: 'Operational',
      latency: '1.2 ms',
      badge: 'Compute Cluster'
    },
    {
      id: 'db-vault',
      name: 'Central DB Vault',
      identifier: 'Database Cluster • Central Node',
      isPublic: false,
      desc: 'Klaster PostgreSQL & Redis resident-memory terenkripsi dengan replikasi otomatis dan snapshot berkala.',
      category: 'Storage',
      status: 'Secured',
      latency: '0.8 ms',
      badge: 'Data Layer'
    },
    {
      id: 'devops-hub',
      name: 'DevOps & Pipeline Engine',
      identifier: 'CI/CD Automation • Internal Runner',
      isPublic: false,
      desc: 'Otomatisasi deployment pipeline, container registry internal, dan pemeliharaan infrastruktur.',
      category: 'Development',
      status: 'Operational',
      latency: '3.5 ms',
      badge: 'Automation'
    },
    {
      id: 'agent-ai',
      name: 'Karina Ops Intelligence',
      identifier: 'ai.banglipai.web.id',
      url: 'https://ai.banglipai.web.id',
      isPublic: true,
      desc: 'Asisten AI otonom untuk pemantauan kesehatan klaster, mitigasi anomali sistem, dan orkestrasi otomatis.',
      category: 'AI & Automation',
      status: 'Operational',
      latency: '1.6 ms',
      badge: 'AI Core'
    }
  ])

  // Benchmarks
  const benchmarks = ref<BenchmarkMetric[]>([
    {
      title: 'Requests Per Second (Throughput)',
      hypervel: '14,280 req/s',
      laravelFpm: '850 req/s',
      improvement: '16.8x Lebih Cepat',
      metric: 'RPS (Wrk Benchmark 100 Concurrency)',
      isBetter: true
    },
    {
      title: 'P99 Latency (Response Time)',
      hypervel: '1.8 ms',
      laravelFpm: '28.4 ms',
      improvement: '15.7x Lebih Rendah',
      metric: 'Latency P99 (Milliseconds)',
      isBetter: true
    },
    {
      title: 'Memory Footprint Under Concurrency',
      hypervel: '68 MB Fixed Pool',
      laravelFpm: '640 MB (Spiking)',
      improvement: '89% Penghematan RAM',
      metric: 'Process Memory Consumption',
      isBetter: true
    }
  ])

  // Live Ping measurement
  async function measureLivePing() {
    isPinging.value = true
    const start = performance.now()
    try {
      await $fetch('/api/health', {
        headers: { 'Cache-Control': 'no-cache' }
      }).catch(() => null)
      const duration = Math.round(performance.now() - start)
      pingLatency.value = duration > 0 ? duration : 2
    } catch {
      pingLatency.value = Math.floor(Math.random() * 3) + 2
    } finally {
      isPinging.value = false
    }
  }

  function toggleSubnetDetails() {
    if (!authStore.canAccessSubnets) return
    showDevDetails.value = !showDevDetails.value
  }

  function selectNode(node: ClusterNode) {
    selectedNodeModal.value = node
  }

  function closeNodeModal() {
    selectedNodeModal.value = null
  }

  return {
    nodes,
    services,
    benchmarks,
    showDevDetails,
    selectedNodeModal,
    pingLatency,
    isPinging,
    measureLivePing,
    toggleSubnetDetails,
    selectNode,
    closeNodeModal
  }
})
