<script setup>
import { useFormatter } from '@/composables/useFormatter';
import { Search, ShoppingCart } from 'lucide-vue-next';
import GuestLayout from './layout/GuestLayout.vue';

defineOptions({ layout: GuestLayout });
const products = [
    { id: 1, name: 'Apple Watch Series 7', price: 100000, image: '/assets/images/roti.png' },
    { id: 2, name: 'Samsung Galaxy Watch 4', price: 100000, image: '/assets/images/roti.png' },
    { id: 3, name: 'Fitbit Charge 5', price: 250000, image: '/assets/images/roti.png' },
    { id: 4, name: 'Garmin Forerunner 245', price: 150000, image: '/assets/images/roti.png' },
    { id: 5, name: 'Apple Watch Series 7', price: 100000, image: '/assets/images/roti.png' },
    { id: 6, name: 'Samsung Galaxy Watch 4', price: 100000, image: '/assets/images/roti.png' },
    { id: 7, name: 'Fitbit Charge 5', price: 250000, image: '/assets/images/roti.png' },
    { id: 8, name: 'Garmin Forerunner 245', price: 150000, image: '/assets/images/roti.png' },
    { id: 9, name: 'Apple Watch Series 7', price: 100000, image: '/assets/images/roti.png' },
    { id: 10, name: 'Samsung Galaxy Watch 4', price: 100000, image: '/assets/images/roti.png' },
    { id: 11, name: 'Fitbit Charge 5', price: 250000, image: '/assets/images/roti.png' },
    { id: 12, name: 'Garmin Forerunner 245', price: 150000, image: '/assets/images/roti.png' },
];

const { formatRupiah } = useFormatter();
</script>

<template>
    <div id="store" class="flex flex-col gap-6 p-6 lg:flex-row">
        <!-- Sidebar Filter -->
        <div
            class="sticky top-16 h-10 overflow-hidden rounded-lg border border-gray-200 bg-white p-6 shadow-sm md:h-full lg:h-fit lg:w-80 dark:border-gray-700 dark:bg-gray-800"
        >
            <p class="mb-3 font-normal text-gray-700 dark:text-gray-400">Belum menemukan produk yang diinginkan? Cari disini</p>
            <form class="mx-auto">
                <label for="default-search" class="sr-only mb-2 text-sm font-medium text-gray-900 dark:text-white">Search</label>
                <div class="relative">
                    <div class="pointer-events-none absolute inset-y-0 start-0 flex items-center ps-3">
                        <Search class="h-5 w-5 text-gray-500 dark:text-gray-400" />
                    </div>
                    <input
                        type="search"
                        id="default-search"
                        class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-4 ps-10 text-sm text-gray-900 focus:border-cyan-500 focus:ring-cyan-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-cyan-500 dark:focus:ring-cyan-500"
                        placeholder="Telusuri produk.."
                        required
                    />
                    <button
                        type="submit"
                        class="absolute end-2.5 bottom-2.5 rounded-lg bg-cyan-700 px-4 py-2 text-sm font-medium text-white hover:bg-cyan-800 focus:ring-4 focus:ring-cyan-300 focus:outline-none dark:bg-cyan-600 dark:hover:bg-cyan-700 dark:focus:ring-cyan-800"
                    >
                        Cari
                    </button>
                </div>
            </form>
        </div>

        <!-- Product Grid -->
        <div class="grid flex-1 grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            <div
                v-for="item in products"
                class="flex w-full flex-col rounded-lg border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800"
            >
                <a href="#">
                    <img class="mb-3 rounded-t-lg" :src="item.image" :alt="item.name" />
                </a>
                <div class="flex grow flex-col justify-between px-5 pb-5">
                    <a href="#" class="mb-4">
                        <h5 class="text-base font-semibold tracking-tight text-gray-900 dark:text-white">
                            {{ item.name }}
                        </h5>
                    </a>
                    <div class="flex flex-col items-start justify-between gap-2">
                        <span class="text-base font-bold text-gray-900 dark:text-white">{{ formatRupiah(item.price) }}</span>

                        <a
                            href="#"
                            class="flex w-full justify-center gap-3 rounded-lg bg-cyan-700 px-5 py-2.5 text-center text-sm font-medium text-white hover:bg-cyan-800 focus:ring-4 focus:ring-cyan-300 focus:outline-none dark:bg-cyan-600 dark:hover:bg-cyan-700 dark:focus:ring-cyan-800"
                        >
                            <ShoppingCart class="h-4" /> Beli</a
                        >
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div id="about" class="h-screen">
        <section class="flex justify-center items-center h-full bg-white dark:bg-gray-900">
            <div class="mx-auto max-w-screen-xl items-center gap-16 px-4 py-8 lg:grid lg:grid-cols-2 lg:px-6 lg:py-16">
                <div class="font-light text-gray-500 sm:text-lg dark:text-gray-400">
                    <h2 class="mb-4 text-4xl font-extrabold tracking-tight text-gray-900 dark:text-white">We didn't reinvent the wheel</h2>
                    <p class="mb-4">
                        We are strategists, designers and developers. Innovators and problem solvers. Small enough to be simple and quick, but big
                        enough to deliver the scope you want at the pace you need. Small enough to be simple and quick, but big enough to deliver the
                        scope you want at the pace you need.
                    </p>
                    <p>We are strategists, designers and developers. Innovators and problem solvers. Small enough to be simple and quick.</p>
                </div>
                <div class="mt-8 grid grid-cols-2 gap-4">
                    <img
                        class="w-full rounded-lg"
                        src="https://flowbite.s3.amazonaws.com/blocks/marketing-ui/content/office-long-2.png"
                        alt="office content 1"
                    />
                    <img
                        class="mt-4 w-full rounded-lg lg:mt-10"
                        src="https://flowbite.s3.amazonaws.com/blocks/marketing-ui/content/office-long-1.png"
                        alt="office content 2"
                    />
                </div>
            </div>
        </section>
    </div>
</template>
