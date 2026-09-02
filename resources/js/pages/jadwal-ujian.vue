<script setup>

/* eslint-disable camelcase */

const emptyExam = () => ({
  title: '',
  start_time: '',
  end_time: '',
  duration: '',
  count_qa: '',
  status: '',
})

const headers = [
  { title: 'No', key: 'id' },
  { title: 'Title', key: 'title' },
  { title: 'Start Time', key: 'start_time' },
  { title: 'End Time', key: 'end_time' },
  { title: 'Duration', key: 'duration' },
  { title: 'Status', key: 'status' },
  { title: 'Actions', key: 'actions', sortable: false, align: 'end', width: '120px' },
]

const exams = ref([])
const exam = ref(emptyExam())
const editingId = ref(null)
const dialog = ref(false)
const isLoading = ref(false)
const isSaving = ref(false)
const errorMessage = ref('')

const categories = ref([])


const isEditing = computed(() => editingId.value !== null)
const dialogTitle = computed(() => isEditing.value ? 'Edit exam' : 'Add exam')

const request = async (url, options = {}) => {
  const response = await fetch(url, {
    credentials: 'same-origin',
    ...options,
    headers: {
      Accept: 'application/json',
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
    exams.value = await request('/api/exams')
  }
  catch (error) {
    errorMessage.value = error.message
  }
  finally {
    isLoading.value = false
  }
}

const loadCategories = async () => {
  isLoading.value = true
  errorMessage.value = ''

  try {
    categories.value = await request('/api/categories')
  }
  catch (error) {
    errorMessage.value = error.message
  }
  finally {
    isLoading.value = false
  }
}

const openCreateDialog = () => {
  editingId.value = null
  exam.value = emptyExam()
  errorMessage.value = ''
  dialog.value = true
}

const openEditDialog = item => {

  editingId.value = item.id
  // console.log(item.id)
  exam.value = {
    title: item.title,
    start_time: item.start_time,
    end_time: item.end_time,
    duration: item.duration,
    count_qa: item.count_qa,
    status: item.status,
  }

  errorMessage.value = ''
  dialog.value = true
}

const saveExam = async () => {
  isSaving.value = true
  errorMessage.value = ''

  try {
    const url = isEditing.value ? `/api/exams/${editingId.value}` : '/api/exams'
    const method = isEditing.value ? 'PUT' : 'POST'
    const token = await getFreshCsrfToken()
    const savedExam = await request(url, {
      method,
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': token,
      },
      body: JSON.stringify(exam.value),
    })

    if (isEditing.value) {
      const index = exams.value.findIndex(item => item.id === savedExam.id)

      exams.value.splice(index, 1, savedExam)
    }
    else {
      exams.value.unshift(savedExam)
    }

    dialog.value = false
  }
  catch (error) {
    errorMessage.value = error.message
  }
  finally {
    isSaving.value = false
  }
}

const deleteExam = async item => {
  if (!window.confirm('Delete this exam?'))
    return

  errorMessage.value = ''

  try {
    await request(`/api/exams/${item.id}`, {
      method: 'DELETE',
      headers: {
        'X-CSRF-TOKEN': await getFreshCsrfToken(),
      },
    })
    exams.value = exams.value.filter(exam => exam.id !== item.id)
  }
  catch (error) {
    errorMessage.value = error.message
  }
}


onMounted(() => Promise.all([loadExams(), loadCategories()]))
</script>

<template>
  <VRow>
    <VCol cols="12">
      <VCard title="Jadwal Ujian" class="card-analytics">
        <template #append>
          <VBtn
            prepend-icon="ri-add-line"
            @click="openCreateDialog"
          >
            Add Jadwal Ujian
          </VBtn>
        </template>

        <VCardText v-if="errorMessage">
          <VAlert type="error">
            {{ errorMessage }}
          </VAlert>
        </VCardText>

        <VDataTable
          :headers="headers"
          :items="exams"
          :loading="isLoading"
          item-value="id"
        >
          

          <template #item.actions="{ item }">
            <VBtn
              icon="ri-pencil-line"
              size="small"
              variant="text"
              @click="openEditDialog(item)"
            />
            <VBtn
              color="error"
              icon="ri-delete-bin-line"
              size="small"
              variant="text"
              @click="deleteExam(item)"
            />
          </template>
        </VDataTable>
      </VCard>

      <VDialog
        v-model="dialog"
        max-width="720"
      >
        <VCard :title="dialogTitle">
          <VCardText>
            <VForm @submit.prevent="saveExam">
              <VTextarea
                v-model="exam.title"
                class="mt-4"
                label="Question"
                :rules="[value => !!value || 'Question is required.']"
              />
              <VTextField
                v-model="exam.start_time"
                class="mt-4"
                label="Start Time"
                type="datetime-local"
                :rules="[value => !!value || 'Start Time is required.']"
              />
              <VTextField
                v-model="exam.end_time"
                class="mt-4"
                label="End Time"
                type="datetime-local"
                :rules="[value => !!value || 'End Time is required.']"
              />
              <VTextField
                v-model="exam.duration"
                class="mt-4"
                label="Duration"
                :rules="[value => !!value || 'Duration is required.']"
              />
              <VTextField
                v-model="exam.count_qa"
                class="mt-4"
                label="Count QA"
                :rules="[value => !!value || 'Count QA is required.']"
              />
              <VSelect
                v-model="exam.status"
                class="mt-4"
                label="Status"
                :items="['Scheduled', 'Ongoing', 'Completed']"
                :rules="[value => !!value || 'Status is required.']"
              />
              
            
              <div class="d-flex justify-end gap-3 mt-6">
                <VBtn
                  variant="tonal"
                  @click="dialog = false"
                >
                  Cancel
                </VBtn>
                <VBtn
                  :loading="isSaving"
                  type="submit"
                >
                  Save
                </VBtn>
              </div>
            </VForm>
          </VCardText>
        </VCard>
      </VDialog>
    </VCol>
  </VRow>
</template>
