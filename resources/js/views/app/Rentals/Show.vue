<script>
    import { getValueLabel, getResponseErrors, hasAccess, setMetaTitle, p, getRentalBoxColor } from '@/utils/helper'
    import { appStore } from '@/store.js'
    
    import RentalService from '@/service/RentalService'
    import HistoryService from '@/service/HistoryService'
    
    export default {
        setup() {
            setMetaTitle('meta.title.rent_show')
            
            const rentalService = new RentalService()
            const historyService = new HistoryService()
            
            return {
                p,
                rentalService,
                historyService,
                hasAccess,
                getValueLabel,
                getRentalBoxColor
            }
        },
        data() {
            return {
                bills: [],
                documents: [],
                payments: [],
                histories: [],
                displayBillConfirmation: false,
                deleteBillId: null,
                displayDocumentConfirmation: false,
                deleteDocumentId: null,
                displayPaymentConfirmation: false,
                deletePaymentId: null,
                errors: [],
                rental: {
                    item: {},
                    tenant: {},
                },
                loading: true,
                meta: {
                    breadcrumbItems: [
                        {'label' : this.$t('menu.estates'), disabled : true },
                        {'label' : this.$t('menu.rentals_list'), route : { name : 'rentals'}  },
                        {'label' : this.$t('rent.details'), disabled : true },
                    ],
                    bills: {
                        search: {},
                        currentPage: 1,
                        perPage: 10,
                        loading: false,
                        totalRecords: null,
                        totalPages: null,
                    },
                    documents: {
                        search: {},
                        currentPage: 1,
                        perPage: 10,
                        loading: false,
                        totalRecords: null,
                        totalPages: null,
                    },
                    payments: {
                        search: {},
                        currentPage: 1,
                        perPage: 10,
                        loading: false,
                        totalRecords: null,
                        totalPages: null,
                    },
                    history: {
                        search: {},
                        currentPage: 1,
                        perPage: 10,
                        loading: false,
                        totalRecords: null,
                        totalPages: null,
                    }
                }
            }
        },
        beforeMount() {
            if(appStore().toastMessage) {
                let m = appStore().toastMessage
                this.$toast.add({ severity: m.severity, summary: m.summary, detail: m.detail, life: 3000 });
                appStore().setToastMessage(null)
            }
            
            this.rentalService.get(this.$route.params.rentalId)
                .then(
                    (response) => {
                        this.rental = response.data
                        this.loading = false
                        
                        this.getBillList()
                        this.getDocumentList()
                        this.getPaymentList()
                        this.getHistoryList()
                    },
                    (errors) => {
                        if(errors.response.status == 404)
                        {
                            appStore().setError404(errors.response.data.message);
                            this.$router.push({name: 'objectnotfound'})
                        }
                        else
                            this.$toast.add({ severity: 'error', summary: this.$t('app.error'), detail: errors.response.data.message, life: 3000 });
                    }
                );
        },
        methods: {
            terminate() {
                this.$router.push({name: 'rental_terminate'})
            },
            
            getBillList() {
                this.meta.bills.loading = true
                
                this.rentalService.bills(this.$route.params.rentalId, this.meta.bills.perPage, this.meta.bills.currentPage, null, null, this.meta.bills.search)
                    .then(
                        (response) => {
                            this.bills = response.data.data
                            this.meta.bills.totalRecords = response.data.total_rows
                            this.meta.bills.totalPages = response.data.total_pages
                            this.meta.bills.loading = false
                        },
                        (errors) => {
                            this.$toast.add({ severity: 'error', summary: this.$t('app.error'), detail: errors.response.data.message, life: 3000 });
                        }
                    );
            },
            
            changeBillsPage(event) {
                this.meta.bills.currentPage = event["page"] + 1;
                this.getBillList()
            },
            
            openBillConfirmation(id) {
                this.displayBillConfirmation = true
                this.deleteBillId = id
            },
            
            confirmDeleteBill() {
                this.rentalService.removeBill(this.$route.params.rentalId, this.deleteBillId)
                    .then(
                        (response) => {
                            this.getBillList()
                            this.$toast.add({ severity: 'success', summary: this.$t('app.success'), detail: this.$t('items.bill_deleted'), life: 3000 });
                        },
                        (errors) => {
                            this.$toast.add({ severity: 'error', summary: this.$t('app.error'), detail: errors.response.data.message, life: 3000 });
                        }
                    )
                
                this.displayBillConfirmation = false
                this.deleteBillId = null
            },
            
            closeBillConfirmation() {
                this.displayBillConfirmation = false
            },
            
            getDocumentList() {
                this.meta.documents.loading = true
                
                this.rentalService.getDocuments(this.$route.params.rentalId, this.meta.documents.perPage, this.meta.documents.currentPage, null, null, this.meta.documents.search)
                    .then(
                        (response) => {
                            this.documents = response.data.data
                            this.meta.documents.totalRecords = response.data.total_rows
                            this.meta.documents.totalPages = response.data.total_pages
                            this.meta.documents.loading = false
                        },
                        (errors) => {
                            this.$toast.add({ severity: 'error', summary: this.$t('app.error'), detail: errors.response.data.message, life: 3000 });
                        }
                    );
            },
            
            changeDocumentsPage(event) {
                this.meta.documents.currentPage = event["page"] + 1;
                this.getDocumentList()
            },
            
            openDocumentConfirmation(id) {
                this.displayDocumentConfirmation = true
                this.deleteDocumentId = id
            },
            
            confirmDeleteDocument() {
                this.rentalService.removeDocument(this.$route.params.rentalId, this.deleteDocumentId)
                    .then(
                        (response) => {
                            this.getDocumentList()
                            this.$toast.add({ severity: 'success', summary: this.$t('app.success'), detail: this.$t('rent.document_deleted'), life: 3000 });
                        },
                        (errors) => {
                            this.$toast.add({ severity: 'error', summary: this.$t('app.error'), detail: errors.response.data.message, life: 3000 });
                        }
                    )
                
                this.displayDocumentConfirmation = false
                this.deleteDocumentId = null
            },
            
            closeDocumentConfirmation() {
                this.displayDocumentConfirmation = false
            },
            
            rowBillsClick(event) {
                this.$router.push({name: 'rental_bill_show', params: {billId : event.data.id}})
            },
            
            newBill() {
                this.$router.push({name: 'rental_bill_new'})
            },
            
            newDocument() {
                this.$router.push({name: 'rental_document_new'})
            },
            
            newPayment() {
                this.$router.push({name: 'rental_payment'})
            },
            
            rowDocumentsClick(event) {
                if (hasAccess('rent:update')) 
                    this.$router.push({name: 'rental_document_edit', params: {documentId : event.data.id}})
            },
            
            edit() {
                this.$router.push({name: 'rental_edit'})
            },
            
            downloadPDF(documentId) {
                this.rentalService.getPDFDocument(this.$route.params.rentalId, documentId)
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
            
            getPaymentList() {
                this.meta.payments.loading = true
                
                this.rentalService.payments(this.$route.params.rentalId, this.meta.payments.perPage, this.meta.payments.currentPage, null, null, this.meta.payments.search)
                    .then(
                        (response) => {
                            this.payments = response.data.data
                            this.meta.payments.totalRecords = response.data.total_rows
                            this.meta.payments.totalPages = response.data.total_pages
                            this.meta.payments.loading = false
                        },
                        (errors) => {
                            this.$toast.add({ severity: 'error', summary: this.$t('app.error'), detail: errors.response.data.message, life: 3000 });
                        }
                    );
            },
            
            changePaymentsPage(event) {
                this.meta.payments.currentPage = event["page"] + 1;
                this.getPaymentList()
            },
            
            openPaymentConfirmation(id) {
                this.displayPaymentConfirmation = true
                this.deletePaymentId = id
            },
            
            confirmDeletePayment() {
                this.rentalService.removePayment(this.$route.params.rentalId, this.deletePaymentId)
                    .then(
                        (response) => {
                            this.getPaymentList()
                            this.getBillList()
                            this.$toast.add({ severity: 'success', summary: this.$t('app.success'), detail: this.$t('items.bill_deleted'), life: 3000 });
                        },
                        (errors) => {
                            this.$toast.add({ severity: 'error', summary: this.$t('app.error'), detail: errors.response.data.message, life: 3000 });
                        }
                    )
                
                this.displayPaymentConfirmation = false
                this.deletePaymentId = null
            },
            
            closePaymentConfirmation() {
                this.displayPaymentConfirmation = false
            },
            
            getHistoryList() {
                this.meta.history.loading = true
                
                this.historyService.list("rental", this.$route.params.rentalId, this.meta.history.perPage, this.meta.history.currentPage, null, null, this.meta.history.search)
                    .then(
                        (response) => {
                            this.histories = response.data.data
                            this.meta.history.totalRecords = response.data.total_rows
                            this.meta.history.totalPages = response.data.total_pages
                            this.meta.history.loading = false
                        },
                        (errors) => {
                            this.$toast.add({ severity: 'error', summary: this.$t('app.error'), detail: errors.response.data.message, life: 3000 });
                        }
                    );
            },
            
            changeHistoryPage(event) {
                this.meta.history.currentPage = event["page"] + 1;
                this.getHistoryList()
            },
        },
    }
</script>

<template>
    <Breadcrumb :model="meta.breadcrumbItems"/>
    
    <div class="mt-5 hidden">
        <strong>TODO:</strong>
        <ul>
            <li>Zwrot kaucji</li>
        </ul>
    </div>
    
    
    <div class="grid mt-1">
        <div class="col col-12">
            <div class="card">
                <div class="flex align-items-center">
                    <div class="w-full">
                        <h3 class="mt-2 mb-1 text-color">{{ $t('rent.rental_agreement_no_of', [rental.full_number, rental.document_date]) }}</h3>
                    </div>
                    <div class="text-right" v-if="rental.can_update && hasAccess('rent:update')">
                        <Button icon="pi pi-pencil" @click="edit" v-tooltip.left="$t('app.edit')"></Button>
                    </div>
                </div>
                
                <Message severity="error" :closable="false" v-if="rental.termination && rental.status == 'current'">
                    {{ $t('rent.rental_is_being_terminated') }}
                </Message>
            
                <div class="mt-4">
                    <div class="grid">
                        <div class="col-12 sm:col-6">
                            <div class="card text-center p-3 border-round-lg uppercase" :class="rental.balance < 0 ? 'text-red-600 border-red-600' : 'text-green-600  border-green-600'">
                                <div class="text-4xl font-semibold">
                                    <template v-if="rental.balance > 0">+</template>{{ numeralFormat(rental.balance, '0.00') }}
                                </div>
                                <div class="text-sm mt-1">
                                    {{ $t('rent.balance') }}
                                </div>
                            </div>
                        </div>
                        <div class="col-12 sm:col-6">
                            <div class="card text-center p-3 border-round-lg uppercase" :class="getRentalBoxColor(rental.status)">
                                <div class="text-4xl font-semibold">
                                    {{ getValueLabel('rental.statuses', rental.status) }}
                                </div>
                                <div class="text-sm mt-1">
                                    {{ $t('rent.status') }}
                                </div>
                            </div>
                        </div>
                    </div>
                    
                </div>
                
            
                <div class="grid mt-3">
                    <div class="col-12 xl:col-7">
                        <div class="grid">
                            <div class="col-fixed pt-0 pb-1" style="width: 186px">
                                <span class="font-medium">{{ $t('rent.tenant') }}:</span>
                            </div>
                            <div class="col-12 sm:col-7 pt-0 pb-1">
                                <router-link v-if="rental.tenant.id" target="_blank" :to="{name: 'tenant_show', params: { tenantId : rental.tenant.id }}">
                                    {{ rental.tenant.name }}
                                </router-link>
                                <span v-else>
                                    {{ rental.tenant.name }}
                                </span>
                            </div>
                            <div class="col-12 pb-2 pt-2"><div class="border-bottom-1 border-gray-200"></div></div>
                            
                            <template v-if="rental.tenant.type == 'person'">
                                <template v-if="rental.tenant.pesel">
                                    <div class="col-fixed pt-0 pb-1" style="width: 186px">
                                        <span class="font-medium">{{ $t('tenants.pesel') }}:</span>
                                    </div>
                                    <div class="col-12 sm:col-7 pt-0 pb-1">
                                        {{ rental.tenant.pesel }}
                                    </div>
                                    <div class="col-12 pb-2 pt-2"><div class="border-bottom-1 border-gray-200"></div></div>
                                </template>
                                <template v-if="rental.tenant.document_number">
                                    <div class="col-fixed pt-0 pb-1" style="width: 186px">
                                        <span class="font-medium">{{ $t('tenants.document_number') }}:</span>
                                    </div>
                                    <div class="col-12 sm:col-7 pt-0 pb-1">
                                        {{ rental.tenant.document_number }}
                                    </div>
                                    <div class="col-12 pb-2 pt-2"><div class="border-bottom-1 border-gray-200"></div></div>
                                </template>
                            </template>
                            
                            <template v-if="rental.tenant.type == 'firm'">
                                <template v-if="rental.tenant.nip">
                                    <div class="col-fixed pt-0 pb-1" style="width: 186px">
                                        <span class="font-medium">{{ $t('tenants.nip') }}:</span>
                                    </div>
                                    <div class="col-12 sm:col-7 pt-0 pb-1">
                                        {{ rental.tenant.nip }}
                                    </div>
                                    <div class="col-12 pb-2 pt-2"><div class="border-bottom-1 border-gray-200"></div></div>
                                </template>
                                <template v-if="rental.tenant.regon">
                                    <div class="col-fixed pt-0 pb-1" style="width: 186px">
                                        <span class="font-medium">{{ $t('tenants.regon') }}:</span>
                                    </div>
                                    <div class="col-12 sm:col-7 pt-0 pb-1">
                                        {{ rental.tenant.regon }}
                                    </div>
                                    <div class="col-12 pb-2 pt-2"><div class="border-bottom-1 border-gray-200"></div></div>
                                </template>
                            </template>
                        </div>
                        
                        <div class="grid mt-4">
                            <div class="col-fixed pt-0 pb-1" style="width: 186px">
                                <span class="font-medium">{{ $t('rent.estate') }}:</span>
                            </div>
                            <div class="col-12 sm:col-7 pt-0 pb-1">
                                 <router-link v-if="rental.item.id" target="_blank" :to="{name: 'item_show', params: { itemId : rental.item.id }}">
                                    {{ rental.item.name }}
                                </router-link>
                                <span v-else>
                                    {{ rental.item.name }}
                                </span>
                            </div>
                            <div class="col-12 pb-2 pt-2"><div class="border-bottom-1 border-gray-200"></div></div>
                            
                            <div class="col-fixed pt-0 pb-1" style="width: 186px">
                                <span class="font-medium">{{ $t('rent.period') }}:</span>
                            </div>
                            <div class="col-12 sm:col-7 pt-0 pb-1">
                                {{ rental.start }} - 
                                <span v-if="rental.period == 'indeterminate'">
                                    <span style="text-transform: lowercase">{{ $t('rent.indeterminate') }}</span>
                                </span>
                                <span v-if="rental.period == 'month'">
                                    {{ rental.end }}<br/>({{ rental.months }} {{ p(rental.months, $t('rent.1months'), $t('rent.2months'), $t('rent.3months')) }})
                                </span>
                                <span v-if="rental.period == 'date'">
                                    {{ rental.end }}
                                </span>
                            </div>
                            <div class="col-12 pb-2 pt-2"><div class="border-bottom-1 border-gray-200"></div></div>
                            
                            <div class="col-fixed pt-0 pb-1" style="width: 186px">
                                <span class="font-medium">{{ $t('rent.rent') }}:</span>
                            </div>
                            <div class="col-12 sm:col-7 pt-0 pb-1">
                                {{ numeralFormat(rental.rent, '0.00') }}
                            </div>
                            <div class="col-12 pb-2 pt-2"><div class="border-bottom-1 border-gray-200"></div></div>
                            
                            <template v-if="rental.payment == 'cyclical'">
                                <div class="col-fixed pt-0 pb-1" style="width: 186px">
                                    <span class="font-medium">{{ $t('rent.payment_day') }}:</span>
                                </div>
                                <div class="col-12 sm:col-7 pt-0 pb-1">
                                    {{ rental.payment_day }}{{ $t("rent.payment_day_postfix") }} {{ $t("rent.each_month") }}
                                </div>
                                <div class="col-12 pb-2 pt-2"><div class="border-bottom-1 border-gray-200"></div></div>
                            </template>
                        </div>
                    </div>
                    <div class="col-12 xl:col-5 relative">
                        <div class="grid">
                            <div class="col-fixed pt-0 pb-1" style="width: 120px">
                                <span class="font-medium">{{ $t('rent.terminate') }}:</span>
                            </div>
                            <div class="col-12 sm:col-7 pt-0 pb-1">
                                <span v-if="rental.termination_period == 'days'">
                                    {{ rental.termination_days }} {{ p(rental.termination_days, $t('rent.1days'), $t('rent.2days'), $t('rent.3days')) }}
                                </span>
                                <span v-if="rental.termination_period == 'months'">
                                    {{ rental.termination_months }} {{ p(rental.termination_months, $t('rent.1months'), $t('rent.2months'), $t('rent.3months')) }}
                                </span>
                            </div>
                            <div class="col-12 pb-2 pt-2"><div class="border-bottom-1 border-gray-200"></div></div>
                            
                            <div class="col-fixed pt-0 pb-1" style="width: 120px">
                                <span class="font-medium">{{ $t('rent.deposit') }}:</span>
                            </div>
                            <div class="col-12 sm:col-7 pt-0 pb-1">
                                <span v-if="rental.deposit">
                                    {{ numeralFormat(rental.deposit, '0.00') }}
                                    <Badge :value="$t('rent.deposit_paid')" class="font-normal ml-1" severity="success" v-if="rental.has_paid_deposit"></Badge>
                                    <Badge :value="$t('rent.deposit_unpaid')" class="font-normal ml-1" severity="danger" v-if="!rental.has_paid_deposit"></Badge>
                                </span>
                                <span v-else>-</span>
                            </div>
                            <div class="col-12 pb-2 pt-2"><div class="border-bottom-1 border-gray-200"></div></div>
                            
                            <div class="col-fixed pt-0 pb-1" style="width: 120px">
                                <span class="font-medium">{{ $t('rent.number_of_people') }}:</span>
                            </div>
                            <div class="col-12 sm:col-7 pt-0 pb-1">
                                {{ rental.number_of_people }}
                            </div>
                            <div class="col-12 pb-2 pt-2"><div class="border-bottom-1 border-gray-200"></div></div>
                        </div>
                            
                        <template v-if="rental.termination">
                            <div class="grid mt-4">
                                <div class="col-fixed pt-0 pb-1" style="width: 150px">
                                    <span class="font-medium">{{ $t('rent.terminate') }}:</span>
                                </div>
                                <div class="col-12 sm:col-7 pt-0 pb-1">
                                    {{ rental.termination_added }}
                                </div>
                                <div class="col-12 pb-2 pt-2"><div class="border-bottom-1 border-gray-200"></div></div>
                                
                                <div class="col-fixed pt-0 pb-1" style="width: 150px">
                                    <span class="font-medium">{{ $t('rent.terminate_end') }}:</span>
                                </div>
                                <div class="col-12 sm:col-7 pt-0 pb-1 font-medium text-red-500">
                                    {{ rental.termination_time }}
                                </div>
                                <div class="col-12 pb-2 pt-2"><div class="border-bottom-1 border-gray-200"></div></div>
                                
                                <template v-if="rental.termination_reason">
                                    <div class="col-12 pt-0 pb-1">
                                        <span class="font-medium">{{ $t('rent.termination_reason') }}:</span>
                                    </div>
                                    <div class="col-12 sm:col-7 pt-0 pb-1 font-italic">
                                        {{ rental.termination_reason }}
                                    </div>
                                </template>
                            </div>
                        </template>
                        
                        <div v-if="rental.status == 'current' && !rental.termination" class="mt-6">
                            <Button :label="$t('rent.terminate_contract')" @click="terminate" type="button" severity="danger" iconPos="right" icon="pi pi-delete-left" class="w-full text-center" v-if="hasAccess('rent:update')" />
                        </div>
                    </div>
                </div>
                
                <p class="m-0 mt-3" v-if="rental.comments">
                    <span class="font-medium">{{ $t('rent.comments') }}: </span>
                    <br/>
                    <i class="text-sm">{{ rental.comments}}</i>
                </p>
                
                <TabView class="mt-5">
                    <TabPanel :header="$t('rent.bills')">
                        <div class="flex justify-content-between align-items-center mb-1">
                            <h5 class="mb-3 mt-2 text-color font-medium">{{ $t('rent.bills') }}</h5>
                            <div class="text-right mb-0 inline-flex" v-if="hasAccess('rent:update')">
                                <Button icon="pi pi-plus" :label="$t('items.add_bill_short')" size="small" v-tooltip.left="$t('items.add_bill')" @click="newBill" class="text-center"></Button>
                            </div>
                        </div>
                    
                        <DataTable :rowClass="({ out_off_date }) => out_off_date ? 'bg-red-100': null" :value="bills" stripedRows class="p-datatable-gridlines clickable" :totalRecords="meta.bills.totalRecords" :rowHover="true" :lazy="true" :paginator="true" :pageCount="meta.bills.totalPages" :rows="meta.bills.perPage" @page="changeBillsPage" :loading="meta.bills.loading" @row-click="rowBillsClick($event)">
                            <Column :header="$t('items.bill_type')" style="min-width: 300px;">
                                <template #body="{ data }">
                                    <div class="mb-1 flex">
                                        <span v-if="data.out_off_date" class="mr-1" v-tooltip.top="$t('items.bill_out_off_date')">
                                            <i class="pi pi-exclamation-circle" style="font-size: 1.2rem; color: var(--red-600)"></i>
                                        </span>
                                        {{ data.bill_type.name }}
                                    </div>
                                    <div class="mt-1" v-if="data.cyclical">
                                        <small class="font-italic">
                                            <i class="pi pi-replay"></i>
                                            {{ $t("items.cyclical_fee") }}
                                        </small>
                                    </div>
                                </template>
                            </Column>
                            <Column :header="$t('items.payer')">
                                <template #body="{ data }">
                                    <span v-if="data.rental_id > 0">{{ $t('items.currently_tenant') }}</span>
                                    <span v-else>{{ $t('items.owner') }}</span>
                                </template>
                            </Column>
                            <Column :header="$t('items.cost')" class="text-right">
                                <template #body="{ data }">
                                    {{ numeralFormat(data.cost, '0.00') }}
                                </template>
                            </Column>
                            <Column :header="$t('items.payment_date')" class="text-center">
                                <template #body="{ data }">
                                    {{ data.payment_date }}
                                </template>
                            </Column>
                            <Column :header="$t('items.paid')" class="text-center">
                                <template #body="{ data }">
                                    <Badge :value="$t('app.yes')" class="uppercase font-normal" severity="success" v-if="data.paid"></Badge>
                                    <Badge :value="$t('app.no')" class="uppercase font-normal" severity="danger" v-if="!data.paid"></Badge>
                                    <div v-if="data.paid">
                                        <small>
                                            {{ data.paid_date }}
                                        </small>
                                    </div>
                                </template>
                            </Column>
                            <Column field="delete" v-if="hasAccess('rent:update')" style="min-width: 60px; width: 60px" class="text-center">
                                <template #body="{ data }">
                                    <Button :disabled="!data.can_delete" icon="pi pi-trash" v-tooltip.bottom="$t('app.remove')" class="p-button-danger p-2" style="width: auto" @click="openBillConfirmation(data.id)"/>
                                </template>
                            </Column>
                            <template #empty>
                                {{ $t('items.empty_bills_list') }}
                            </template>
                        </DataTable>
                        <Dialog :header="$t('app.confirmation')" v-model:visible="displayBillConfirmation" :style="{ width: '450px' }" :modal="true">
                            <div class="flex align-items-center justify-content-center">
                                <i class="pi pi-exclamation-triangle mr-3" style="font-size: 2rem" />
                                <span>{{ $t('app.remove_object_confirmation') }}</span>
                            </div>
                            <template #footer>
                                <Button :label="$t('app.no')" icon="pi pi-times" @click="closeBillConfirmation" class="p-button-text" />
                                <Button :label="$t('app.yes')" icon="pi pi-check" @click="confirmDeleteBill" class="p-button-danger" autofocus />
                            </template>
                        </Dialog>
                    </TabPanel>
                    
                    <TabPanel :header="$t('rent.documents')">
                        <div class="flex justify-content-between align-items-center mb-1">
                            <h5 class="mb-3 mt-2 text-color font-medium">{{ $t('rent.documents') }}</h5>
                            <div class="text-right mb-0 inline-flex" v-if="hasAccess('rent:update')">
                                <Button icon="pi pi-plus" :label="$t('items.add_document_short')" size="small" v-tooltip.left="$t('items.add_document')" @click="newDocument" class="text-center"></Button>
                            </div>
                        </div>
                        
                        <DataTable :value="documents" stripedRows class="p-datatable-gridlines" :class="hasAccess('rent:update') ? 'clickable' : ''" @row-click="rowDocumentsClick($event)" :totalRecords="meta.documents.totalRecords" :rowHover="true" :lazy="true" :paginator="true" :pageCount="meta.documents.totalPages" :rows="meta.documents.perPage" @page="changeDocumentsPage" :loading="meta.documents.loading">
                            <Column :header="$t('rent.document_title')" field="title" style="min-width: 300px;"></Column>
                            <Column :header="$t('rent.document_created')" class="text-center" field="created_at"></Column>
                            <Column :header="$t('rent.document_updated')" class="text-center" field="updated_at"></Column>
                            <Column class="text-center" style="min-width: 60px; width: 60px;">
                                <template #body="{ data }">
                                    <Button icon="pi pi-file-pdf" v-tooltip.bottom="$t('rent.download_document')" class="p-button-info p-2" style="width: auto" @click="downloadPDF(data.id)"/>
                                </template>
                            </Column>
                            <Column field="delete" v-if="hasAccess('rent:update')" style="min-width: 60px; width: 60px" class="text-center">
                                <template #body="{ data }">
                                    <Button icon="pi pi-trash" v-tooltip.bottom="$t('app.remove')" class="p-button-danger p-2" style="width: auto" @click="openDocumentConfirmation(data.id)"/>
                                </template>
                            </Column>
                            <template #empty>
                                {{ $t('rent.empty_documents_list') }}
                            </template>
                        </DataTable>
                        <Dialog :header="$t('app.confirmation')" v-model:visible="displayDocumentConfirmation" :style="{ width: '450px' }" :modal="true">
                            <div class="flex align-items-center justify-content-center">
                                <i class="pi pi-exclamation-triangle mr-3" style="font-size: 2rem" />
                                <span>{{ $t('app.remove_object_confirmation') }}</span>
                            </div>
                            <template #footer>
                                <Button :label="$t('app.no')" icon="pi pi-times" @click="closeDocumentConfirmation" class="p-button-text" />
                                <Button :label="$t('app.yes')" icon="pi pi-check" @click="confirmDeleteDocument" class="p-button-danger" autofocus />
                            </template>
                        </Dialog>
                    </TabPanel>
                    
                    <TabPanel :header="$t('rent.deposits')">
                        <div class="flex justify-content-between align-items-center mb-1">
                            <h5 class="mb-3 mt-2 text-color font-medium">{{ $t('rent.deposits') }}</h5>
                            <div class="text-right mb-0 inline-flex" v-if="hasAccess('rent:update')">
                                <Button icon="pi pi-plus" :label="$t('rent.add_payment_short')" size="small" v-tooltip.left="$t('rent.add_payment')" @click="newPayment" class="text-center"></Button>
                            </div>
                        </div>
                    
                        <DataTable :value="payments" stripedRows class="p-datatable-gridlines" :totalRecords="meta.payments.totalRecords" :rowHover="true" :lazy="true" :paginator="true" :pageCount="meta.payments.totalPages" :rows="meta.payments.perPage" @page="changePaymentsPage" :loading="meta.payments.loading">
                            <Column :header="$t('rent.due')">
                                <template #body="{ data }">
                                    <ul class="list-unstyled">
                                        <li v-for="item in data.associated_documents" class="pt-1 pb-1">
                                            <router-link :to="{name: 'rental_bill_show', params: { billId : item.id }}">
                                                {{ item.bill_type.name }}
                                            </router-link>
                                        </li>
                                    </ul>
                                </template>
                            </Column>
                            <Column :header="$t('rent.amount')" class="text-right">
                                <template #body="{ data }">
                                    {{ numeralFormat(data.amount, '0.00') }}
                                </template>
                            </Column>
                            <Column :header="$t('rent.paid_date')" class="text-center" field="paid_date"></Column>
                            <Column :header="$t('rent.payment_method')">
                                <template #body="{ data }">
                                    {{ getValueLabel('payments.methods', data.payment_method) }}
                                </template>    
                            </Column>
                            <Column :header="$t('rent.payment_accepted')">
                                <template #body="{ data }">
                                    <span v-if="data.created_by">
                                        {{ data.created_by.firstname }}
                                        {{ data.created_by.lastname }}
                                    </span>
                                </template>
                            </Column>
                            <Column field="delete" v-if="hasAccess('rent:update')" style="min-width: 60px; width: 60px" class="text-center">
                                <template #body="{ data }">
                                    <Button icon="pi pi-trash" v-tooltip.bottom="$t('app.remove')" class="p-button-danger p-2" style="width: auto" @click="openPaymentConfirmation(data.id)"/>
                                </template>
                            </Column>
                            <template #empty>
                                {{ $t('rent.empty_payment_list') }}
                            </template>
                        </DataTable>
                        
                        <Dialog :header="$t('app.confirmation')" v-model:visible="displayPaymentConfirmation" :style="{ width: '450px' }" :modal="true">
                            <div class="flex align-items-center justify-content-center">
                                <i class="pi pi-exclamation-triangle mr-3" style="font-size: 2rem" />
                                <span>{{ $t('app.remove_object_confirmation') }}</span>
                            </div>
                            <template #footer>
                                <Button :label="$t('app.no')" icon="pi pi-times" @click="closePaymentConfirmation" class="p-button-text" />
                                <Button :label="$t('app.yes')" icon="pi pi-check" @click="confirmDeletePayment" class="p-button-danger" autofocus />
                            </template>
                        </Dialog>
                    </TabPanel>
                    
                    <TabPanel :header="$t('rent.history')">
                        <div class="flex justify-content-between align-items-center mb-1">
                            <h5 class="mb-3 mt-2 text-color font-medium">{{ $t('rent.history') }}</h5>
                        </div>
                    
                        <DataTable :value="histories" stripedRows class="p-datatable-gridlines" :totalRecords="meta.history.totalRecords" :rowHover="true" :lazy="true" :paginator="true" :pageCount="meta.history.totalPages" :rows="meta.payments.perPage" @page="changeHistoryPage" :loading="meta.history.loading">
                            <Column :header="$t('history.user')" field="user"></Column>
                            <Column :header="$t('history.event')" field="event"></Column>
                            <Column :header="$t('history.diff')">
                                <template #body="{ data }">
                                    <ul class="list-unstyled" v-if="data.diff">
                                        <li v-for="item in data.diff">
                                            <span class="font-medium">{{ item.field }}:</span>
                                            {{ item.old }} &raquo; {{ item.new }}
                                        </li>
                                    </ul>
                                </template>
                            </Column>
                            <Column :header="$t('history.date')" field="created_at"></Column>
                            <template #empty>
                                {{ $t('history.empty_history_list') }}
                            </template>
                        </DataTable>
                    </TabPanel>
                </TabView>
            </div>
        </div>
    </div>
</template>