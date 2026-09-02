<script setup>
import { useAuthStore } from '@/stores/auth'
import { computed, onMounted, ref } from 'vue'

const authStore = useAuthStore()

const exams = ref([])
const isLoading = ref(false)
const errorMessage = ref('')
const detailDialog = ref(false)
const selectedExam = ref(null)
const questionsList = ref([])

const getCsrfHeaders = () => {
  const metaToken = document.querySelector('meta[name="csrf-token"]')?.content ?? ''
  const xsrfCookie = document.cookie
    .split('; ')
    .find(row => row.startsWith('XSRF-TOKEN='))
    ?.split('=')[1]

  const xsrfToken = xsrfCookie ? decodeURIComponent(xsrfCookie) : ''

  return {
    Accept: 'application/json',
    ...(metaToken ? { 'X-CSRF-TOKEN': metaToken } : {}),
    ...(xsrfToken ? { 'X-XSRF-TOKEN': xsrfToken } : {}),
  }
}

const request = async (url, options = {}) => {
  const response = await fetch(url, {
    credentials: 'same-origin',
    ...options,
    headers: {
      ...getCsrfHeaders(),
      ...options.headers,
    },
  })

  if (response.status === 204)
    return null

  const data = await response.json()

  if (!response.ok)
    throw new Error(Object.values(data.errors ?? {})[0]?.[0] ?? data.message ?? 'The request could not be completed.')

  return data
}

const getFreshCsrfToken = async () => {
  const response = await fetch('/csrf-token', {
    credentials: 'same-origin',
    headers: { Accept: 'application/json' },
  })

  if (!response.ok)
    throw new Error('Unable to refresh the security token.')

  const { token } = await response.json()

  return token
}

const loadExams = async () => {
  isLoading.value = true
  errorMessage.value = ''

  try {
    const token = await getFreshCsrfToken()

    exams.value = await request('/api/laporan-user', {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': token,
      },
    })
  }
  catch (error) {
    errorMessage.value = error.message
  }
  finally {
    isLoading.value = false
  }
}

const totalExams = computed(() => exams.value.length)
const scheduledExams = computed(() => exams.value.filter(item => String(item.status).toLowerCase() === 'scheduled').length)
const ongoingExams = computed(() => exams.value.filter(item => String(item.status).toLowerCase() === 'ongoing').length)
const completedExams = computed(() => exams.value.filter(item => String(item.status).toLowerCase() === 'completed').length)

const statusColor = status => {
  switch (String(status).toLowerCase()) {
    case 'scheduled':
      return 'primary'
    case 'ongoing':
      return 'warning'
    case 'completed':
      return 'success'
    default:
      return 'secondary'
  }
}

const openDetailDialog = async (exam) => {
  selectedExam.value = exam
  detailDialog.value = true
  isLoading.value = true
  errorMessage.value = ''

  try {
    const token = await getFreshCsrfToken()

    const ql = await request('/api/hasil-ujian', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': token,
      },
      body: JSON.stringify({ exam_id: exam.exam_id ?? exam.id, siswa_id: authStore.user?.id ?? null }),
    })
    questionsList.value = ql.result ?? []
  }
  catch (error) {
    errorMessage.value = error.message
  }
  finally {
    isLoading.value = false
  }
}

const headers = [
  { title: 'No', key: 'no' },
  { title: 'Judul', key: 'title' },
  { title: 'Tanggal Ujian', key: 'exam_date' },
  { title: 'Durasi', key: 'duration' },
  { title: 'Jumlah Soal', key: 'count_qa' },
  { title: 'Total Nilai', key: 'total_score' },
  { title: 'Status', key: 'status' },
  { title: 'Aksi', key: 'actions', sortable: false, align: 'end' },
]

onMounted(loadExams)
</script>

