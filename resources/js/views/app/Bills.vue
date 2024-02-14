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
            setMetaTitle('meta.title.bills')
            
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
                bills: [],
                paid_status: [
                    {"id": 0, "name" : this.$t('items.unpaid')},
                    {"id": 1, "name" : this.$t('items.paid')},
                ],
                billTypes: getValues("bills.system_types"),
                loadingBillTypesDictionary: false,
                meta: {
                    loading: false,
                    currentPage: 1,
                    perPage: this.rowsPerPage,
                    totalRecords: null,
                    totalPages: null,
                    breadcrumbItems: [
                        {'label' : this.$t('menu.estates'), disabled : true },
                        {'label' : this.$t('menu.bills'), disabled : true },
                    ],
                    search: {}
                }
            }
        },
        beforeMount() {
            let filter = appStore().getTableFilter('bills');
            if (filter != undefined)
            {
                this.meta.search = filter;
                this.meta.search.payment_date_from = this.meta.search.payment_date_from ? new Date(this.meta.search.payment_date_from) : null
                this.meta.search.payment_date_to = this.meta.search.payment_date_to ? new Date(this.meta.search.payment_date_to) : null
            }
                
            if(appStore().toastMessage) {
                let m = appStore().toastMessage
                this.$toast.add({ severity: m.severity, summary: m.summary, detail: m.detail, life: 3000 });
                appStore().setToastMessage(null)
            }
            this.getList()
            this.getBillTypes()
        },
        methods: {
            getList() {
                this.meta.loading = true
                
                let search = Object.assign({}, this.meta.search)
                search.payment_date_from = search.payment_date_from ? moment(search.payment_date_from).format("YYYY-MM-DD") : null
                search.payment_date_to = search.payment_date_to ? moment(search.payment_date_to).format("YYYY-MM-DD") : null
                
                this.itemService.allBills(this.meta.perPage, this.meta.currentPage, null, null, search)
                    .then(
                        (response) => {
                            this.bills = response.data.data
                            this.meta.totalRecords = response.data.total_rows
                            this.meta.totalPages = response.data.total_pages
                            this.meta.loading = false
                        },
                        (errors) => {
                            this.$toast.add({ severity: 'error', summary: this.$t('app.error'), detail: errors.response.data.message, life: 3000 });
                        }
                    );
            },
            
            getBillTypes() {
                this.billTypes = getValues("bills.system_types")
                this.loadingBillTypesDictionary = true
                this.dictionaryService.listByType('bills', 999, 1)
                    .then(
                        (response) => {
                            response.data.data.forEach((type) => {
                                this.billTypes.push(type)
                            })
                            this.loadingBillTypesDictionary = false
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
                appStore().setTableFilter('bills', this.meta.search)
                this.getList()
            },
            
            resetSearch() {
                this.meta.currentPage = 1
                this.meta.search = {}
                appStore().setTableFilter('bills', this.meta.search)
                this.getList()
            },
            
            rowClick(event) {
                this.$router.push({name: 'item_bill_show', params: { itemId : event.data.item_id, billId : event.data.id }})
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
                    <h4 class="inline-flex mb-0 text-color font-medium">{{ $t('menu.bills') }}</h4>
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
                            <Dropdown id="bill_type_id" v-model="meta.search.bill_type_id" :filter="true" :loading="loadingBillTypesDictionary" :options="billTypes" optionLabel="name" optionValue="id" :placeholder="$t('items.bill_type')" class="w-full"/>
                        </div>
                        
                        <div class="col-12 md:col-3 mb-3">
                            <Dropdown v-model="meta.search.paid" :showClear="this.meta.search.paid !== '' ? true : false" :options="paid_status" optionLabel="name" optionValue="id" :placeholder="$t('items.payment_status')" class="w-full" />
                        </div>
                        
                        <div class="col-12 md:col-7 sm:col-9 mb-3">
                            <div class="flex">
                                <div class="col-6 p-0 pr-1">
                                    <Calendar id="start" v-model="meta.search.payment_date_from" :placeholder="$t('items.payment_date_from')" class="w-auto" showIcon/>
                                </div>
                                <div class="col-6 p-0 pl-1">
                                    <Calendar id="end" v-model="meta.search.payment_date_to" :placeholder="$t('items.payment_date_to')" showIcon/>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-12 mb-3" style="width: 120px;">
                            <Button type="submit" iconPos="left" icon="pi pi-search" class="mr-2"/>
                            <Button severity="secondary" outlined iconPos="left" icon="pi pi-filter-slash" @click="resetSearch"/>
                        </div>
                    </div>
                </form>
                
                <DataTable :rowClass="({ out_off_date }) => out_off_date ? 'bg-red-100': null" :value="bills" stripedRows class="p-datatable-gridlines clickable" :totalRecords="meta.totalRecords" :rowHover="true" :lazy="true" :paginator="true" :pageCount="meta.totalPages" :rows="meta.perPage" @page="changePage" :loading="meta.loading" @row-click="rowClick($event)" stateStorage="session" stateKey="dt-state-bills-table">
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
                    <Column :header="$t('items.cost')" class="text-right">
                        <template #body="{ data }">
                            {{ numeralFormat(data.cost, '0.00') }} {{ data.currency }}
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
                    <Column :header="$t('items.payer')">
                        <template #body="{ data }">
                            <span v-if="data.rental_id > 0">
                                <span v-if="data.rental">
                                    <div>{{ $t('items.tenant') }}</div>
                                    <small>
                                        <router-link :to="{name: 'rental_show', params: { rentalId : data.rental_id }}" v-if="hasAccess('rent:list')" target="_blank">
                                            {{ data.rental.full_number }}
                                        </router-link>
                                    </small>
                                </span>
                                <span v-else>
                                    {{ $t('items.currently_tenant') }}
                                </span>
                            </span>
                            <span v-else>{{ $t('items.owner') }}</span>
                        </template>
                    </Column>
                    
                    <template #empty>
                        {{ $t('items.empty_bills_list') }}
                    </template>
                </DataTable>
            </div>
        </div>
    </div>
</template>