<script>
    import { hasAccess, setMetaTitle, getValueLabel, getValues, getRentalRowColor } from '@/utils/helper'
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
                getValueLabel,
                getRentalRowColor
            }
        },
        data() {
            return {
                loading: false,
                errors: [],
                rentals: [],
                displayConfirmation: false,
                deleteRentalId: null,
                item_types: getValues('item_types'),
                tenant_types: getValues('tenant_types'),
                statuses: getValues('rental.statuses'),
                meta: {
                    list: {
                        first: appStore().getDTSessionStateFirst("dt-state-rentals-table"),
                        size: this.rowsPerPage,
                        sort: 'full_number',
                        order: -1,
                    },
                    search: {},
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
            let order = appStore().getTableOrder('rentals');
            if (order != undefined) {
                this.meta.list.sort = order.col;
                this.meta.list.order = order.dir;
            }
            
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
                
                this.rentalService.list(this.meta.list, search)
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
                this.meta.list.first = event["first"];
                this.getList()
            },
            
            sort(event) {
                this.meta.list.sort = event['sortField']
                this.meta.list.order = event['sortOrder']
                this.meta.list.first = 0
                
                appStore().setTableOrder('rentals', this.meta.list.sort, this.meta.list.order);
                
                this.getList()
            },
            
            rowClick(event) {
                this.showRental(event.data.id)
            },
            
            search() {
                this.meta.list.first = 0
                appStore().setTableFilter('rentals', this.meta.search)
                this.getList()
            },
            
            resetSearch() {
                this.meta.list.first = 0
                this.meta.search = {}
                appStore().setTableFilter('rentals', this.meta.search)
                this.getList()
            },
            
            openConfirmation(id) {
                this.displayConfirmation = true
                this.deleteRentalId = id
            },
            
            confirmDeleteRental() {
                this.rentalService.remove(this.deleteRentalId)
                    .then(
                        (response) => {
                            this.getList()
                            this.$toast.add({ severity: 'success', summary: this.$t('app.success'), detail: this.$t('items.deleted'), life: 3000 });
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
                            <InputText type="text" :placeholder="$t('rent.number')" class="w-full" v-model="meta.search.number"/>
                        </div>
                        
                        <div class="col-12 md:col-4 mb-3">
                            <InputText type="text" :placeholder="$t('rent.item_name')" class="w-full" v-model="meta.search.item_name"/>
                        </div>
                        <div class="col-12 md:col-4 mb-3">
                            <Dropdown v-model="meta.search.item_type" :showClear="this.meta.search.item_type ? true : false" :options="item_types" optionLabel="name" optionValue="id" :placeholder="$t('rent.item_type')" class="w-full" />
                        </div>
                        <div class="col-12 md:col-3 mb-3">
                            <InputText type="text" :placeholder="$t('rent.item_address')" class="w-full" v-model="meta.search.item_address"/>
                        </div>
                        
                        <div class="col-12 md:col-3 mb-3">
                            <InputText type="text" :placeholder="$t('rent.tenant_name')" class="w-full" v-model="meta.search.tenant_name"/>
                        </div>
                        <div class="col-12 md:col-3 mb-3">
                            <Dropdown v-model="meta.search.tenant_type" :showClear="this.meta.search.tenant_type ? true : false" :options="tenant_types" optionLabel="name" optionValue="id" :placeholder="$t('rent.tenant_type')" class="w-full" />
                        </div>
                        <div class="col-12 md:col-3 mb-3">
                            <InputText type="text" :placeholder="$t('rent.tenant_address')" class="w-full" v-model="meta.search.tenant_address"/>
                        </div>
                        
                        <div class="col-12 md:col-3 mb-3">
                            <Dropdown v-model="meta.search.status" :showClear="this.meta.search.status ? true : false" :options="statuses" optionLabel="name" optionValue="id" :placeholder="$t('rent.status')" class="w-full" />
                        </div>
                        
                        <div class="col-12 md:col-7 sm:col-9 mb-3">
                            <div class="flex">
                                <div class="col-6 p-0 pr-1">
                                    <Calendar id="start" v-model="meta.search.start" :placeholder="$t('rent.start_date_list')" class="w-auto" showIcon/>
                                </div>
                                <div class="col-6 p-0 pl-1">
                                    <Calendar id="end" v-model="meta.search.end" :placeholder="$t('rent.end_date_list')" showIcon/>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-12 mb-3" style="width: 120px;">
                            <Button type="submit" iconPos="left" icon="pi pi-search" class="mr-2"/>
                            <Button severity="secondary" outlined iconPos="left" icon="pi pi-filter-slash" @click="resetSearch"/>
                        </div>
                    </div>
                </form>
                <DataTable :rowClass="({ status }) => getRentalRowColor(status)" :value="rentals" stripedRows class="p-datatable-gridlines clickable" :totalRecords="meta.totalRecords" :rowHover="true" :lazy="true" :paginator="true" :pageCount="meta.totalPages" :rows="meta.list.size" :first="meta.list.first" @sort="sort($event)" @page="changePage" :loading="loading" @row-click="rowClick($event)" :sortField="this.meta.list.sort" :sortOrder="this.meta.list.order" stateStorage="session" stateKey="dt-state-rentals-table">
                    <Column :header="$t('rent.number')" field="full_number" sortable>
                        <template #body="{ data }">
                            {{ data.full_number }}
                            <div>
                                <small>{{ data.document_date }}</small>
                            </div>
                        </template>
                    </Column>
                    <Column :header="$t('rent.period_short')">
                        <template #body="{ data }">
                            {{ data.start }} -
                            <template v-if="data.termination">
                                <span>{{ data.termination_time }}</span>
                            </template>
                            <template v-else>
                                <span v-if="data.period == 'indeterminate'">{{ $t("rent.indeterminate") }}</span>
                                <span v-else>{{ data.end }}</span>
                            </template>
                        </template>
                    </Column>
                    <Column :header="$t('rent.status')">
                        <template #body="{ data }">
                            {{ getValueLabel('rental.statuses', data.status) }}
                            <span v-if="data.termination && data.status == 'current'" class="mr-1" v-tooltip.top="$t('rent.rental_is_being_terminated')">
                                <i class="pi pi-delete-left" style="font-size: 1.2rem; color: var(--red-600)"></i>
                            </span>
                        </template>
                    </Column>
                    <Column :header="$t('rent.balance')" field="balance" sortable>
                        <template #body="{ data }">
                            {{ numeralFormat(data.balance, '0.00') }}
                            {{ data.currency }}
                        </template>
                    </Column>
                    <Column :header="$t('rent.estate')" style="min-width: 300px;">
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
                    <Column :header="$t('rent.tenant')" style="min-width: 300px;">
                        <template #body="{ data }">
                            <Badge :value="getValueLabel('tenant_types', data.tenant.type)" class="font-normal" severity="info"></Badge>
                            <div class="mt-1">
                                {{ data.tenant.name }}
                                
                                <div>
                                    <small>
                                        <Address :object="data.tenant" :newline="true" emptyChar=""/>
                                    </small>
                                </div>
                            </div>
                        </template>
                    </Column>
                    <Column :header="$t('rent.rent')" field="rent" sortable>
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
                        {{ $t('rent.empty_list') }}
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