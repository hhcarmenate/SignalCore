import { createRouter, createWebHistory } from 'vue-router'

import AnalyticsView from '../views/AnalyticsView.vue'
import BotsView from '../views/BotsView.vue'
import DashboardView from '../views/DashboardView.vue'
import NotificationsView from '../views/NotificationsView.vue'
import SignalsView from '../views/SignalsView.vue'
import WatchlistsView from '../views/WatchlistsView.vue'

const appName = 'SignalCore'

const router = createRouter({
  history: createWebHistory(),
  routes: [
    { path: '/', redirect: '/dashboard' },
    { path: '/dashboard', name: 'dashboard', component: DashboardView, meta: { title: 'Dashboard' } },
    { path: '/signals', name: 'signals', component: SignalsView, meta: { title: 'Signals' } },
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
