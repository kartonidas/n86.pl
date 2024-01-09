<script>
    import { getValueLabel, getResponseErrors, hasAccess, setMetaTitle, p } from '@/utils/helper'
    import { appStore } from '@/store.js'
    import Header from '@/views/app/_partials/Header.vue'
    
    import Address from '@/views/app/_partials/Address.vue'
    import Rental from '@/views/app/_partials/Rental.vue'
    import RentalService from '@/service/RentalService'
    
    export default {
        components: { Address, Header, Rental },
        setup() {
            setMetaTitle('meta.title.rent_show')
            
            const rentalService = new RentalService()
            
            return {
                p,
                rentalService,
                hasAccess,
                getValueLabel,
            }
        },
        data() {
            return {
                bills: [],
                documents: [],
                displayBillConfirmation: false,
                deleteBillId: null,
                displayDocumentConfirmation: false,
                deleteDocumentId: null,
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
                        perPage: this.rowsPerPage,
                        loading: false,
                        totalRecords: null,
                        totalPages: null,
                    },
                    documents: {
                        search: {},
                        currentPage: 1,
                        perPage: this.rowsPerPage,
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
                
            this.getBillList()
            this.getDocumentList()
        },
        methods: {
            terminate() {
                this.$router.push({name: 'rental_terminate'})
            },
            
            getBillList() {
                this.meta.loading = true
                
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
                this.meta.loading = true
                
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
            }
        },
    }
</script>

<template>
    <Breadcrumb :model="meta.breadcrumbItems"/>
    
    <div class="mt-5 hidden">
        <strong>TODO:</strong>
        <ul>
            <li>Aktualizacja dokumentów</li>
            <li>Lista wpłat</li>
            <li>Edycja danych najmu (tylko podstwawoe informacje, jak czynsz, data trwania, okres wypowiedzenia)</li>
            <li>Historia edycji (może nowa zakładka)</li>
            <li>Posprawdzać uprawnienia (w szczegolności menu)</li>
        </ul>
    </div>
    
    <div class="grid mt-1">
        <div class="col col-12">
            <div class="card">
                <h3 class="mb-5 text-color">
                    {{ $t('rent.rental_agreement_no_of', [rental.full_number, rental.document_date]) }}
                </h3>
                
                <Message severity="error" :closable="false" v-if="rental.termination && rental.status == 'current'">
                    {{ $t('rent.rental_is_being_terminated') }}
                </Message>
            
                <div class="grid mt-3">
                    <div class="col-12 xl:col-7">
                        <table class="table">
                            <tr>
                                <td class="font-medium" style="width: 165px">{{ $t('rent.tenant') }}:</td>
                                <td class="font-italic">
                                    <router-link v-if="rental.tenant.id" target="_blank" :to="{name: 'tenant_show', params: { tenantId : rental.tenant.id }}">
                                        {{ rental.tenant.name }}
                                    </router-link>
                                    <span v-else>
                                        {{ rental.tenant.name }}
                                    </span>
                                    <Badge :value="getValueLabel('tenant_types', rental.tenant.type)" class="font-normal ml-1" severity="info"></Badge>
                                </td>
                            </tr>
                            <tr v-if="rental.tenant.type == 'person' && rental.tenant.pesel">
                                <td class="font-medium">{{ $t('tenants.pesel') }}:</td>
                                <td class="font-italic">{{ rental.tenant.pesel }}</td>
                            </tr>
                            <tr v-if="rental.tenant.type == 'person' && rental.tenant.document_number">
                                <td class="font-medium">{{ $t('tenants.document_number') }}:</td>
                                <td class="font-italic">{{ rental.tenant.document_number }}</td>
                            </tr>
                            <tr v-if="rental.tenant.type == 'firm' && rental.tenant.nip">
                                <td class="font-medium">{{ $t('tenants.nip') }}:</td>
                                <td class="font-italic">{{ rental.tenant.nip }}</td>
                            </tr>
                            <tr v-if="rental.tenant.type == 'firm' && rental.tenant.regon">
                                <td class="font-medium">{{ $t('tenants.regon') }}:</td>
                                <td class="font-italic">{{ rental.tenant.regon }}</td>
                            </tr>
                        </table>
                        <table class="table mt-5">
                            <tr>
                                <td class="font-medium" style="width: 165px">{{ $t('rent.estate') }}:</td>
                                <td class="font-italic">
                                    <router-link v-if="rental.item.id" target="_blank" :to="{name: 'item_show', params: { itemId : rental.item.id }}">
                                        {{ rental.item.name }}
                                    </router-link>
                                    <span v-else>
                                        {{ rental.item.name }}
                                    </span>
                                    <Badge :value="getValueLabel('item_types', rental.item.type)" class="font-normal ml-1" severity="info"></Badge>
                                </td>
                            </tr>
                            <tr>
                                <td class="font-medium" style="width: 165px">{{ $t('rent.period') }}:</td>
                                <td class="font-italic">
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
                                </td>
                            </tr>
                            <tr>
                                <td class="font-medium">{{ $t('rent.rent') }}:</td>
                                <td class="font-italic">{{ numeralFormat(rental.rent, '0.00') }}</td>
                            </tr>
                            <tr v-if="rental.payment == 'cyclical'">
                                <td class="font-medium">{{ $t('rent.payment_day') }}:</td>
                                <td class="font-italic">{{ rental.payment_day }}{{ $t("rent.payment_day_postfix") }} {{ $t("rent.each_month") }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-12 xl:col-5 relative">
                        <table class="table">
                            <tr>
                                <td class="font-medium" style="width: 120px">{{ $t('rent.terminate') }}:</td>
                                <td class="font-italic">
                                    <span v-if="rental.termination_period == 'days'">
                                        {{ rental.termination_days }} {{ p(rental.termination_days, $t('rent.1days'), $t('rent.2days'), $t('rent.3days')) }}
                                    </span>
                                    <span v-if="rental.termination_period == 'months'">
                                        {{ rental.termination_months }} {{ p(rental.termination_months, $t('rent.1months'), $t('rent.2months'), $t('rent.3months')) }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td class="font-medium">{{ $t('rent.deposit') }}:</td>
                                <td class="font-italic">
                                    <span v-if="rental.deposit">
                                        {{ numeralFormat(rental.deposit, '0.00') }} (opłacona???)
                                    </span>
                                    <span v-else>-</span>
                                </td>
                            </tr>
                            <tr>
                                <td class="font-medium">{{ $t('rent.number_of_people') }}:</td>
                                <td class="font-italic">{{ rental.number_of_people }}</td>
                            </tr>
                        </table>
                        
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
                
                <div class="mt-5">
                    <div class="flex justify-content-between align-items-center mb-1">
                        <h5 class="mb-3 mt-2 text-color font-medium">{{ $t('rent.documents') }}</h5>
                        <div class="text-right mb-0 inline-flex" v-if="hasAccess('rent:update')">
                            <Button icon="pi pi-plus" :label="$t('items.add_document_short')" size="small" v-tooltip.left="$t('items.add_document')" @click="newDocument" class="text-center"></Button>
                        </div>
                    </div>
                    
                    <DataTable :value="documents" stripedRows class="p-datatable-gridlines clickable" :totalRecords="meta.documents.totalRecords" :rowHover="true" :lazy="true" :paginator="true" :pageCount="meta.documents.totalPages" :rows="meta.documents.perPage" @page="changeDocumentsPage" :loading="meta.documents.loading">
                        <Column :header="$t('rent.document_title')" field="title" style="min-width: 300px;"></Column>
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
                </div>
                
                <div class="mt-5">
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
                </div>
            </div>
        </div>
    </div>
</template>