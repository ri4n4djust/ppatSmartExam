import { defineStore } from 'pinia'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null,
    idUser: null
  }),

  getters: {
    isAuthenticated: state => state.user !== null,
  },

  actions: {
    setUser(user) {
      this.user = user
      this.idUser = user.id
    },

    clearUser() {
      this.user = null
      this.idUser = null
    },

    async restoreSession() {
      try {
        const response = await fetch('/auth/user', {
          headers: {
            Accept: 'application/json',
          },
          credentials: 'same-origin',
        })

        if (!response.ok) {
          this.clearUser()

          return false
        }

        const { user } = await response.json()

        this.setUser(user)

        return true
      }
      catch {
        this.clearUser()

        return false
      }
    },
  },
})
