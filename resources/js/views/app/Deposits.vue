<script>
    import { hasAccess, setMetaTitle, getValueLabel, getValues } from '@/utils/helper'
    import { appStore } from '@/store.js'
    
    import Address from '@/views/app/_partials/Address.vue'
    import DictionaryService from '@/service/DictionaryService'
    import ItemService from '@/service/ItemService'
    import moment from 'moment'
    
    export default {
        components: { Address },
        setup() {
            setMetaTitle('meta.title.deposits')
            
            const itemService = new ItemService()
            const dictionaryService = new DictionaryService()
            
            return {
                itemService,
                dictionaryService,
                hasAccess,
                getValueLabel
            }
        },
        data() {
            return {
                deposits: [],
                paymentMethods: [],
                loadingPaymentMethodDictionary: false,
                meta: {
                    loading: false,
                    currentPage: 1,
                    perPage: this.rowsPerPage,
                    totalRecords: null,
                    totalPages: null,
                    breadcrumbItems: [
                        {'label' : this.$t('menu.estates'), disabled : true },
                        {'label' : this.$t('menu.deposits'), disabled : true },
                    ],
                    search: {}
                }
            }
        },
        beforeMount() {
            let filter = appStore().getTableFilter('deposits');
            if (filter != undefined)
            {
                this.meta.search = filter;
                this.meta.search.paid_date_from = this.meta.search.paid_date_from ? new Date(this.meta.search.paid_date_from) : null
                this.meta.search.paid_date_to = this.meta.search.paid_date_to ? new Date(this.meta.search.paid_date_to) : null
            }
                
            if(appStore().toastMessage) {
                let m = appStore().toastMessage
                this.$toast.add({ severity: m.severity, summary: m.summary, detail: m.detail, life: 3000 });
                appStore().setToastMessage(null)
            }
            this.getList()
            this.getPaymentMethods()
        },
        methods: {
            getList() {
                this.meta.loading = true
                
                let search = Object.assign({}, this.meta.search)
                search.paid_date_from = search.paid_date_from ? moment(search.paid_date_from).format("YYYY-MM-DD") : null
                search.paid_date_to = search.paid_date_to ? moment(search.paid_date_to).format("YYYY-MM-DD") : null
                
                this.itemService.allDeposits(this.meta.perPage, this.meta.currentPage, null, null, search)
                    .then(
                        (response) => {
                            this.deposits = response.data.data
                            this.meta.totalRecords = response.data.total_rows
                            this.meta.totalPages = response.data.total_pages
                            this.meta.loading = false
                        },
                        (errors) => {
                            this.$toast.add({ severity: 'error', summary: this.$t('app.error'), detail: errors.response.data.message, life: 3000 });
                        }
                    );
            },
            
            getPaymentMethods() {
                this.paymentMethods = []
                this.loadingPaymentMethodDictionary = true
                this.dictionaryService.listByType('payment_types', 999, 1)
                    .then(
                        (response) => {
                            this.paymentMethods = response.data.data
                            this.loadingPaymentMethodDictionary = false
                        },
                        (errors) => {
                            this.$toast.add({ severity: 'error', summary: this.$t('app.error'), detail: errors.response.data.message, life: 3000 });
                        }
                    );
            },
            
            changePage(event) {
                this.meta.currentPage = event["page"] + 1;
                this.getList()
            },
            
            search() {
                this.meta.currentPage = 1
                appStore().setTableFilter('deposits', this.meta.search)
                this.getList()
            },
            
            resetSearch() {
                this.meta.currentPage = 1
                this.meta.search = {}
                appStore().setTableFilter('deposits', this.meta.search)
                this.getList()
            },
        },
    }
</script>

