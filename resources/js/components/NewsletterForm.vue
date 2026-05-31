<script setup lang="ts">
import { ref } from 'vue'
import { PaperAirplaneIcon } from '@heroicons/vue/24/outline'
import { useI18n } from '@/composables/useI18n'

const { t } = useI18n()

const email = ref('')
const name = ref('')
const loading = ref(false)
const success = ref(false)
const error = ref('')

const subscribe = async () => {
  if (!email.value) {
    error.value = t('nlNeedEmail')
    return
  }

  loading.value = true
  error.value = ''
  success.value = false

  try {
    const response = await fetch('/api/newsletter/subscribe', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
      body: JSON.stringify({
        email: email.value,
        name: name.value || null,
      }),
    })

    const data = await response.json()

    if (response.ok) {
      success.value = true
      email.value = ''
      name.value = ''
      setTimeout(() => {
        success.value = false
      }, 5000)
    } else {
      error.value = data.message || 'Ocorreu um erro. Tente novamente.'
    }
  } catch {
    error.value = t('nlServerError')
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="mx-auto w-full max-w-md">
    <form @submit.prevent="subscribe" class="space-y-4">
      <!-- Success Message -->
      <div
        v-if="success"
        class="rounded-xl bg-green-500/10 border border-green-500/30 p-4 text-center text-green-400"
      >
        {{ t('nlSuccess') }}
      </div>

      <!-- Error Message -->
      <div
        v-if="error"
        class="rounded-xl bg-red-500/10 border border-red-500/30 p-4 text-center text-red-400"
      >
        {{ error }}
      </div>

      <!-- Input Group -->
      <div class="flex flex-col gap-3 sm:flex-row">
        <input
          v-model="email"
          type="email"
          :placeholder="t('nlPlaceholder')"
          required
          class="flex-1 rounded-xl border border-white/20 bg-white/5 px-4 py-3 text-white placeholder-gray-500 backdrop-blur-sm transition-all focus:border-blue-500/50 focus:outline-none focus:ring-2 focus:ring-blue-500/20"
        />
        <button
          type="submit"
          :disabled="loading"
          class="group flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-3 font-semibold text-white transition-all hover:from-blue-700 hover:to-blue-800 disabled:cursor-not-allowed disabled:opacity-50"
        >
          <span>{{ loading ? t('nlSending') : t('nlSubscribe') }}</span>
          <PaperAirplaneIcon
            class="h-5 w-5 transition-transform group-hover:translate-x-0.5 group-hover:-translate-y-0.5"
          />
        </button>
      </div>

      <p class="text-center text-xs text-gray-500">
        {{ t('nlConsent') }}
      </p>
    </form>
  </div>
</template>
