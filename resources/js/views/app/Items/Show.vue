<script>
    import { getValueLabel, getResponseErrors, hasAccess, setMetaTitle, timeToDate } from '@/utils/helper'
    import { appStore } from '@/store.js'
    
    import TabMenu from './_TabMenu.vue'
    import Address from '@/views/app/_partials/Address.vue'
    import Rental from '@/views/app/_partials/Rental.vue'
    import ItemService from '@/service/ItemService'
    import RentalService from '@/service/RentalService'
    
    export default {
        components: { Address, Rental, TabMenu },
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
                        {'label' : this.$t('items.details'), disabled : true },
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
                    (errors) => {
                        this.$toast.add({ severity: 'error', summary: this.$t('app.error'), detail: errors.response.data.message, life: 3000 });
                    }
                );
        },
        methods: {
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
                this.$router.push({name: 'rental_show', params: { rentalId : event.data.id }})
            },
            
            showRental() {
                
            }
        },
    }
</script>

<template>
    <Breadcrumb :model="meta.breadcrumbItems"/>
    
    <div class="grid mt-1" v-if="!loading">
        <div class="col col-12">
            <div class="card">
                <TabMenu active="TabMenu" :item="item" activeIndex="0" class="mb-5"/>
                
                <div class="grid mt-4">
                    <div class="col text-center" v-if="item.area">
                        <span class="font-medium">{{ $t('items.area') }}: </span>
                        <br/>
                        <i>{{ numeralFormat(item.area, '0.00') }} (m2)</i>
                    </div>
                    <div class="col text-center" v-if="item.num_rooms">
                        <span class="font-medium">{{ $t('items.number_of_rooms') }}: </span>
                        <br/>
                        <i>{{ item.num_rooms }}</i>
                    </div>
                    <div class="col text-center" v-if="item.default_rent">
                        <span class="font-medium">{{ $t('items.default_rent_value') }}: </span>
                        <br/>
                        <i>{{ numeralFormat(item.default_rent, '0.00') }}</i>
                    </div>
                    <div class="col text-center" v-if="item.default_deposit">
                        <span class="font-medium">{{ $t('items.default_deposit_value') }}: </span>
                        <br/>
                        <i>{{ numeralFormat(item.default_deposit, '0.00') }}</i>
                    </div>
                    <div class="col text-center">
                        <span class="font-medium">{{ $t('items.ownership') }}: </span>
                        <br/>
                        <i>
                            <span v-if="item.ownership_type == 'manage'">
                                <router-link v-if="item.customer.id" :to="{name: 'customer_show', params: { customerId : item.customer.id }}">
                                    {{ item.customer.name }}
                                </router-link>
                            </span>
                        </i>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="flex justify-content-between align-items-center mb-3 text-color font-medium">
                     <h4 class="inline-flex mb-0 mt-2 text-color font-medium">
                         {{ $t("items.currently_tenant") }}
                     </h4>
                </div>
                
                <div v-if="item.current_rental">
                    <div class="grid align-items-center">
                        <div class="col-12 lg:col-6">
                            <Rental :object="item.current_rental" />
                        </div>
                        <div class="col-12 lg:col-6 text-center">
                            <Button severity="success" :label="$t('rent.go_to_details')" @click="showRental" class="align-center mt-5" iconPos="right" icon="pi pi-external-link"></Button>
                        </div>
                    </div>
                </div>
                <div v-else>
                    <div class="grid align-items-center">
                        <div class="col-12 lg:col-6 text-lg font-medium text-red-500">
                            {{ $t('items.currently_no_tenant') }}
                        </div>
                        <div class="col-12 lg:col-6 text-center">
                            <Button :label="$t('items.start_rental')" @click="rentItem" type="button" severity="danger" iconPos="right" icon="pi pi-briefcase" class="w-auto text-center" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col col-12">
            <div class="card">
                <div class="flex justify-content-between align-items-center mb-3 text-color font-medium">
                    <h5 class="inline-flex mb-0 text-color font-medium">{{ $t("items.reservations") }}</h5>
                    <div v-if="hasAccess('rent:create')">
                        <Button icon="pi pi-plus" @click="rentItem" v-tooltip.left="$t('items.add_new_tenant')"></Button>
                    </div>
                </div>
                <DataTable :value="rentals" stripedRows class="p-datatable-gridlines clickable" :totalRecords="meta.rentals.totalRecords" :rowHover="true" :lazy="true" :paginator="true" :pageCount="meta.rentals.totalPages" :rows="meta.rentals.perPage" @page="changeRentalsPage" :loading="meta.rentals.loading" @row-click="rowRentalsClick($event)">
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
            </div>
        </div>
        
        <div class="col col-12">
            <div class="card">
                <div class="flex justify-content-between align-items-center mb-3 text-color font-medium">
                    <h5 class="inline-flex mb-0 text-color font-medium">{{ $t("items.history_rentals") }}</h5>
                </div>
                <DataTable :value="archive_rentals" stripedRows class="p-datatable-gridlines clickable" :totalRecords="meta.archive_rentals.totalRecords" :rowHover="true" :lazy="true" :paginator="true" :pageCount="meta.archive_rentals.totalPages" :rows="meta.archive_rentals.perPage" @page="changeArchivePage" :loading="meta.archive_rentals.loading" @row-click="rowRentalsClick($event)">
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
            </div>
        </div>
    </div>
</template>