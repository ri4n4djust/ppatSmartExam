<script setup>
const students = ref([])
const search = ref('')
const isLoading = ref(false)
const isSaving = ref(false)
const errorMessage = ref('')
const dialog = ref(false)
const editingId = ref(null)
const student = ref({ name: '', username: '', email: '', password: '' })

const getCsrfHeaders = () => {
	const metaToken = document.querySelector('meta[name="csrf-token"]')?.content ?? ''

	return {
		Accept: 'application/json',
		...(metaToken ? { 'X-CSRF-TOKEN': metaToken } : {}),
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

const loadStudents = async () => {
	isLoading.value = true
	errorMessage.value = ''

	try {
		const token = await getFreshCsrfToken()

		students.value = await request('/api/daftar-siswa', {
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

const filteredStudents = computed(() => {
	const query = search.value.trim().toLowerCase()

	if (!query)
		return students.value

	return students.value.filter(student => [student.name, student.username, student.email]
		.some(value => String(value ?? '').toLowerCase().includes(query)))
})

const openEditDialog = item => {
	editingId.value = item.id
	student.value = {
		name: item.name ?? '',
		username: item.username ?? '',
		email: item.email ?? '',
		password: '',
	}
	errorMessage.value = ''
	dialog.value = true
}

const saveStudent = async () => {
	isSaving.value = true
	errorMessage.value = ''

	try {
		const token = await getFreshCsrfToken()
		const savedStudent = await request(`/api/daftar-siswa/${editingId.value}`, {
			method: 'PUT',
			headers: {
				'Content-Type': 'application/json',
				'X-CSRF-TOKEN': token,
			},
			body: JSON.stringify(student.value),
		})
		const index = students.value.findIndex(item => item.id === savedStudent.id)

		students.value.splice(index, 1, savedStudent)
		dialog.value = false
	}
	catch (error) {
		errorMessage.value = error.message
	}
	finally {
		isSaving.value = false
	}
}

const headers = [
	{ title: 'No', key: 'no' },
	{ title: 'Nama', key: 'name' },
	{ title: 'Username', key: 'username' },
	{ title: 'Email', key: 'email' },
	{ title: 'Terdaftar', key: 'created_at' },
	{ title: 'Aksi', key: 'actions', sortable: false, align: 'end' },
]

onMounted(loadStudents)
</script>

<template>
	<VRow>
		<VCol cols="12">
			<VCard title="Daftar Siswa">
				<VCardText v-if="errorMessage">
					<VAlert type="error">
						{{ errorMessage }}
					</VAlert>
				</VCardText>

				<VCardText>
					<VTextField
						v-model="search"
						clearable
						label="Cari username atau email"
						prepend-inner-icon="ri-search-line"
					/>
				</VCardText>

				<VDataTable
					:headers="headers"
					:items="filteredStudents"
					:loading="isLoading"
					item-value="id"
				>
					<template #item.no="{ index }">
						{{ index + 1 }}
					</template>
					<template #item.actions="{ item }">
						<VBtn
							icon="ri-pencil-line"
							size="small"
							variant="text"
							@click="openEditDialog(item)"
						/>
					</template>
				</VDataTable>
			</VCard>
		</VCol>
	</VRow>

	<VDialog
		v-model="dialog"
		max-width="560"
	>
		<VCard title="Edit Siswa">
			<VCardText>
				<VForm @submit.prevent="saveStudent">
					<VTextField
						v-model="student.name"
						label="Nama"
						:rules="[value => !!value || 'Nama wajib diisi.']"
					/>
					<VTextField
						v-model="student.username"
						class="mt-4"
						label="Username"
						:rules="[value => !!value || 'Username wajib diisi.']"
					/>
					<VTextField
						v-model="student.email"
						class="mt-4"
						label="Email"
						:rules="[value => !!value || 'Email wajib diisi.']"
					/>
					<VTextField
						v-model="student.password"
						class="mt-4"
						label="Password baru (opsional)"
						type="password"
						:rules="[value => !value || value.length >= 8 || 'Password minimal 8 karakter.']"
					/>
					<div class="d-flex justify-end gap-3 mt-6">
						<VBtn
							variant="tonal"
							@click="dialog = false"
						>
							Batal
						</VBtn>
						<VBtn
							:loading="isSaving"
							type="submit"
						>
							Simpan
						</VBtn>
					</div>
				</VForm>
			</VCardText>
		</VCard>
	</VDialog>
</template>
