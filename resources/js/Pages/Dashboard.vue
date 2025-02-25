<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";
import { onMounted, nextTick, ref } from "vue";
import Highcharts from "highcharts";
import axios from "axios";

const pieChartContainer = ref(null);
const columnChartContainer = ref(null);
const areaChartContainer = ref(null);

// Date filter state (optional)
const startDate = ref("");
const endDate = ref("");

// Function to fetch and update charts
const fetchData = () => {
    let params = {};

    // Only add start_date and end_date if selected
    if (startDate.value && endDate.value) {
        params.start_date = startDate.value;
        params.end_date = endDate.value;
    }

    axios.get("/api/category-stats", { params }).then((response) => {
        console.log(response.data);
        Highcharts.chart(pieChartContainer.value, {
            chart: { type: "pie" },
            title: { text: "Gender Statistics" },
            series: [
                {
                    name: "Categories",
                    data: response.data.map((item) => ({
                        name: item.category,
                        y: item.total,
                    })),
                },
            ],
        });
    });

    axios.get("/api/date-stats", { params }).then((response) => {
        console.log(response.data);
        Highcharts.chart(columnChartContainer.value, {
            chart: { type: "column" },
            title: { text: "Aggregation Statistics" },
            xAxis: { type: "category", title: { text: "Date" } },
            yAxis: { title: { text: "Total" } },
            series: [
                {
                    name: "Total",
                    data: response.data.map((item) => [item.date, item.total]),
                },
            ],
        });
    });

    axios.get("/api/genderaggreg-stats", { params }).then((response) => {
        const data = response.data;
        console.log(data);

        // Format data for Highcharts (Sorting by date)
        const categories = data.map((item) => item.date);
        const maleData = data.map((item) => item.male);
        const femaleData = data.map((item) => item.female);

        // Initialize Highcharts Area Chart
        Highcharts.chart(areaChartContainer.value, {
            chart: { type: "area" },
            title: { text: "Gender Count Over Time" },
            xAxis: { categories, title: { text: "Date" } },
            yAxis: { title: { text: "Count" } },
            plotOptions: {
                area: { stacking: "normal", marker: { enabled: false } },
            },
            series: [
                { name: "Male", data: maleData, color: "#3498db" }, // Blue
                { name: "Female", data: femaleData, color: "#e74c3c" }, // Red
            ],
        });
    });
};

// Fetch all data when the page loads
onMounted(() => {
    nextTick(() => {
        fetchData();
    });
});
</script>

<template>
    <Head title="Dashboard" />
    <AuthenticatedLayout>
        <div class="p-10 max-w-screen-xl mx-auto">
            <!-- Date Filter Section -->
            <div class="flex flex-col lg:flex-row gap-4 items-center mb-6">
                <label class="text-gray-700">Start Date:</label>
                <input
                    type="date"
                    v-model="startDate"
                    class="border px-3 py-2 rounded-md"
                />

                <label class="text-gray-700">End Date:</label>
                <input
                    type="date"
                    v-model="endDate"
                    class="border px-3 py-2 rounded-md"
                />

                <button
                    @click="fetchData"
                    class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700"
                >
                    Apply Filter
                </button>

                <button
                    @click="(startDate = ''), (endDate = ''), fetchData()"
                    class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600"
                >
                    Reset Filter
                </button>
            </div>

            <!-- Charts -->
            <div class="flex flex-col lg:flex-row items-center gap-10 mb-10">
                <div
                    ref="pieChartContainer"
                    class="w-full max-w-sm rounded-md"
                ></div>
                <div ref="columnChartContainer" class="flex-1 rounded-md"></div>
            </div>
            <div ref="areaChartContainer" class="flex-1 rounded-md"></div>
        </div>
    </AuthenticatedLayout>
</template>
