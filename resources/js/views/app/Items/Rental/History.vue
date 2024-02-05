<script>
    import { getValueLabel, getResponseErrors, setMetaTitle } from '@/utils/helper'
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
            }
        },
        data() {
            return {
                rentals: [],
                archive_rentals: [],
                meta: {
                    archive_rentals: {
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
            this.getArchiveList()
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
                    items.push({'label' : this.$t('items.history'), disabled : true })
                }
                    
                return items
            },
            
            getArchiveList() {
                const search = {
                    item_id : this.$route.params.itemId,
                    status_arr : ['archive', 'termination', 'canceled'],
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
        },
    }
</script>

<template>
    <Breadcrumb :model="getBreadcrumbs()"/>
    
    <div class="grid mt-1">
        <div class="col col-12">
            <div class="card">
                <TabMenu active="TabMenu" :item="item" :showEditButton="false" activeIndex="rent:history" class="mb-5"/>
                
                <DataTable :value="archive_rentals" stripedRows class="p-datatable-gridlines clickable" :totalRecords="meta.archive_rentals.totalRecords" :rowHover="true" :lazy="true" :paginator="true" :pageCount="meta.archive_rentals.totalPages" :rows="meta.archive_rentals.perPage" @page="changeArchivePage" :loading="meta.archive_rentals.loading" @row-click="rowRentalsClick($event)">
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
                    <Column :header="$t('rent.rent')">
                        <template #body="{ data }">
                            {{ numeralFormat(data.rent, '0.00') }}
                            {{ data.currency }}
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