<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import Modal from "@/Components/Modal.vue";
import { reactive, computed } from "vue";
import { useForm, router, Head } from "@inertiajs/vue3";

const props = defineProps({
    items: Object,
    filters: Object,
});

const state = reactive({
    categories: ["male", "female"],
    filters: {
        search: props.filters?.search || "",
        category: props.filters?.category || "",
    },
    showModal: false,
    isEditing: false,
    form: useForm({
        id: null,
        name: "",
        category: "",
    }),
});

const filters = computed(() => state.filters);
const categories = computed(() => state.categories);

// Change page function
const changePage = (page) => {
    router.get("/items", { ...state.filters, page }, { preserveState: true });
};

const fetchData = () => {
    router.get("/items", state.filters, { preserveState: true });
};

const openModal = (item = null) => {
    if (item) {
        state.isEditing = true;
        state.form.id = item.id;
        state.form.name = item.name;
        state.form.category = item.category;
    } else {
        state.isEditing = false;
        state.form.id = null;
        state.form.name = "";
        state.form.category = "";
    }
    state.showModal = true;
};

const saveItem = () => {
    if (state.isEditing) {
        router.put(`/items/${state.form.id}`, state.form, {
            onSuccess: () => (state.showModal = false),
        });
    } else {
        router.post("/items", state.form, {
            onSuccess: () => (state.showModal = false),
        });
    }
};

const destroy = (id) => {
    router.delete(`/items/${id}`);
};
</script>

<template>
    <Head title="Items" />
    <AuthenticatedLayout>
        <div class="p-10 max-w-screen-xl mx-auto relative">
            <div class="w-full bg-white p-5 rounded-md">
                <h1 class="text-2xl font-semibold mb-5">Users Management</h1>
                <div class="flex justify-between mb-5">
                    <div class="flex items-center gap-3">
                        <input
                            v-model="filters.search"
                            placeholder="Search..."
                            @input="fetchData"
                            class="rounded-md"
                        />
                        <select
                            v-model="filters.category"
                            @change="fetchData"
                            class="rounded-md"
                        >
                            <option value="" class="capitalize">
                                All Genders
                            </option>
                            <option
                                v-for="category in categories"
                                :key="category"
                                :value="category"
                                class="capitalize"
                            >
                                {{ category }}
                            </option>
                        </select>
                    </div>
                    <button
                        @click="openModal()"
                        class="bg-blue-600 text-white px-5 rounded-md"
                    >
                        Add New Item
                    </button>
                </div>

                <table class="w-full table table-auto">
                    <thead class="bg-slate-200 text-left rounded">
                        <tr>
                            <th class="rounded-l-md p-2">Name</th>
                            <th>Category</th>
                            <th>Date</th>
                            <th>Updated Date</th>
                            <th class="rounded-r-md text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="item in items.data" :key="item.id">
                            <td>{{ item.name }}</td>
                            <td class="capitalize">{{ item.category }}</td>
                            <td>{{ item.data_date }}</td>
                            <td>
                                {{
                                    new Date(
                                        item.updated_at
                                    ).toLocaleDateString()
                                }}
                            </td>
                            <td class="flex justify-center items-center py-3">
                                <div class="flex items-center gap-2">
                                    <button
                                        @click="openModal(item)"
                                        class="bg-blue-950 text-white py-1 px-3 rounded-md"
                                    >
                                        Edit
                                    </button>
                                    <button
                                        @click="destroy(item.id)"
                                        class="bg-red-600 text-white py-1 px-3 rounded-md"
                                    >
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <!-- Pagination Controls -->
                <div class="flex justify-between items-center mt-4">
                    <button
                        v-if="items.prev_page_url"
                        @click="changePage(items.current_page - 1)"
                        class="bg-gray-300 px-4 py-2 rounded-md"
                    >
                        Previous
                    </button>

                    <!-- Page Numbers -->
                    <div class="flex gap-2">
                        <button
                            v-for="page in items.last_page"
                            :key="page"
                            @click="changePage(page)"
                            :class="{
                                'bg-blue-600 text-white':
                                    page === items.current_page,
                                'bg-gray-300': page !== items.current_page,
                            }"
                            class="px-4 py-2 rounded-md"
                        >
                            {{ page }}
                        </button>
                    </div>

                    <button
                        v-if="items.next_page_url"
                        @click="changePage(items.current_page + 1)"
                        class="bg-gray-300 px-4 py-2 rounded-md"
                    >
                        Next
                    </button>
                </div>

                <!-- Modal for Add/Edit Item -->
                <Modal
                    :show="state.showModal"
                    maxWidth="md"
                    @close="state.showModal = false"
                >
                    <form class="p-6" @submit.prevent="saveItem">
                        <h2 class="text-xl font-bold mb-4">
                            {{ isEditing ? "Edit Item" : "Add New Item" }}
                        </h2>

                        <div class="mb-4">
                            <label class="block text-gray-700">Name</label>
                            <input
                                v-model="state.form.name"
                                placeholder="Enter name"
                                class="w-full border rounded-md px-3 py-2 mt-1"
                            />
                            <small>{{ state.form.errors.name }}</small>
                        </div>

                        <div class="mb-4">
                            <label class="block text-gray-700">Category</label>
                            <select
                                v-model="state.form.category"
                                class="w-full border rounded-md px-3 py-2 mt-1"
                            >
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                            </select>
                            <small>{{ state.form.errors.category }}</small>
                        </div>

                        <div class="flex justify-end gap-2">
                            <button
                                type="submit"
                                class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700"
                            >
                                {{ isEditing ? "Update" : "Save" }}
                            </button>
                            <button
                                @click="state.showModal = false"
                                class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600"
                            >
                                Cancel
                            </button>
                        </div>
                    </form>
                </Modal>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
