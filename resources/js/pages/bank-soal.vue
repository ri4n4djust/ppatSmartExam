<script setup>

/* eslint-disable camelcase */

const emptyQuestion = () => ({
  category: "",
  question: '',
  difficulty: '',
  type: 'mcq',
  options: 
    {
      option_a: 'A.',
      option_b: 'B.',
      option_c: 'C.',
      option_d: 'D.',
    },
  correct_answer: '',
  score: 1,
  score_if_wrong: -1,
})

const headers = [
  { title: 'No', key: 'no' },
  { title: 'Category', key: 'category_id' },
  { title: 'Question', key: 'text' },
  { title: 'Correct answer', key: 'correct_answer', width: '160px' },
  { title: 'Score', key: 'score_value', width: '100px' },
  { title: 'Actions', key: 'actions', sortable: false, align: 'end', width: '120px' },
]

const questions = ref([])
const question = ref(emptyQuestion())
const editingId = ref(null)
const dialog = ref(false)
const isLoading = ref(false)
const isSaving = ref(false)
const errorMessage = ref('')
const search = ref('')
const selectedCategory = ref(null)

const categories = ref([])


const isEditing = computed(() => editingId.value !== null)
const dialogTitle = computed(() => isEditing.value ? 'Edit question' : 'Add question')

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