<template>
  <VRow>
    <VCol cols="12">
      <VCard title="Laporan Ujian">
        <VCardText v-if="errorMessage">
          <VAlert type="error">
            {{ errorMessage }}
          </VAlert>
        </VCardText>

        <VRow class="px-4 pb-2">
          <VCol
            cols="12"
            md="3"
          >
            <VCard color="primary" variant="tonal">
              <VCardText>
                <div class="text-caption">Total Ujian</div>
                <div class="text-h4 font-weight-bold">
                  {{ totalExams }}
                </div>
              </VCardText>
            </VCard>
          </VCol>

          <VCol
            cols="12"
            md="3"
          >
            <VCard color="info" variant="tonal">
              <VCardText>
                <div class="text-caption">Scheduled</div>
                <div class="text-h4 font-weight-bold">
                  {{ scheduledExams }}
                </div>
              </VCardText>
            </VCard>
          </VCol>

          <VCol
            cols="12"
            md="3"
          >
            <VCard color="warning" variant="tonal">
              <VCardText>
                <div class="text-caption">Ongoing</div>
                <div class="text-h4 font-weight-bold">
                  {{ ongoingExams }}
                </div>
              </VCardText>
            </VCard>
          </VCol>

          <VCol
            cols="12"
            md="3"
          >
            <VCard color="success" variant="tonal">
              <VCardText>
                <div class="text-caption">Completed</div>
                <div class="text-h4 font-weight-bold">
                  {{ completedExams }}
                </div>
              </VCardText>
            </VCard>
          </VCol>
        </VRow>
      </VCard>
    </VCol>

    <VCol cols="12">
      <VCard>
        <VCardText>
          <VDataTable
            :headers="headers"
            :items="exams"
            :loading="isLoading"
            item-value="id"
          >
            <template #item.no="{ index }">
              {{ index + 1 }}
            </template>
            <template #item.status="{ item }">
              <VChip
                :color="statusColor(item.status)"
                size="small"
                variant="tonal"
              >
                {{ item.status }}
              </VChip>
            </template>
            <template #item.actions="{ item }">
              <VBtn
                icon="ri-eye-line"
                size="small"
                variant="text"
                @click="openDetailDialog(item)"
              />
            </template>
          </VDataTable>
        </VCardText>
        
      </VCard>
    </VCol>
  </VRow>

  <VDialog
    v-model="detailDialog"
    max-width="560"
  >
    <VCard title="Detail Hasil Ujian">
      <VCardText v-if="selectedExam">
        <VRow>
          <VCol cols="12" md="6">
            <VListItem title="Judul" :subtitle="selectedExam.title" />
          </VCol>
          <VCol cols="12" md="6">
            <VListItem title="Tanggal Ujian" :subtitle="selectedExam.exam_date" />
          </VCol>
          <VCol cols="12" md="6">
            <VListItem title="Durasi" :subtitle="`${selectedExam.duration} menit`" />
          </VCol>
          <VCol cols="12" md="6">
            <VListItem title="Jumlah Soal" :subtitle="String(selectedExam.count_qa)" />
          </VCol>
          <VCol cols="12" md="6">
            <VListItem title="Total Nilai" :subtitle="String(selectedExam.total_score)" />
          </VCol>
          <VCol cols="12" md="6">
            <VListItem title="Status" :subtitle="selectedExam.status" />
          </VCol>
        </VRow>
      </VCardText>
      <VCardText v-if="questionsList.length">
        <VDataTable
          :headers="[
            { title: 'No', key: 'no' },
            { title: 'Pertanyaan', key: 'question_text' },
            { title: 'Jawaban', key: 'answer_text' },
            { title: 'Nilai', key: 'score' },
          ]"
          :items="questionsList"
          :loading="isLoading"
          item-value="id"
        >
          <template #item.no="{ index }">
            {{ index + 1 }}
          </template>
        </VDataTable>  
      </VCardText>
      <VCardActions class="justify-end">
        <VBtn
          variant="tonal"
          @click="detailDialog = false"
        >
          Tutup
        </VBtn>
      </VCardActions>
    </VCard>
  </VDialog>
</template>
