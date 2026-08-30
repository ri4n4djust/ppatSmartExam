import { createRouter, createWebHistory } from 'vue-router'
import { store } from '@/plugins/pinia'
import { useAuthStore } from '@/stores/auth'
import { routes } from './routes'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes,
})

router.beforeEach(async to => {
  if (!to.matched.some(route => route.meta.requiresAuth))
    return true

  const authStore = useAuthStore(store)

  if (await authStore.restoreSession())
    return true

  return {
    path: '/login',
    query: { redirect: to.fullPath },
  }
})

export default function (app) {
  app.use(router)
}
export { router }
