<script>
    import { hasAccess, setMetaTitle, getValueLabel, getValues } from '@/utils/helper'
    import { appStore } from '@/store.js'
    import moment from 'moment'
    
    import Address from '@/views/app/_partials/Address.vue'
    import RentalService from '@/service/RentalService'
    
    export default {
        components: { Address },
        setup() {
            setMetaTitle('meta.title.rentals_list')
            
            const rentalService = new RentalService()
            
            return {
                rentalService,
                hasAccess,
                getValueLabel
            }
        },
        data() {
            return {
                loading: false,
                errors: [],
                rentals: [],
                item_types: getValues('item_types'),
                tenant_types: getValues('tenant_types'),
                statuses: getValues('rental.statuses'),
                meta: {
                    search: {},
                    currentPage: 1,
                    perPage: this.rowsPerPage,
                    totalRecords: null,
                    totalPages: null,
                    breadcrumbItems: [
                        {'label' : this.$t('menu.estates'), disabled : true },
                        {'label' : this.$t('menu.rentals_list'), disabled : true },
                    ],
                }
            }
        },
        beforeMount() {
            let filter = appStore().getTableFilter('rentals');
            if (filter != undefined)
            {
                this.meta.search = filter;
                this.meta.search.start = this.meta.search.start ? new Date(this.meta.search.start) : null
                this.meta.search.end = this.meta.search.end ? new Date(this.meta.search.end) : null
            }
                
            if(appStore().toastMessage) {
                let m = appStore().toastMessage
                this.$toast.add({ severity: m.severity, summary: m.summary, detail: m.detail, life: 3000 });
                appStore().setToastMessage(null)
            }
            this.getList()
        },
        methods: {
            getList() {
                this.loading = true
                
                let search = Object.assign({}, this.meta.search)
                search.start = search.start ? moment(search.start).format("YYYY-MM-DD") : null
                search.end = search.end ? moment(search.end).format("YYYY-MM-DD") : null
                
                this.rentalService.list(this.meta.perPage, this.meta.currentPage, null, null, search)
                    .then(
                        (response) => {
                            this.rentals = response.data.data
                            this.meta.totalRecords = response.data.total_rows
                            this.meta.totalPages = response.data.total_pages
                            this.loading = false
                        },
                        (errors) => {
                            this.$toast.add({ severity: 'error', summary: this.$t('app.error'), detail: errors.response.data.message, life: 3000 });
                        }
                    );
            },
            
            newRent() {
                this.$router.push({name: 'rent_source_direct'})
            },
            
            showRental(rentalId) {
                this.$router.push({name: 'rental_show', params: { rentalId : rentalId }})
            },
            
            changePage(event) {
                this.meta.currentPage = event["page"] + 1;
                this.getList()
            },
            
            rowClick(event) {
                this.showRental(event.data.id)
            },
            
            search() {
                this.meta.currentPage = 1
                appStore().setTableFilter('rentals', this.meta.search)
                this.getList()
            },
            
            resetSearch() {
                this.meta.currentPage = 1
                this.meta.search = {}
                appStore().setTableFilter('rentals', this.meta.search)
                this.getList()
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
                    <h4 class="inline-flex mb-0 text-color font-medium">{{ $t('menu.rentals_list') }}</h4>
                    <div class="text-right mb-0 inline-flex" v-if="hasAccess('rent:create')">
                        <Button icon="pi pi-plus" v-tooltip.left="$t('rent.add_rent')" @click="newRent" class="text-center"></Button>
                    </div>
                </div>
                <form v-on:submit.prevent="search">
                    <div class="formgrid grid mb-1">
                        <div class="col-12 md:col-4 mb-3">
                            <InputText type="text" :placeholder="$t('rent.item_name')" class="w-full" v-model="meta.search.item_name"/>
                        </div>
                        <div class="col-12 md:col-4 mb-3">
                            <Dropdown v-model="meta.search.item_type" :showClear="this.meta.search.item_type ? true : false" :options="item_types" optionLabel="name" optionValue="id" :placeholder="$t('rent.item_type')" class="w-full" />
                        </div>
                        <div class="col-12 md:col-4 mb-3">
                            <InputText type="text" :placeholder="$t('rent.item_address')" class="w-full" v-model="meta.search.item_address"/>
                        </div>
                        
                        <div class="col-12 md:col-4 mb-3">
                            <InputText type="text" :placeholder="$t('rent.tenant_name')" class="w-full" v-model="meta.search.tenant_name"/>
                        </div>
                        <div class="col-12 md:col-4 mb-3">
                            <Dropdown v-model="meta.search.tenant_type" :showClear="this.meta.search.tenant_type ? true : false" :options="tenant_types" optionLabel="name" optionValue="id" :placeholder="$t('rent.tenant_type')" class="w-full" />
                        </div>
                        <div class="col-12 md:col-4 mb-3">
                            <InputText type="text" :placeholder="$t('rent.tenant_address')" class="w-full" v-model="meta.search.tenant_address"/>
                        </div>
                        
                        <div class="col-12 md:col-4 mb-3">
                            <Dropdown v-model="meta.search.status" :showClear="this.meta.search.status ? true : false" :options="statuses" optionLabel="name" optionValue="id" :placeholder="$t('rent.status')" class="w-full" />
                        </div>
                        
                        <div class="col-12 md:col-6 sm:col-9 mb-3">
                            <div class="flex">
                                <div class="col-6 p-0 pr-1">
                                    <Calendar id="start" v-model="meta.search.start" :placeholder="$t('rent.start_date')" class="w-auto" showIcon/>
                                </div>
                                <div class="col-6 p-0 pl-1">
                                    <Calendar id="end" v-model="meta.search.end" :placeholder="$t('rent.end_date')" showIcon/>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-12 mb-3" style="width: 120px;">
                            <Button type="submit" iconPos="left" icon="pi pi-search" class="mr-2"/>
                            <Button severity="secondary" outlined iconPos="left" icon="pi pi-filter-slash" @click="resetSearch"/>
                        </div>
                    </div>
                </form>
                <DataTable :value="rentals" stripedRows class="p-datatable-gridlines clickable" :totalRecords="meta.totalRecords" :rowHover="true" :lazy="true" :paginator="true" :pageCount="meta.totalPages" :rows="meta.perPage" @page="changePage" :loading="loading" @row-click="rowClick($event)">
                    <Column :header="$t('rent.estate')" style="min-width: 300px;">
                        <template #body="{ data }">
                            <Badge :value="getValueLabel('item_types', data.item.type)" class="font-normal" severity="info"></Badge>
                            <div class="mt-1">
                                <router-link :to="{name: 'item_show', params: { itemId : data.item.id }}">
                                    {{ data.item.name }}
                                </router-link>
                                
                                <div>
                                    <small>
                                        <Address :object="data.item" :newline="true" emptyChar=""/>
                                    </small>
                                </div>
                            </div>
                        </template>
                    </Column>
                    <Column :header="$t('rent.tenant')" style="min-width: 300px;">
                        <template #body="{ data }">
                            <Badge :value="getValueLabel('tenant_types', data.tenant.type)" class="font-normal" severity="info"></Badge>
                            <div class="mt-1">
                                <router-link :to="{name: 'tenant_show', params: { tenantId : data.tenant.id }}">
                                    {{ data.tenant.name }}
                                </router-link>
                                
                                <div>
                                    <small>
                                        <Address :object="data.tenant" :newline="true" emptyChar=""/>
                                    </small>
                                </div>
                            </div>
                        </template>
                    </Column>
                    <Column :header="$t('rent.rent')">
                        <template #body="{ data }">
                            {{ numeralFormat(data.rent, '0.00') }}
                        </template>
                    </Column>
                    <Column :header="$t('rent.status')">
                        <template #body="{ data }">
                            {{ getValueLabel('rental.statuses', data.status) }}
                        </template>
                    </Column>
                    <Column :header="$t('rent.period_short')">
                        <template #body="{ data }">
                            {{ data.start }} - 
                            <span v-if="data.period == 'indeterminate'">{{ $t("rent.indeterminate") }}</span>
                            <span v-else>{{ data.end }}</span>
                        </template>
                    </Column>
                    <template #empty>
                        {{ $t('rent.empty_list') }}
                    </template>
                </DataTable>
            </div>
        </div>
    </div>
    
</template>