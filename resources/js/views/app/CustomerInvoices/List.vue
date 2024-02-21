<script>
    import { hasAccess, setMetaTitle, getValueLabel, getValues } from '@/utils/helper'
    import { appStore } from '@/store.js'
    import UserInvoiceService from '@/service/UserInvoiceService'
    import Address from '@/views/app/_partials/Address.vue'
    import moment from 'moment'
    
    export default {
        components: { Address },
        setup() {
            setMetaTitle('meta.title.customer_invoices_list')
            
            const userInvoiceService = new UserInvoiceService()
            
            return {
                userInvoiceService,
                hasAccess,
                getValueLabel
            }
        },
        data() {
            return {
                loading: true,
                customerInvoices: [],
                saleRegistries: [],
                displayConfirmation: false,
                deleteCustomerInvoiceId: null,
                documentTypes: getValues('invoices.types'),
                meta: {
                    list: {
                        first: appStore().getDTSessionStateFirst("dt-state-customer-invoices-table"),
                        size: this.rowsPerPage,
                        sort: 'title',
                        order: 1
                    },
                    search: {},
                    totalRecords: null,
                    totalPages: null,
                    breadcrumbItems: [
                        {'label' : this.$t('menu.settlements'), disabled : true },
                        {'label' : this.$t('menu.customer_invoices'), disabled : true },
                    ]
                }
            }
        },
        beforeMount() {
            let order = appStore().getTableOrder('customer_invoices');
            if (order != undefined) {
                this.meta.list.sort = order.col;
                this.meta.list.order = order.dir;
            }
            
            let filter = appStore().getTableFilter('customer_invoices');
            if (filter != undefined)
                this.meta.search = filter;
                
            if(appStore().toastMessage) {
                let m = appStore().toastMessage
                this.$toast.add({ severity: m.severity, summary: m.summary, detail: m.detail, life: 3000 });
                appStore().setToastMessage(null);
            }
            this.getList()
            this.getSaleRegistries()
        },
        methods: {
            getList() {
                this.loading = true
                
                let search = Object.assign({}, this.meta.search)
                search.date_from = search.date_from ? moment(search.date_from).format("YYYY-MM-DD") : null
                search.date_to = search.date_to ? moment(search.date_to).format("YYYY-MM-DD") : null
                
                this.userInvoiceService.invoices(this.meta.list, search)
                    .then(
                        (response) => {
                            this.customerInvoices = response.data.data
                            this.meta.totalRecords = response.data.total_rows
                            this.meta.totalPages = response.data.total_pages
                            this.loading = false
                        },
                        (errors) => {
                            this.$toast.add({ severity: 'error', summary: this.$t('app.error'), detail: errors.response.data.message, life: 3000 });
                        }
                    )
            },
            
            getSaleRegistries() {
                this.userInvoiceService.saleRegisterList({size: 100, first: 0})
                    .then(
                        (response) => {
                            response.data.data.forEach((i) => {
                                this.saleRegistries.push({"id" : i.id, "name" : i.name })
                            })
                        },
                        (errors) => {
                            this.$toast.add({ severity: 'error', summary: this.$t('app.error'), detail: errors.response.data.message, life: 3000 });
                        }
                    )
            },
            
            changePage(event) {
                this.meta.list.first = event["first"];
                this.getList()
            },
            
            newInvoice() {
                this.$router.push({name: 'customer_invoices_new'})
            },
            
            editInvoice(customerInvoiceId) {
                this.$router.push({name: 'customer_invoices_edit', params: { customerInvoiceId : customerInvoiceId }})
            },
            
            openConfirmation(id) {
                this.displayConfirmation = true
                this.deleteCustomerInvoiceId = id
            },
            
            confirmDeleteInvoice() {
                this.userInvoiceService.saleRegisterRemove(this.deleteCustomerInvoiceId)
                    .then(
                        (response) => {
                            this.getList()
                            this.$toast.add({ severity: 'success', summary: this.$t('app.success'), detail: this.$t('customer_invoices.invoice_deleted'), life: 3000 });
                        },
                        (errors) => {
                            this.$toast.add({ severity: 'error', summary: this.$t('app.error'), detail: errors.response.data.message, life: 3000 });
                        }
                    )
                
                this.displayConfirmation = false
                this.deleteCustomerInvoiceId = null
            },
            
            closeConfirmation() {
                this.displayConfirmation = false
            },
            
            rowClick(event) {
                if (hasAccess('customer_invoices:update')) 
                    this.editInvoice(event.data.id)
            },
            
            sort(event) {
                this.meta.list.sort = event['sortField']
                this.meta.list.order = event['sortOrder']
                this.meta.list.first = 0
                
                appStore().setTableOrder('customer_invoices', this.meta.list.sort, this.meta.list.order);
                
                this.getList()
            },
            
            search() {
                this.meta.list.first = 0
                appStore().setTableFilter('customer_invoices', this.meta.search)
                this.getList()
            },
            
            resetSearch() {
                this.meta.list.first = 0
                this.meta.search = {}
                appStore().setTableFilter('customer_invoices', this.meta.search)
                this.getList()
            },
            
            downloadPDF(invoiceId) {
                this.userInvoiceService.getPDF(invoiceId)
                    .then(
                        (response) => {
                            const contentDisposition = response.headers['content-disposition'];
                            let fileName = 'file.pdf';
                            if (contentDisposition) {
                                const fileNameMatch = contentDisposition.match(/filename="(.+)"/);
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
            },
            
            makeFromProforma(invoiceId) {
                this.$router.push({name: 'customer_invoices_new_from_proforma', params: {customerInvoiceId : invoiceId}})
            }
        },
    }
</script>

<template>
    <Breadcrumb :model="meta.breadcrumbItems"/>
    
    <div class="grid mt-1">
        <div class="col-12">
            <div class="card">
                <div class="flex justify-content-between align-items-center mb-5">
                    <h4 class="inline-flex mb-0 text-color font-medium">{{ $t('menu.customer_invoices') }}</h4>
                    <div class="text-right mb-0 inline-flex" v-if="hasAccess('customer_invoices:create')">
                        <Button icon="pi pi-plus" v-tooltip.left="$t('customer_invoices.new_invoice')" @click="newInvoice" class="text-center"></Button>
                    </div>
                </div>
                
                <form v-on:submit.prevent="search">
                    <div class="formgrid grid mb-1">
                        <div class="col-12 md:col-6 mb-3">
                            <Dropdown v-model="meta.search.type" :showClear="this.meta.search.type ? true : false" :options="documentTypes" optionLabel="name" optionValue="id" :placeholder="$t('customer_invoices.document_type')" class="w-full" />
                        </div>
                        
                        <div class="col-12 md:col-6 mb-3">
                            <InputText type="text" :placeholder="$t('customer_invoices.number')" class="w-full" v-model="meta.search.number"/>
                        </div>
                        
                        <div class="col-12 md:col-3 mb-3">
                            <Dropdown v-model="meta.search.sale_register_id" :showClear="this.meta.search.sale_register_id ? true : false" :options="saleRegistries" optionLabel="name" optionValue="id" :placeholder="$t('customer_invoices.sale_register')" class="w-full" />
                        </div>
                        
                        <div class="col-12 md:col-7 sm:col-9 mb-3">
                            <div class="flex">
                                <div class="col-6 p-0 pr-1">
                                    <Calendar id="start" v-model="meta.search.date_from" :placeholder="$t('customer_invoices.start_document_date')" class="w-auto" showIcon/>
                                </div>
                                <div class="col-6 p-0 pl-1">
                                    <Calendar id="end" v-model="meta.search.date_to" :placeholder="$t('customer_invoices.end_document_date')" showIcon/>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-12 mb-3" style="width: 120px;">
                            <Button type="submit" iconPos="left" icon="pi pi-search" class="mr-2"/>
                            <Button severity="secondary" outlined iconPos="left" icon="pi pi-filter-slash" @click="resetSearch"/>
                        </div>
                    </div>
                </form>
                
                <DataTable :value="customerInvoices" stripedRows class="p-datatable-gridlines clickable" :totalRecords="meta.totalRecords" :rowHover="true" :lazy="true" :paginator="true" :pageCount="meta.totalPages" :rows="meta.list.size" :first="meta.list.first" @page="changePage" :loading="loading" @row-click="rowClick($event)" @sort="sort($event)" :sortField="this.meta.list.sort" :sortOrder="this.meta.list.order" stateStorage="session" stateKey="dt-state-customer-invoices-table">
                    <Column class="text-left" style="min-width: 60px; width: 60px;">
                        <template #body="{ data }">
                            <Button icon="pi pi-file-pdf" v-tooltip.bottom="$t('customer_invoices.download_invoice')" class="p-button-info p-2" style="width: auto" @click="downloadPDF(data.id)"/>
                            <template v-if="data.type == 'proforma' && data.make_from_proforma && hasAccess('customer_invoices:create')">
                                <Button icon="pi pi-book" severity="warning" v-tooltip.bottom="$t('customer_invoices.make_from_proforma')" class="p-button-info p-2 ml-1" style="width: auto" @click="makeFromProforma(data.id)"/>
                            </template>
                        </template>
                    </Column>
                    <Column :header="$t('customer_invoices.number')" field="full_number" style="min-width: 300px;" sortable>
                        <template #body="{ data }">
                            <div class="font-medium mb-1 text-lg">{{ data.customer_name }}</div>
                            <div class="text-sm text-gray-500">{{ data.full_number }}</div>
                            <template v-if="data.proforma_number">
                                <div class="mt-2 text-sm">
                                    {{ $t("customer_invoices.from_proforma") }}:
                                    <router-link :to="{name: 'customer_invoices_edit', params: { customerInvoiceId : data.proforma_id }}" v-if="hasAccess('dictionary:update')">
                                         {{ data.proforma_number }}
                                    </router-link>
                                </div>
                            </template>
                        </template>
                    </Column>
                    <Column :header="$t('customer_invoices.document_date')" field="document_date" class="text-center" sortable></Column>
                    <Column :header="$t('customer_invoices.net_amount')" field="net_amount" class="text-right" sortable>
                        <template #body="{ data }">
                            {{ numeralFormat(data.net_amount, '0.00') }}
                        </template>
                    </Column>
                    <Column :header="$t('customer_invoices.gross_amount')" field="gross_amount" class="text-right" sortable>
                        <template #body="{ data }">
                            {{ numeralFormat(data.gross_amount, '0.00') }}
                        </template>
                    </Column>
                    <Column :header="$t('customer_invoices.sale_register')">
                        <template #body="{ data }">
                            {{ data.sale_register.name }}
                        </template>
                    </Column>
                    <Column field="delete" v-if="hasAccess('customer_invoices:delete')" style="min-width: 45px; width: 45px" class="text-center">
                        <template #body="{ data }">
                            <Button icon="pi pi-trash" v-tooltip.bottom="$t('app.remove')" class="p-button-danger p-2" style="width: auto" @click="openConfirmation(data.id)" :disabled="!data.can_delete"/>
                        </template>
                    </Column>
                    <template #empty>
                        {{ $t('customer_invoices.empty_list') }}
                    </template>
                </DataTable>
                <Dialog :header="$t('app.confirmation')" v-model:visible="displayConfirmation" :style="{ width: '450px' }" :modal="true">
                    <div class="flex align-items-center justify-content-center">
                        <i class="pi pi-exclamation-triangle mr-3" style="font-size: 2rem" />
                        <span>{{ $t('app.remove_object_confirmation') }}</span>
                    </div>
                    <template #footer>
                        <Button :label="$t('app.no')" icon="pi pi-times" @click="closeConfirmation" class="p-button-text" />
                        <Button :label="$t('app.yes')" icon="pi pi-check" @click="confirmDeleteInvoice" class="p-button-danger" autofocus />
                    </template>
                </Dialog>
            </div>
        </div>
    </div>
</template>