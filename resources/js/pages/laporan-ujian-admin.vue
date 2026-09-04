<script setup>
import { useLaporanStore } from '@/stores/laporanStore'

const examsResult = ref([])
// const resultDetail = ref([])
const isLoading = ref(false)
const errorMessage = ref('')
const laporanStore = useLaporanStore()
const detailDialog = ref(false)
const questionsList = ref([])
const selectedExam = ref(null)

const categorySummary = computed(() => {
  const summary = {}

  for (const item of questionsList.value) {
    const categoryName = item.category_name || 'Tanpa Kategori'

    if (!summary[categoryName]) {
      summary[categoryName] = {
        name: categoryName,
        totalQuestions: 0,
        correctAnswers: 0,
      }
    }

    summary[categoryName].totalQuestions += 1

    if (item.is_correct === true || Number(item.is_correct) === 1)
      summary[categoryName].correctAnswers += 1
  }

  return Object.values(summary)
    .map(entry => ({
      ...entry,
      percentage: entry.totalQuestions ? (entry.correctAnswers / entry.totalQuestions) * 100 : 0,
    }))
    .sort((a, b) => a.name.localeCompare(b.name))
})


const getExam = async () => {
  isLoading.value = true
  try {
    const result = await laporanStore.fetchLaporan()
    examsResult.value = result   // simpan ke reactive variable
  } catch (error) {
    console.error('Gagal ambil laporan:', error)
  } finally {
    isLoading.value = false
  }
}
const getDetail = async (exam) => {
  selectedExam.value = exam
  isLoading.value = true
  try {
    const result = await laporanStore.fetchDetail(exam)
    questionsList.value = result.result   // simpan ke reactive variable
    detailDialog.value = true
  } catch (error) {
    console.error('Gagal ambil laporan:', error)
  } finally {
    isLoading.value = false
  }
}
const headers = [
  { title: 'No', key: 'no' },
  { title: 'Siswa', key: 'student_name' },
  { title: 'Email', key: 'student_email' },
  { title: 'Judul', key: 'title' },
  { title: 'Tanggal Ujian', key: 'exam_date' },
  { title: 'Durasi', key: 'duration' },
  { title: 'Jumlah Soal', key: 'count_qa' },
  { title: 'Total Nilai', key: 'total_score' },
  { title: 'Aksi', key: 'actions', sortable: false, align: 'end' },
]
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

onMounted(() => Promise.all([getExam()]))
</script>

<template>
  <VRow>
    <VCol cols="12">
      <VCard>
        <VCardText>
          <VDataTable
            :headers="headers"
            :items="examsResult"
            :loading="isLoading"
            item-value="student_id"
          >
            <template #item.no="{ index }">
              {{ index + 1 }}
            </template>
            <template #item.actions="{ item }">
              <VBtn
                icon="ri-eye-line"
                size="small"
                variant="text"
                @click="getDetail(item)"
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
          <VCol cols="12" md="4">
            <VListItem title="Nama" :subtitle="selectedExam.student_name" />
          </VCol>
          <VCol cols="12" md="4">
            <VListItem title="Judul" :subtitle="selectedExam.title" />
          </VCol>
          <VCol cols="12" md="4">
            <VListItem title="Tanggal Ujian" :subtitle="selectedExam.exam_date" />
          </VCol>
          <VCol cols="12" md="4">
            <VListItem title="Durasi" :subtitle="`${selectedExam.duration} menit`" />
          </VCol>
          <VCol cols="12" md="4">
            <VListItem title="Jumlah Soal" :subtitle="String(selectedExam.count_qa)" />
          </VCol>
          <VCol cols="12" md="4">
            <VListItem title="Total Nilai" :subtitle="String(selectedExam.total_score)" />
          </VCol>
        </VRow>
      </VCardText>

      <VCardText v-if="categorySummary.length">
        <div class="d-flex flex-column gap-3">
          <div
            v-for="category in categorySummary"
            :key="category.name"
            class="rounded border pa-3"
          >
            <div class="d-flex justify-space-between align-center mb-2">
              <strong>{{ category.name }}</strong>
              <span class="text-body-2 font-weight-medium">
                {{ category.percentage.toFixed(1) }}%
              </span>
            </div>

            <VProgressLinear
              :model-value="category.percentage"
              color="primary"
              height="8"
              rounded
            />

            <div class="text-caption mt-2">
              {{ category.correctAnswers }} / {{ category.totalQuestions }} jawaban benar
            </div>
          </div>
        </div>
      </VCardText>

      <VCardText v-if="questionsList.length">
        <VDataTable
          :headers="[
            { title: 'No', key: 'no' },
            // { title: 'Kategori', key: 'category_name' },
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