<template>
    <Breadcrumb :model="meta.breadcrumbItems"/>
    <div class="grid mt-1">
        <div class="col-12">
            <div class="card">
                <div class="flex justify-content-between align-items-center mb-5">
                    <h4 class="inline-flex mb-0 text-color font-medium">{{ $t('menu.deposits') }}</h4>
                </div>
                
                <form v-on:submit.prevent="search">
                    <div class="formgrid grid mb-1">
                        <div class="col-12 md:col-4 mb-3">
                            <InputText type="text" :placeholder="$t('items.name')" class="w-full" v-model="meta.search.item_name"/>
                        </div>
                        <div class="col-12 md:col-4 mb-3">
                            <InputText type="text" :placeholder="$t('items.address')" class="w-full" v-model="meta.search.item_address"/>
                        </div>
                        
                        <div class="col-12 md:col-4 sm:col-12 mb-3">
                            <Dropdown id="payment_method" v-model="meta.search.payment_method" :loading="loadingPaymentMethodDictionary" :options="paymentMethods" optionLabel="name" optionValue="id" :placeholder="$t('items.payment_method')" class="w-full"/>
                        </div>
                        
                        <div class="col-12 md:col-7 sm:col-9 mb-3">
                            <div class="flex">
                                <div class="col-6 p-0 pr-1">
                                    <Calendar id="start" v-model="meta.search.paid_date_from" :placeholder="$t('items.paid_date_from')" class="w-auto" showIcon/>
                                </div>
                                <div class="col-6 p-0 pl-1">
                                    <Calendar id="end" v-model="meta.search.paid_date_to" :placeholder="$t('items.paid_date_to')" showIcon/>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-12 mb-3" style="width: 120px;">
                            <Button type="submit" iconPos="left" icon="pi pi-search" class="mr-2"/>
                            <Button severity="secondary" outlined iconPos="left" icon="pi pi-filter-slash" @click="resetSearch"/>
                        </div>
                    </div>
                </form>
                
                <DataTable :rowClass="({ out_off_date }) => out_off_date ? 'bg-red-100': null" :value="deposits" stripedRows class="p-datatable-gridlines" :totalRecords="meta.totalRecords" :rowHover="true" :lazy="true" :paginator="true" :pageCount="meta.totalPages" :rows="meta.perPage" @page="changePage" :loading="meta.loading" stateStorage="session" stateKey="dt-state-deposits-table">
                    <Column :header="$t('items.estate')" style="min-width: 300px;">
                        <template #body="{ data }">
                            <div :class="data.item.mode == 'archived' ? 'archived-item' : ''">
                                <Badge :value="getValueLabel('item_types', data.item.type)" class="font-normal" severity="info"></Badge>
                                <div class="mt-1">
                                    <i class="pi pi-lock pr-1" v-if="data.item.mode == 'locked'" v-tooltip.top="$t('items.locked')"></i>
                                    {{ data.item.name }}
                                    
                                    <div>
                                        <small>
                                            <Address :object="data.item" :newline="true" emptyChar=""/>
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </Column>
                    <Column :header="$t('rent.due')">
                        <template #body="{ data }">
                            <ul class="list-unstyled">
                                <li v-for="item in data.associated_documents" class="pt-1 pb-1">
                                    <router-link :to="{name: 'rental_bill_show', params: { billId : item.id, rentalId : item.rental_id }}">
                                        {{ item.bill_type.name }}
                                    </router-link>
                                </li>
                            </ul>
                        </template>
                    </Column>
                    <Column :header="$t('rent.amount')" class="text-right">
                        <template #body="{ data }">
                            {{ numeralFormat(data.amount, '0.00') }} {{ data.currency }}
                        </template>
                    </Column>
                    <Column :header="$t('rent.paid_date')" class="text-center" field="paid_date"></Column>
                    <Column :header="$t('rent.payment_method')">
                        <template #body="{ data }">
                            {{ getValueLabel('payments.methods', data.payment_method) }}
                        </template>    
                    </Column>
                    <template #empty>
                        {{ $t('items.empty_deposits_list') }}
                    </template>
                </DataTable>
            </div>
        </div>
    </div>
</template>