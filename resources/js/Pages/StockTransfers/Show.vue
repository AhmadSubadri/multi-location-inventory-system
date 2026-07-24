<script setup>
import Modal from '@/Components/Modal.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    transfer: Object,
});

const isReceiveModalOpen = ref(false);

const receiveForm = useForm({
    items: props.transfer.items?.map((i) => ({
        id: i.id,
        product_name: i.product?.name,
        qty_sent: i.qty_sent,
        qty_received: i.qty_sent, // Default receive all
    })) || [],
});

const approve = () => {
    if (confirm(`Setujui pengajuan transfer ${props.transfer.code}?`)) {
        router.post(route('stock-transfers.approve', props.transfer.id));
    }
};

const ship = () => {
    if (confirm(`Kirim stok untuk dokumen ${props.transfer.code}? Stok dari ${props.transfer.from_location?.name} akan dipotong dan status berubah menjadi In-Transit (Shipped).`)) {
        router.post(route('stock-transfers.ship', props.transfer.id));
    }
};

const openReceiveModal = () => {
    isReceiveModalOpen.value = true;
};

const submitReceive = () => {
    receiveForm.post(route('stock-transfers.receive', props.transfer.id), {
        onSuccess: () => {
            isReceiveModalOpen.value = false;
        },
    });
};
</script>

<template>
    <AuthenticatedLayout>
        <Head :title="`Detail Transfer: ${transfer.code}`" />

        <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <Link :href="route('stock-transfers.index')" class="text-xs text-surface-500 hover:text-surface-700 flex items-center gap-1 mb-1">
                    <i class="fa-solid fa-arrow-left"></i> Kembali ke Daftar Transfer
                </Link>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-extrabold text-surface-900 dark:text-white">{{ transfer.code }}</h1>
                    <span
                        class="badge capitalize font-semibold"
                        :class="{
                            'badge-warning': transfer.status === 'submitted',
                            'badge-info': transfer.status === 'approved',
                            'badge-danger': transfer.status === 'shipped',
                            'badge-success': transfer.status === 'received',
                        }"
                    >
                        {{ transfer.status === 'shipped' ? 'Shipped (In-Transit)' : transfer.status }}
                    </span>
                </div>
            </div>

            <!-- Sequential Workflow Action Buttons -->
            <div class="flex items-center gap-2">
                <!-- Step 1: Approve -->
                <button v-if="transfer.status === 'submitted'" @click="approve" class="btn-primary">
                    <i class="fa-solid fa-check"></i> Setujui Transfer
                </button>

                <!-- Step 2: Ship (Deduct Stock & mark In-Transit) -->
                <button v-if="transfer.status === 'approved'" @click="ship" class="btn-warning">
                    <i class="fa-solid fa-truck-fast"></i> Kirim Stok (Mark Shipped / In-Transit)
                </button>

                <!-- Step 3: Receive (Add Stock to Destination) -->
                <button v-if="transfer.status === 'shipped'" @click="openReceiveModal" class="btn-success">
                    <i class="fa-solid fa-box-open"></i> Terima Stok (Receive Items)
                </button>
            </div>
        </div>

        <!-- Workflow Visual Stepper -->
        <div class="card p-6 mb-6">
            <div class="grid grid-cols-4 gap-2 text-center text-xs font-bold">
                <div class="p-2 rounded-lg border" :class="transfer.status === 'submitted' ? 'bg-primary-50 text-primary-700 border-primary-300' : 'bg-surface-50 text-surface-400'">
                    1. Submitted
                </div>
                <div class="p-2 rounded-lg border" :class="transfer.status === 'approved' ? 'bg-primary-50 text-primary-700 border-primary-300' : 'bg-surface-50 text-surface-400'">
                    2. Approved
                </div>
                <div class="p-2 rounded-lg border" :class="transfer.status === 'shipped' ? 'bg-warning-50 text-warning-700 border-warning-300' : 'bg-surface-50 text-surface-400'">
                    3. Shipped (In-Transit)
                </div>
                <div class="p-2 rounded-lg border" :class="transfer.status === 'received' ? 'bg-accent-50 text-accent-700 border-accent-300' : 'bg-surface-50 text-surface-400'">
                    4. Received (Selesai)
                </div>
            </div>
        </div>

        <!-- Meta -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-3 card p-6 grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs">
                <div>
                    <span class="text-surface-500 block">Asal Pengiriman:</span>
                    <strong class="text-sm font-bold text-surface-900 dark:text-white">{{ transfer.from_location?.name }}</strong>
                </div>
                <div>
                    <span class="text-surface-500 block">Tujuan Penerimaan:</span>
                    <strong class="text-sm font-bold text-primary-600">{{ transfer.to_location?.name }}</strong>
                </div>
                <div>
                    <span class="text-surface-500 block">Tgl Pengiriman (Shipped):</span>
                    <strong class="text-sm text-surface-900 dark:text-white">{{ $datetime(transfer.shipped_at) }}</strong>
                </div>
                <div>
                    <span class="text-surface-500 block">Tgl Diterima (Received):</span>
                    <strong class="text-sm text-surface-900 dark:text-white">{{ $datetime(transfer.received_at) }}</strong>
                </div>
            </div>

            <div class="lg:col-span-3 card overflow-hidden">
                <div class="px-6 py-4 border-b border-surface-200 dark:border-surface-700 font-bold text-sm">
                    Rincian Barang & Batch Yang Ditransfer
                </div>
                <div class="overflow-x-auto">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Produk</th>
                                <th>Nomor Batch</th>
                                <th class="text-right">Qty Dikirim</th>
                                <th class="text-right">Qty Diterima</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="item in transfer.items" :key="item.id">
                                <td class="font-bold text-surface-900 dark:text-white">
                                    {{ item.product?.name }}
                                </td>
                                <td class="font-mono text-xs font-semibold">{{ item.batch?.batch_number }}</td>
                                <td class="text-right font-bold">{{ $number(item.qty_sent) }} {{ item.product?.base_unit?.symbol }}</td>
                                <td class="text-right font-bold text-accent-600">
                                    {{ item.qty_received !== null ? `${$number(item.qty_received)} ${item.product?.base_unit?.symbol}` : '-' }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Receive Modal -->
        <Modal :show="isReceiveModalOpen" @close="isReceiveModalOpen = false" title="Konfirmasi Penerimaan Stok (Receive Items)">
            <form @submit.prevent="submitReceive" class="space-y-4">
                <p class="text-xs text-surface-500">Masukkan jumlah fisik barang yang diterima di lokasi tujuan.</p>

                <div v-for="rItem in receiveForm.items" :key="rItem.id" class="p-3 bg-surface-50 dark:bg-surface-800 rounded-xl border flex items-center justify-between gap-4">
                    <div class="text-xs font-bold text-surface-900 dark:text-white">
                        {{ rItem.product_name }}
                        <span class="block font-normal text-surface-400">Qty Dikirim: {{ rItem.qty_sent }}</span>
                    </div>
                    <div class="w-32">
                        <label class="form-label text-[10px]">Qty Diterima</label>
                        <input v-model.number="rItem.qty_received" type="number" min="0" step="any" required class="form-input text-xs font-bold" />
                    </div>
                </div>
            </form>
            <template #footer>
                <button type="button" @click="isReceiveModalOpen = false" class="btn-secondary">Batal</button>
                <button type="button" @click="submitReceive" :disabled="receiveForm.processing" class="btn-success">Simpan Penerimaan Stok</button>
            </template>
        </Modal>
    </AuthenticatedLayout>
</template>
