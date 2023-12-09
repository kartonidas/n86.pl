<script>
    import { getValueLabel, getResponseErrors, hasAccess, setMetaTitle, timeToDate } from '@/utils/helper'
    import { appStore } from '@/store.js'
    
    import Address from '@/views/app/_partials/Address.vue'
    import Rental from '@/views/app/_partials/Rental.vue'
    import ItemService from '@/service/ItemService'
    import RentalService from '@/service/RentalService'
    
    export default {
        components: { Address, Rental },
        setup() {
            setMetaTitle('meta.title.items_show')
            
            const itemService = new ItemService()
            const rentalService = new RentalService()
            
            return {
                itemService,
                rentalService,
                hasAccess,
                getValueLabel,
                timeToDate
            }
        },
        data() {
            return {
                errors: [],
                item: {
                    customer: {}
                },
                loading: true,
                rentals: [],
                archive_rentals: [],
                meta: {
                    rentals: {
                        currentPage: 1,
                        perPage: this.rowsPerPage,
                        totalRecords: null,
                        totalPages: null,
                        loading: true
                    },
                    archive_rentals: {
                        currentPage: 1,
                        perPage: this.rowsPerPage,
                        totalRecords: null,
                        totalPages: null,
                        loading: true
                    },
                    breadcrumbItems: [
                        {'label' : this.$t('menu.estates'), disabled : true },
                        {'label' : this.$t('menu.estate_list'), route : { name : 'items'} },
                        {'label' : this.$t('items.edit'), disabled : true },
                    ],
                }
            }
        },
        beforeMount() {
            if(appStore().toastMessage) {
                let m = appStore().toastMessage
                this.$toast.add({ severity: m.severity, summary: m.summary, detail: m.detail, life: 3000 });
                appStore().setToastMessage(null)
            }
            
            this.itemService.get(this.$route.params.itemId)
                .then(
                    (response) => {
                        this.item = response.data
                        this.loading = false
                        
                        this.getReservationList()
                        this.getArchiveList()
                    },
                    (response) => {
                        this.$toast.add({ severity: 'error', summary: this.$t('app.error'), detail: response.response.data.message, life: 3000 });
                    }
                );
        },
        methods: {
            editItem() {
                this.$router.push({name: 'item_edit', params: { itemId : this.$route.params.itemId }})
            },
            
            rentItem() {
                this.$router.push({name: 'rent_source_item', params: { itemId : this.$route.params.itemId }})
            },
            
            getReservationList() {
                const search = {
                    item_id : this.$route.params.itemId,
                    status : 'waiting',
                };
                this.rentalService.list(this.meta.rentals.perPage, this.meta.rentals.currentPage, 'start', 1, search)
                    .then(
                        (response) => {
                            this.rentals = response.data.data
                            this.meta.rentals.totalRecords = response.data.total_rows
                            this.meta.rentals.totalPages = response.data.total_pages
                            this.meta.rentals.loading = false
                        },
                        (errors) => {
                        }
                    )
            },
            
            changeRentalsPage(event) {
                this.meta.rentals.currentPage = event["page"] + 1;
                this.getReservationList()
            },
            
            getArchiveList() {
                const search = {
                    item_id : this.$route.params.itemId,
                    status : 'archive',
                };
                this.rentalService.list(this.meta.archive_rentals.perPage, this.meta.archive_rentals.currentPage, 'end', -1, search)
                    .then(
                        (response) => {
                            this.archive_rentals = response.data.data
                            this.meta.archive_rentals.totalRecords = response.data.total_rows
                            this.meta.archive_rentals.totalPages = response.data.total_pages
                            this.meta.archive_rentals.loading = false
                        },
                        (errors) => {
                        }
                    )
            },
            
            changeArchivePage(event) {
                this.meta.archive_rentals.currentPage = event["page"] + 1;
                this.getArchiveList()
            },
            
            rowRentalsClick(event) {
            }
        },
    }
</script>

