<script>
    import { getValueLabel, getResponseErrors, hasAccess, setMetaTitle, timeToDate } from '@/utils/helper'
    import { appStore } from '@/store.js'
    
    import TabMenu from '@/views/app/Items/_TabMenu.vue'
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
                getValueLabel,
                hasAccess,
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
                meta: {
                    rentals: {
                        currentPage: 1,
                        perPage: this.rowsPerPage,
                        totalRecords: null,
                        totalPages: null,
                        loading: true
                    },
                }
            }
        },
        beforeMount() {
            this.itemService.get(this.$route.params.itemId)
                .then(
                    (response) => {
                        this.item = response.data
                        this.loading = false
                        
                        this.getReservationList()
                    },
                    (errors) => {
                        this.$toast.add({ severity: 'error', summary: this.$t('app.error'), detail: errors.response.data.message, life: 3000 });
                    }
                );
        },
        methods: {
            getBreadcrumbs() {
                let items = [
                    {'label' : this.$t('menu.estates'), disabled : true },
                    {'label' : this.$t('menu.estate_list'), route : { name : 'items'} },
                ]
                
                if(this.item.name != undefined)
                {
                    items.push({'label' : this.item.name, route : { name : 'item_show'} })
                    items.push({'label' : this.$t('items.reservation'), disabled : true })
                }
                    
                return items
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
            
            rowRentalsClick(event) {
                this.$router.push({name: 'rental_show', params: { rentalId : event.data.id }})
            }
        },
    }
</script>

<template>
    <Breadcrumb :model="getBreadcrumbs()"/>
    
    <div class="grid mt-1" v-if="!loading">
        <div class="col col-12">
            <div class="card">
                <TabMenu active="TabMenu" :item="item" :showEditButton="false" activeIndex="rent:reservation" class="mb-5"/>
                
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
    </div>
</template>