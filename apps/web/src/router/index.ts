import { createRouter, createWebHistory } from 'vue-router'

const DashboardView = () => import('../views/DashboardView.vue')
const SignalsView = () => import('../views/SignalsView.vue')
const SignalDetailView = () => import('../views/SignalDetailView.vue')
const WatchlistsView = () => import('../views/WatchlistsView.vue')
const BotsView = () => import('../views/BotsView.vue')
const NotificationsView = () => import('../views/NotificationsView.vue')
const AnalyticsView = () => import('../views/AnalyticsView.vue')

const appName = 'SignalCore'

const router = createRouter({
  history: createWebHistory(),
  routes: [
    { path: '/', redirect: '/dashboard' },
    { path: '/dashboard', name: 'dashboard', component: DashboardView, meta: { title: 'Dashboard' } },
    { path: '/signals', name: 'signals', component: SignalsView, meta: { title: 'Signals' } },
    { path: '/signals/:id', name: 'signal-detail', component: SignalDetailView, meta: { title: 'Signal Detail' } },
    { path: '/watchlists', name: 'watchlists', component: WatchlistsView, meta: { title: 'Watchlists' } },
    { path: '/bots', name: 'bots', component: BotsView, meta: { title: 'Bots' } },
    { path: '/notifications', name: 'notifications', component: NotificationsView, meta: { title: 'Notifications' } },
    { path: '/analytics', name: 'analytics', component: AnalyticsView, meta: { title: 'Analytics' } },
  ],
})

router.afterEach((to) => {
  const pageTitle = typeof to.meta.title === 'string' ? to.meta.title : ''
  document.title = pageTitle ? appName + ' | ' + pageTitle : appName
})

export default router
