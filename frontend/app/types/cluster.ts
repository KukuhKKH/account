import type { Component } from 'vue'

export interface ClusterNode {
  id: string
  name: string
  type: string
  zone: string
  internalIp: string
  maskedIp: string
  role: string
  stack: string
  ports: string
  capabilities: string[]
  integration: string
  status: 'online' | 'warning' | 'standby'
  ping: string
  icon?: Component
  color: string
}

export interface EcosystemService {
  id: string
  name: string
  identifier: string
  url?: string
  isPublic: boolean
  desc: string
  category: 'Infrastructure' | 'Identity' | 'AI & Automation' | 'Storage' | 'Development'
  status: 'Operational' | 'Secured' | 'Standby'
  latency: string
  badge: string
}

export interface BenchmarkMetric {
  title: string
  hypervel: string
  laravelFpm: string
  improvement: string
  metric: string
  isBetter: boolean
}
