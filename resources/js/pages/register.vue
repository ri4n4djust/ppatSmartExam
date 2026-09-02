<script setup>
import { useAuthStore } from '@/stores/auth'
import AuthProvider from '@/views/pages/authentication/AuthProvider.vue'
import logo from '@images/logoippat.png'
import authV1MaskDark from '@images/pages/auth-v1-mask-dark.png'
import authV1MaskLight from '@images/pages/auth-v1-mask-light.png'
import authV1Tree2 from '@images/pages/auth-v1-tree-2.png'
import authV1Tree from '@images/pages/auth-v1-tree.png'
import { useTheme } from 'vuetify'

const form = ref({
  username: '',
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  privacyPolicies: false,
})

const router = useRouter()
const authStore = useAuthStore()
const errorMessage = ref('')
const isLoading = ref(false)

const vuetifyTheme = useTheme()

const authThemeMask = computed(() => {
  return vuetifyTheme.global.name.value === 'light' ? authV1MaskLight : authV1MaskDark
})

const isPasswordVisible = ref(false)

const getCsrfHeaders = () => {
  const metaToken = document.querySelector('meta[name="csrf-token"]')?.content ?? ''

  return {
    Accept: 'application/json',
    'Content-Type': 'application/json',
    ...(metaToken ? { 'X-CSRF-TOKEN': metaToken } : {}),
  }
}

const register = async () => {
  errorMessage.value = ''
  isLoading.value = true

  try {
    const response = await fetch('/index.php/auth/register', {
      method: 'POST',
      headers: getCsrfHeaders(),
      credentials: 'same-origin',
      body: JSON.stringify(form.value),
    })
    const responseText = await response.text()
    let data

    try {
      data = JSON.parse(responseText)
    }
    catch {
      throw new Error(`Server returned an invalid response (${response.status}).`)
    }

    if (!response.ok)
      throw new Error(Object.values(data.errors ?? {})[0]?.[0] ?? data.message ?? 'Unable to create the account.')

    authStore.setUser(data.user)

    await router.push('/dashboard')
  }
  catch (error) {
    errorMessage.value = error.message
  }
  finally {
    isLoading.value = false
  }
}
</script>

<template>
  <!-- eslint-disable vue/no-v-html -->

  <div class="auth-wrapper d-flex align-center justify-center pa-4">
    <VCard
      class="auth-card pa-4 pt-7"
      max-width="448"
    >
      <VCardItem class="justify-center">
        <RouterLink
          to="/"
          class="d-flex align-center gap-3"
        >
          <!-- eslint-disable vue/no-v-html -->
          <img
            :src="logo"
            alt="PPAT SMART EXAM logo"
            class="app-logo-image login-logo"
          >
          <!-- <h2 class="font-weight-medium text-2xl text-uppercase">
            PPAT SMART EXAM
          </h2> -->
        </RouterLink>
      </VCardItem>

      <VCardText class="pt-2">
        <h4 class="text-h4 mb-1">
          Welcome to PPAT SMART EXAM! 👋🏻
        </h4>
        <p class="mb-0">
          Make your app management easy and fun!
        </p>
      </VCardText>

      <VCardText>
        <VForm @submit.prevent="register">
          <VRow>
            <VCol
              v-if="errorMessage"
              cols="12"
            >
              <VAlert type="error" density="compact">
                {{ errorMessage }}
              </VAlert>
            </VCol>
            <!-- Username -->
            <VCol cols="12">
              <VTextField
                v-model="form.username"
                label="Username"
                placeholder="Johndoe"
              />
            </VCol>
            <!-- Name -->
            <VCol cols="12">
              <VTextField
                v-model="form.name"
                label="Nama"
                placeholder="Nama lengkap"
              />
            </VCol>
            <!-- email -->
            <VCol cols="12">
              <VTextField
                v-model="form.email"
                label="Email"
                placeholder="johndoe@email.com"
                type="email"
              />
            </VCol>

            <!-- password -->
            <VCol cols="12">
              <VTextField
                v-model="form.password"
                label="Password"
                placeholder="············"
                :type="isPasswordVisible ? 'text' : 'password'"
                autocomplete="password"
                :append-inner-icon="isPasswordVisible ? 'ri-eye-off-line' : 'ri-eye-line'"
                @click:append-inner="isPasswordVisible = !isPasswordVisible"
              />
              <VTextField
                v-model="form.password_confirmation"
                class="mt-4"
                label="Confirm password"
                :type="isPasswordVisible ? 'text' : 'password'"
                autocomplete="new-password"
              />
              <div class="d-flex align-center my-6">
                <VCheckbox
                  id="privacy-policy"
                  v-model="form.privacyPolicies"
                  inline
                />
                <VLabel
                  for="privacy-policy"
                  style="opacity: 1;"
                >
                  <span class="me-1">I agree to</span>
                  <a
                    href="javascript:void(0)"
                    class="text-primary"
                  >privacy policy & terms</a>
                </VLabel>
              </div>

              <VBtn
                block
                type="submit"
                :loading="isLoading"
              >
                Sign up
              </VBtn>
            </VCol>

            <!-- login instead -->
            <VCol
              cols="12"
              class="text-center text-base"
            >
              <span>Already have an account?</span>
              <RouterLink
                class="text-primary ms-2"
                to="login"
              >
                Sign in instead
              </RouterLink>
            </VCol>

            <VCol
              cols="12"
              class="d-flex align-center"
            >
              <VDivider />
              <span class="mx-4">or</span>
              <VDivider />
            </VCol>

            <!-- auth providers -->
            <VCol
              cols="12"
              class="text-center"
            >
              <AuthProvider />
            </VCol>
          </VRow>
        </VForm>
      </VCardText>
    </VCard>

    <VImg
      class="auth-footer-start-tree d-none d-md-block"
      :src="authV1Tree"
      :width="250"
    />

    <VImg
      :src="authV1Tree2"
      class="auth-footer-end-tree d-none d-md-block"
      :width="350"
    />

    <!-- bg img -->
    <VImg
      class="auth-footer-mask d-none d-md-block"
      :src="authThemeMask"
    />
  </div>
</template>

<style lang="scss">
@use "@core-scss/template/pages/page-auth";
.login-logo {
  block-size: 4rem;
  inline-size: 4rem;
  object-fit: contain;
}
</style>