<template>
    <Breadcrumb :model="meta.breadcrumbItems"/>
    
    <div class="grid mt-1" v-if="!loading">
        <div class="col col-12">
            <div class="grid mt-1">
                <div class="col col-12 lg:col-6">
                    <Card class="mb-3">
                       <template #title>
                           <div class="flex justify-content-between align-items-center mb-3 text-color font-medium">
                               {{ item.name }}
                               <div v-if="hasAccess('item:update')">
                                   <Button icon="pi pi-pencil" @click="editItem" v-tooltip.left="$t('app.edit')"></Button>
                               </div>
                           </div>
                       </template>
                       <template #content pt="item">
                           <p class="m-0">
                               <span class="font-medium">{{ $t('items.estate_type') }}: </span> <i>{{ getValueLabel('item_types', item.type) }}</i>
                           </p>
                           <p class="m-0 mt-2">
                               <span class="font-medium">{{ $t('items.address') }}: </span> <i><Address :object="item"/></i>
                           </p>
                           <p class="m-0 mt-2" v-if="item.area">
                               <span class="font-medium">{{ $t('items.area') }}: </span> <i>{{ numeralFormat(item.area, '0.00') }} (m2)</i>
                           </p>
                           <p class="m-0 mt-2" v-if="item.num_rooms">
                               <span class="font-medium">{{ $t('items.number_of_rooms') }}: </span> <i>{{ item.num_rooms }}</i>
                           </p>
                           <p class="m-0 mt-2" v-if="item.default_rent">
                               <span class="font-medium">{{ $t('items.default_rent_value') }}: </span> <i>{{ numeralFormat(item.default_rent, '0.00') }}</i>
                           </p>
                           <p class="m-0 mt-2" v-if="item.default_deposit">
                               <span class="font-medium">{{ $t('items.default_deposit_value') }}: </span> <i>{{ numeralFormat(item.default_deposit, '0.00') }}</i>
                           </p>
                           <p class="m-0 mt-2">
                               <span class="font-medium">{{ $t('items.ownership') }}: </span>
                               <i>
                                   {{ getValueLabel('ownership_types', item.ownership_type) }}
                                   <span v-if="item.ownership_type == 'manage'">:
                                       <router-link v-if="item.customer.id" :to="{name: 'customer_show', params: { customerId : item.customer.id }}">
                                           {{ item.customer.name }}
                                       </router-link>
                                   </span>
                               </i>
                           </p>
                       </template>
                   </Card>
                </div>
                <div class="col col-12 lg:col-6">
                    <Card class="mb-3">
                        <template #title>
                           <div class="flex justify-content-between align-items-center mb-3 text-color font-medium">
                                <span class="mt-2">
                                    {{ $t("items.currently_tenant") }}
                                </span>
                           </div>
                        </template>
                        <template #content pt="item">
                            <div v-if="item.current_rental">
                                <Rental :object="item.current_rental" />
                            </div>
                            <div v-else>
                                <div class="text-center">
                                    <div class="mt-4 mb-4 text-lg font-medium text-red-500">
                                        {{ $t('items.currently_no_tenant') }}
                                    </div>
                                    <Button :label="$t('items.start_rental')" @click="rentItem" type="button" severity="secondary" iconPos="right" icon="pi pi-briefcase" class="w-auto text-center" />
                                </div>
                            </div>
                        </template>
                    </Card>
                </div>
            </div>
        </div>
        <div class="col col-12">
            <Card class="mb-3">
                <template #title>
                    <div class="flex justify-content-between align-items-center mb-3 text-color font-medium">
                        {{ $t("items.reservations") }}
                        <div v-if="hasAccess('rent:create')">
                            <Button icon="pi pi-plus" @click="rentItem" v-tooltip.left="$t('items.add_new_tenant')"></Button>
                        </div>
                    </div>
                </template>
                <template #content pt="item">
                    <DataTable :value="rentals" stripedRows class="p-datatable-gridlines" :totalRecords="meta.rentals.totalRecords" :rowHover="true" :lazy="true" :paginator="true" :pageCount="meta.rentals.totalPages" :rows="meta.rentals.perPage" @page="changeRentalsPage" :loading="meta.rentals.loading" @row-click="rowRentalsClick($event)">
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
                        <Column :header="$t('rent.period_short')">
                            <template #body="{ data }">
                                {{ timeToDate(data.start) }} - 
                                <span v-if="data.period == 'indeterminate'">{{ $t("rent.indeterminate") }}</span>
                                <span v-else>{{ timeToDate(data.end) }}</span>
                            </template>
                        </Column>
                        <Column :header="$t('rent.rent')">
                            <template #body="{ data }">
                                {{ numeralFormat(data.rent, '0.00') }}
                            </template>
                        </Column>
                        <template #empty>
                            {{ $t('rent.empty_reservation_list') }}
                        </template>
                    </DataTable>
                </template>
            </Card>
        </div>
        
        <div class="col col-12">
            <Card class="mb-3">
                <template #title>
                    <div class="flex justify-content-between align-items-center mb-3 text-color font-medium">
                        {{ $t("items.history_rentals") }}
                    </div>
                </template>
                <template #content pt="item">
                    <DataTable :value="archive_rentals" stripedRows class="p-datatable-gridlines" :totalRecords="meta.archive_rentals.totalRecords" :rowHover="true" :lazy="true" :paginator="true" :pageCount="meta.archive_rentals.totalPages" :rows="meta.archive_rentals.perPage" @page="changeArchivePage" :loading="meta.archive_rentals.loading" @row-click="rowRentalsClick($event)">
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
                        <Column :header="$t('rent.period_short')">
                            <template #body="{ data }">
                                {{ timeToDate(data.start) }} - 
                                <span v-if="data.period == 'indeterminate'">{{ $t("rent.indeterminate") }}</span>
                                <span v-else>{{ timeToDate(data.end) }}</span>
                            </template>
                        </Column>
                        <Column :header="$t('rent.rent')">
                            <template #body="{ data }">
                                {{ numeralFormat(data.rent, '0.00') }}
                            </template>
                        </Column>
                        <template #empty>
                            {{ $t('rent.empty_reservation_list') }}
                        </template>
                    </DataTable>
                </template>
            </Card>
        </div>
    </div>
</template>