const loadQuestions = async () => {
  isLoading.value = true
  errorMessage.value = ''

  try {
    questions.value = await request('/api/questions')
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
  question.value = emptyQuestion()
  errorMessage.value = ''
  dialog.value = true
}

const getQuestionOptions = options => {
  if (typeof options === 'string') {
    try {
      return JSON.parse(options)
    }
    catch {
      return emptyQuestion().options
    }
  }

  return options ?? emptyQuestion().options
}

const openEditDialog = item => {

  editingId.value = item.id
  question.value = {
    question: item.text,
    difficulty: item.difficulty,
    type: item.type,
    options: getQuestionOptions(item.options),
    correct_answer: item.correct_answer,
    category: Number(item.category_id),
    score: Number(item.score_value),
    score_if_wrong: Number(item.score_ifwrong),
  }
  errorMessage.value = ''
  dialog.value = true
}

const saveQuestion = async () => {
  isSaving.value = true
  errorMessage.value = ''

  try {
    const url = isEditing.value ? `/api/questions/${editingId.value}` : '/api/questions'
    const method = isEditing.value ? 'PUT' : 'POST'
    const token = await getFreshCsrfToken()
    const payload = isEditing.value
      ? { ...question.value, category_id: question.value.category }
      : question.value

    const savedQuestion = await request(url, {
      method,
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': token,
      },
      body: JSON.stringify(payload),
    })

    if (isEditing.value) {
      const index = questions.value.findIndex(item => item.id === savedQuestion.id)

      questions.value.splice(index, 1, savedQuestion)
    }
    else {
      questions.value.unshift(savedQuestion)
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

const deleteQuestion = async item => {
  if (!window.confirm('Delete this question?'))
    return

  errorMessage.value = ''
  const token = await getFreshCsrfToken()

  try {
    await request(`/api/questions/${item.id}`, {
      method: 'DELETE',
      headers: {
        'X-CSRF-TOKEN': token,
      },
    })
    questions.value = questions.value.filter(question => question.id !== item.id)
  }
  catch (error) {
    errorMessage.value = error.message
  }
}

const getCategoryName = categoryId => {
  const category = categories.value.find(item => Number(item.id) === Number(categoryId))
  return category?.name ?? `#${categoryId ?? 'unknown'}`
}

const tableQuestions = computed(() => questions.value.map(item => ({
  ...item,
  category_name: getCategoryName(item.category_id),
})))

const filteredQuestions = computed(() => {
  const query = search.value.trim().toLowerCase()

  return tableQuestions.value.filter(item => {
    const matchesCategory = selectedCategory.value === null
      || Number(item.category_id) === Number(selectedCategory.value)
    const matchesSearch = !query || [item.text, item.category_name, item.correct_answer]
      .some(value => String(value ?? '').toLowerCase().includes(query))

    return matchesCategory && matchesSearch
  })
})

onMounted(() => Promise.all([loadQuestions(), loadCategories()]))
</script>

<template>
  <VRow>
    <VCol cols="12">
      <VCard title="Bank Soal">
        <template #append>
          <VBtn
            prepend-icon="ri-add-line"
            @click="openCreateDialog"
          >
            Add question
          </VBtn>
        </template>

        <VCardText v-if="errorMessage">
          <VAlert type="error">
            {{ errorMessage }}
          </VAlert>
        </VCardText>

        <VCardText>
          <VRow>
            <VCol
              cols="12"
              md="7"
            >
              <VTextField
                v-model="search"
                clearable
                label="Cari soal atau kategori"
                prepend-inner-icon="ri-search-line"
              />
            </VCol>
            <VCol
              cols="12"
              md="5"
            >
              <VSelect
                v-model="selectedCategory"
                clearable
                label="Pilih kategori"
                :items="categories"
                item-title="name"
                item-value="id"
              />
            </VCol>
          </VRow>
        </VCardText>

        <VDataTable
          :headers="headers"
          :items="filteredQuestions"
          :loading="isLoading"
          item-value="id"
        >
          <template #item.no="{ index }">
            {{ index + 1 }}
          </template>

          <template #item.category_id="{ item }">
            {{ item.category_name }}
          </template>

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
              @click="deleteQuestion(item)"
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
            <VForm @submit.prevent="saveQuestion">
              <VSelect
                v-model="question.category"
                class="mt-4"
                label="Category"
                :items="categories"
                item-title="name"
                item-value="id"
                :rules="[value => !!value || 'Category is required.']"
              />
              <VTextarea
                v-model="question.question"
                class="mt-4"
                label="Question"
                :rules="[value => !!value || 'Question is required.']"
              />
              <VSelect
                v-model="question.difficulty"
                class="mt-4"
                label="Difficulty"
                :items="['Easy', 'Medium', 'Hard']"
                :rules="[value => !!value || 'Difficulty is required.']"
              />
              <v-select
                v-model="question.type"
                class="mt-4"
                label="Type"
                :items="['mcq', 'true_false', 'Essay']"
                :rules="[value => !!value || 'Type is required.']"
              />
              <VTextField
                v-model="question.options.option_a"
                class="mt-4"
                label="Option A"
                :rules="[value => !!value || 'Option A is required.']"
              />
              <VTextField
                v-model="question.options.option_b"
                class="mt-4"
                label="Option B"
                :rules="[value => !!value || 'Option B is required.']"
              />
              <VTextField
                v-model="question.options.option_c"
                class="mt-4"
                label="Option C"
                :rules="[value => !!value || 'Option C is required.']"
              />
              <VTextField
                v-model="question.options.option_d"
                class="mt-4"
                label="Option D"
                :rules="[value => !!value || 'Option D is required.']"
              />
              <VRow class="mt-1">
                <VCol
                  cols="12"
                  md="6"
                >
                  <VTextField
                    v-model="question.score"
                    label="Score"
                    min="1"
                    type="number"
                    :rules="[value => !!value || 'Score is required.']"
                  />
                </VCol>
                <VCol
                  cols="12"
                  md="6"
                >
                  <VTextField
                    v-model="question.score_if_wrong"
                    label="Score if Wrong"
                    min="1"
                    type="number"
                    :rules="[value => !!value || 'Score if wrong is required.']"
                  />
                </VCol>
              </VRow>
              <VSelect
                v-model="question.correct_answer"
                class="mt-4"
                :items="[question.options.option_a, question.options.option_b, question.options.option_c, question.options.option_d]"
                label="Correct answer"
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
