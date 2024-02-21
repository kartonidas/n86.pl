<script>
    import { getValueLabel, hasAccess, setMetaTitle } from '@/utils/helper'
    import { appStore } from '@/store.js'
    
    import TabMenu from '@/views/app/Items/_TabMenu.vue'
    import Address from '@/views/app/_partials/Address.vue'
    import RentalService from '@/service/RentalService'
    
    export default {
        components: { Address, TabMenu },
        props: {
            item: { type: Object },
        },
        setup() {
            setMetaTitle('meta.title.items_show')
            
            const rentalService = new RentalService()
            
            return {
                rentalService,
                getValueLabel,
                hasAccess,
            }
        },
        data() {
            return {
                rentals: [],
                displayConfirmation: false,
                deleteRentalId: null,
                meta: {
                    rentals: {
                        list: {
                            first: 0,
                            size: this.rowsPerPage,
                            sort: 'start',
                            order: 1,
                        },
                        totalRecords: null,
                        totalPages: null,
                        loading: true
                    },
                }
            }
        },
        beforeMount() {
            this.getReservationList()
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
                this.rentalService.list(this.meta.rentals.list, search)
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
                this.meta.rentals.list.first = event["first"];
                this.getReservationList()
            },
            
            rowRentalsClick(event) {
                this.$router.push({name: 'rental_show', params: { rentalId : event.data.id }})
            },
            
            openConfirmation(id) {
                this.displayConfirmation = true
                this.deleteRentalId = id
            },
            
            confirmDeleteRental() {
                this.rentalService.remove(this.deleteRentalId)
                    .then(
                        (response) => {
                            this.getReservationList()
                            this.$toast.add({ severity: 'success', summary: this.$t('app.success'), detail: this.$t('rent.deleted'), life: 3000 });
                        },
                        (errors) => {
                            this.$toast.add({ severity: 'error', summary: this.$t('app.error'), detail: errors.response.data.message, life: 3000 });
                        }
                    )
                
                this.displayConfirmation = false
                this.deleteRentalId = null
            },
            
            closeConfirmation() {
                this.displayConfirmation = false
            },
        },
    }
</script>

<template>
    <Breadcrumb :model="getBreadcrumbs()"/>
    
    <div class="grid mt-1">
        <div class="col col-12">
            <div class="card pt-4">
                <Help show="rental:history,status|item" mark="rental:history" class="text-right mb-3"/>
                <TabMenu active="TabMenu" :item="item" :showEditButton="false" activeIndex="rent:reservation" class="mb-5"/>
                
                <div class="flex justify-content-between align-items-center mb-3 text-color font-medium">
                    <h5 class="inline-flex mb-0 text-color font-medium">{{ $t("items.reservations") }}</h5>
                    <div v-if="hasAccess('rent:create') && item.can_add_rental">
                        <Button icon="pi pi-plus" @click="rentItem" v-tooltip.left="$t('items.add_new_tenant')"></Button>
                    </div>
                </div>
                <DataTable :value="rentals" stripedRows class="p-datatable-gridlines clickable" :totalRecords="meta.rentals.totalRecords" :rowHover="true" :lazy="true" :paginator="true" :pageCount="meta.rentals.totalPages" :rows="meta.rentals.list.size" :first="meta.rentals.list.first" @page="changeRentalsPage" :loading="meta.rentals.loading" @row-click="rowRentalsClick($event)">
                    <Column :header="$t('rent.number')" field="full_number">
                        <template #body="{ data }">
                            {{ data.full_number }}
                            <div>
                                <small>{{ data.document_date }}</small>
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
                    <Column :header="$t('rent.period_short')">
                        <template #body="{ data }">
                            {{ data.start }} - 
                            <span v-if="data.period == 'indeterminate'">{{ $t("rent.indeterminate") }}</span>
                            <span v-else>{{ data.end }}</span>
                        </template>
                    </Column>
                    <Column :header="$t('rent.rent')">
                        <template #body="{ data }">
                            {{ numeralFormat(data.rent, '0.00') }}
                            {{ data.currency }}
                        </template>
                    </Column>
                    <Column field="delete" v-if="hasAccess('rent:delete')" style="min-width: 60px; width: 60px" class="text-center">
                        <template #body="{ data }">
                            <Button :disabled="!data.can_delete" icon="pi pi-trash" v-tooltip.bottom="$t('app.remove')" class="p-button-danger p-2" style="width: auto" @click="openConfirmation(data.id)"/>
                        </template>
                    </Column>
                    <template #empty>
                        {{ $t('rent.empty_reservation_list') }}
                    </template>
                </DataTable>
                <Dialog :header="$t('app.confirmation')" v-model:visible="displayConfirmation" :style="{ width: '450px' }" :modal="true">
                    <div class="flex align-items-center justify-content-center">
                        <i class="pi pi-exclamation-triangle mr-3" style="font-size: 2rem" />
                        <span>{{ $t('app.remove_object_confirmation') }}</span>
                    </div>
                    <template #footer>
                        <Button :label="$t('app.no')" icon="pi pi-times" @click="closeConfirmation" class="p-button-text" />
                        <Button :label="$t('app.yes')" icon="pi pi-check" @click="confirmDeleteRental" class="p-button-danger" autofocus />
                    </template>
                </Dialog>
            </div>
        </div>
    </div>
</template>