<script>
    import { getResponseErrors, setMetaTitle } from '@/utils/helper'
    import OrderService from '@/service/OrderService'
    
    export default {
        setup() {
            setMetaTitle('meta.title.invoices')
            
            const orderService = new OrderService()
            
            return {
                orderService,
            }
        },
        data() {
            return {
                invoices : [],
                meta: {
                    search: {},
                    loading: false,
                    currentPage: 1,
                    perPage: this.rowsPerPage,
                    totalRecords: null,
                    totalPages: null,
                    breadcrumbItems: [
                        {'label' : this.$t('menu.finances'), disabled : true },
                        {'label' : this.$t('menu.invoices'), disabled : true },
                    ],
                }
            }
        },
        beforeMount() {
            this.orderService.invoices(this.meta.perPage, this.meta.currentPage, null, null, this.meta.search)
                .then(
                    (response) => {
                        this.invoices = response.data.data
                        this.meta.totalRecords = response.data.total_rows
                        this.meta.totalPages = response.data.total_pages
                        this.meta.loading = false
                    },
                    (errors) => {
                        this.$toast.add({ severity: 'error', summary: this.$t('app.error'), detail: errors.response.data.message, life: 3000 });
                    },
                )
        },
        methods: {
            changePage(event) {
                this.meta.currentPage = event["page"] + 1;
                this.getList()
            },
            downloadPDF(invoiceId) {
                this.orderService.getPDFInvoice(invoiceId)
                    .then(
                        (response) => {
                            const contentDisposition = response.headers['content-disposition'];
                            let fileName = 'file.pdf';
                            if (contentDisposition) {
                                const fileNameMatch = contentDisposition.match(/filename=(.+)/);
                                if (fileNameMatch.length === 2)
                                    fileName = fileNameMatch[1];
                            }
                            
                            var fileURL = window.URL.createObjectURL(new Blob([response.data]));
                            var fileLink = document.createElement('a');
                            fileLink.href = fileURL;
                            fileLink.setAttribute('download', fileName);
                            document.body.appendChild(fileLink);
                            fileLink.click();
                        },
                        (errors) => {
                            
                        }
                    )
            }
        },
    }
</script>

<template><Breadcrumb :model="meta.breadcrumbItems"/>
    <div class="grid mt-1">
        <div class="col-12">
            <div class="card">
                <DataTable :value="invoices" stripedRows class="p-datatable-gridlines clickable" :totalRecords="meta.totalRecords" :rowHover="true" :lazy="true" :paginator="true" :pageCount="meta.totalPages" :rows="meta.perPage" @page="changePage" :loading="meta.loading">
                    <Column field="full_number" :header="$t('invoices.number')" class="text-left"></Column>
                    <Column field="amount" :header="$t('invoices.amount')" class="text-right">
                        <template #body="{ data }">
                            {{ numeralFormat(data.amount, '0.00') }} {{ data.currency }}
                        </template>
                    </Column>
                    <Column field="gross" :header="$t('invoices.gross_amount')" class="text-right">
                        <template #body="{ data }">
                            {{ numeralFormat(data.gross, '0.00') }} {{ data.currency }}
                        </template>
                    </Column>
                    <Column field="created_at" :header="$t('invoices.date')" class="text-right"></Column>
                    <Column class="text-center" style="min-width: 60px; width: 60px;">
                        <template #body="{ data }">
                            <Button icon="pi pi-file-pdf" v-tooltip.bottom="$t('invoices.download_document')" class="p-button-info p-2" style="width: auto" @click="downloadPDF(data.id)"/>
                        </template>
                    </Column>
                    <template #empty>
                        {{ $t('invoices.empty_list') }}
                    </template>
                </DataTable>
            </div>
        </div>
    </div>
</template>