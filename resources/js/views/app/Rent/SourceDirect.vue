<script>
    import { ref } from 'vue'
    import { getResponseErrors, getValueLabel, getValues, setMetaTitle, hasAccess } from '@/utils/helper'
    import ItemService from '@/service/ItemService'
    import RentalService from '@/service/RentalService'
    import TenantService from '@/service/TenantService'
    
    import Address from '@/views/app/_partials/Address.vue'
    import Header from '@/views/app/_partials/Header.vue'
    import TenantForm from './../Tenants/_Form.vue'
    import ItemForm from './../Items/_Form.vue'
    import RentForm from './_RentForm.vue'
    import Summary from './_Summary.vue'
    
    export default {
        components: { Address, Header, ItemForm, RentForm, Summary, TenantForm },
        setup() {
            setMetaTitle('meta.title.rent_item')
            
            const item = ref({
                ownership_type: 'property',
                type: 'apartment',
                country : 'PL',
            })
            
            const tenant = ref({
                type : 'person',
                contacts : {
                    email : [],
                    phone : []
                },
                country : 'PL'
            })
            
            const itemService = new ItemService()
            const tenantService = new TenantService();
            const rentalService = new RentalService();
            
            return {
                item,
                tenant,
                itemService,
                tenantService,
                rentalService,
                getValueLabel,
                hasAccess
            }
        },
        data() {
            return {
                activeStep: 0,
                errors: [],
                loading: false,
                rent: {},
                saving: false,
                selectItemModalVisible: false,
                selectTenantModalVisible: false,
                selectedItem: false,
                selectedTenant: false,
                stepsItems: [
                    { label : this.$t('rent.tenant_data'), icon : 'pi pi-user'},
                    { label : this.$t('rent.tenant_data'), icon : 'pi pi-building'},
                    { label : this.$t('rent.rent_details'), icon : 'pi pi-dollar'},
                    { label : this.$t('rent.summary'), icon : 'pi pi-check'},
                ],
                rented: [
                    {"id": 0, "name" : this.$t('items.free')},
                    {"id": 1, "name" : this.$t('items.rented')},
                ],
                item_types: getValues('item_types'),
                tenant_types: getValues('tenant_types'),
                items: [],
                tenants: [],
                meta: {
                    breadcrumbItems: [
                        {'label' : this.$t('menu.estates'), disabled : true },
                        {'label' : this.$t('rent.new_rent'), disabled : true },
                    ],
                    items: {
                        list: {
                            first: 0,
                            size: this.rowsPerPage,
                            sort: 'name',
                            order: 1,
                        },
                        totalRecords: null,
                        totalPages: null,
                        loading: false,
                        search: {
                            name : '',
                            type : '',
                            rented : '',
                            mode: 'normal'
                        }
                    },
                    tenants: {
                        list: {
                            first: 0,
                            size: this.rowsPerPage,
                            sort: 'name',
                            order: 1,
                        },
                        totalRecords: null,
                        totalPages: null,
                        loading: false,
                        search: {
                            name : '',
                            pesel_nip : '',
                            type : '',
                        }
                    }
                }
            }
        },
        methods: {
            changeStep(step) {
                this.activeStep = step
                scroll(0,0)
            },
            addNewItem() {
                if (this.selectedItem !== false) {
                    this.selectedItem = false
                    this.rent.item_id = null
                    this.item = {
                        ownership_type: 'property',
                        type: 'apartment',
                        country : 'PL',
                    }
                }
            },
            
            selectItem(event) {
                this.meta.items.loading = true
                this.itemService.get(event.data.id)
                    .then(
                        (response) => {
                            this.item = response.data
                            this.item._update = false
                            this.meta.items.loading = false
                            this.selectItemModalVisible = false
                            this.selectedItem = response.data.id
                            this.rent.item_id = response.data.id
                        },
                        (errors) => {
                            this.$toast.add({ severity: 'error', summary: this.$t('app.error'), detail: errors.response.data.message, life: 3000 });
                            this.meta.items.loading = false
                        }
                    )
            },
            
            selectItemModal() {
                this.selectItemModalVisible = true
                this.getItemsList()
            },
            
            changeItemsPage(event) {
                this.meta.items.list.first = event["first"];
                this.getItemsList()
            },
            
            sortItems(event) {
                this.meta.items.list.sort = event['sortField']
                this.meta.items.list.order = event['sortOrder']
                this.meta.items.list.first = 0
                this.getItemsList()
            },
            
            searchItems() {
                this.meta.items.list.first = 0
                this.getItemsList()
            },
            
            resetSearchItems() {
                this.meta.items.list.first = 0
                this.meta.items.search.name = '';
                this.meta.items.search.pesel_nip = '';
                this.meta.items.search.type = '';
                this.getItemsList()
            },
            
            getItemsList() {
                this.meta.items.loading = true
                this.itemService.list(this.meta.items.list, this.meta.items.search)
                    .then(
                        (response) => {
                            this.items = response.data.data
                            this.meta.items.totalRecords = response.data.total_rows
                            this.meta.items.totalPages = response.data.total_pages
                            this.meta.items.loading = false
                        },
                        (errors) => {
                            this.$toast.add({ severity: 'error', summary: this.$t('app.error'), detail: errors.response.data.message, life: 3000 });
                        }
                    )
            },
            
            setItemData() {
                this.saving = true
                this.errors = []
                
                this.itemService.validate(this.item)
                    .then(
                        (response) => {
                            this.saving = false
                            this.changeStep(2)
                        },
                        (errors) => {
                            this.saving = false
                            this.$toast.add({ severity: 'error', summary: this.$t('app.error'), detail: errors.response.data.message, life: 3000 });
                            this.errors = getResponseErrors(errors)
                        }
                    )
            },
            
            addNewTenant() {
                if (this.selectedTenant !== false) {
                    this.selectedTenant = false
                    this.tenant = {
                        type : 'person',
                        contacts : {
                            email : [],
                            phone : []
                        },
                        country : 'PL'
                    }
                }
            },
            
            selectTenant(event) {
                this.meta.tenants.loading = true
                this.tenantService.get(event.data.id)
                    .then(
                        (response) => {
                            this.tenant = response.data
                            this.tenant._update = false
                            this.meta.tenants.loading = false
                            this.selectTenantModalVisible = false
                            this.selectedTenant = response.data.id
                        },
                        (errors) => {
                            this.$toast.add({ severity: 'error', summary: this.$t('app.error'), detail: errors.response.data.message, life: 3000 });
                            this.meta.tenants.loading = false
                        }
                    )
            },
            
            selectTenantModal() {
                this.selectTenantModalVisible = true
                this.getTenantsList()
            },
            
            changeTenantsPage(event) {
                this.meta.tenants.list.first = event["first"];
                this.getTenantsList()
            },
            
            sortTenants(event) {
                this.meta.tenants.list.sort = event['sortField']
                this.meta.tenants.list.order = event['sortOrder']
                this.meta.tenants.list.first = 0
                this.getTenantsList()
            },
            
            searchTenants() {
                this.meta.tenants.list.first = 0
                this.getTenantsList()
            },
            
            resetSearchTenants() {
                this.meta.tenants.list.first = 0
                this.meta.tenants.search.name = '';
                this.meta.tenants.search.pesel_nip = '';
                this.meta.tenants.search.type = '';
                this.getTenantsList()
            },
            
            getTenantsList() {
                this.meta.tenants.loading = true
                this.tenantService.list(this.meta.tenants.list, this.meta.tenants.search)
                    .then(
                        (response) => {
                            this.tenants = response.data.data
                            this.meta.tenants.totalRecords = response.data.total_rows
                            this.meta.tenants.totalPages = response.data.total_pages
                            this.meta.tenants.loading = false
                        },
                        (errors) => {
                            this.$toast.add({ severity: 'error', summary: this.$t('app.error'), detail: errors.response.data.message, life: 3000 });
                        }
                    )
            },
            
            setTenantData() {
                this.saving = true
                this.errors = []
                
                this.tenantService.validate(this.tenant)
                    .then(
                        (response) => {
                            this.saving = false
                            this.changeStep(1)
                        },
                        (errors) => {
                            this.saving = false
                            this.$toast.add({ severity: 'error', summary: this.$t('app.error'), detail: errors.response.data.message, life: 3000 });
                            this.errors = getResponseErrors(errors)
                        }
                    )
            },
            
            setRentalData(rent) {
                this.rent = rent
                this.rentalService.validate(this.rent)
                    .then(
                        (response) => {
                            this.changeStep(3)
                        },
                        (errors) => {
                            this.$toast.add({ severity: 'error', summary: this.$t('app.error'), detail: errors.response.data.message, life: 3000 });
                        }
                    )
            },
            
            confirmRental() {
                this.rentalService.rent(this.tenant, this.item, this.rent)
                    .then(
                        (response) => {
                            this.$router.push({name: 'rent_success', params: { rentId : response.data }})
                        },
                        (errors) => {
                            this.$toast.add({ severity: 'error', summary: this.$t('app.error'), detail: errors.response.data.message, life: 3000 });
                            this.errors = getResponseErrors(errors)
                        },
                    )
            },
            
            back() {
                if (this.activeStep > 0) 
                    this.changeStep(this.activeStep - 1)
            }
        }
    }
</script>

<template>
    <Breadcrumb :model="meta.breadcrumbItems"/>
    <div class="card p-fluid mt-4">
        
        <div class="mb-5">
            <div class="grid align-items-center">
                <div class="col-12 md:col-5" v-if="activeStep > 0">
                    <Header :object="tenant" type="tenant"/>
                </div>
                
                <div class="col-12 md:col-2 text-center" v-if="activeStep > 1">
                    <i class="pi pi-angle-double-right text-5xl hidden md:block"></i>
                    <i class="pi pi-angle-double-down text-5xl md:hidden"></i>
                </div>
                
                <div class="col-12 md:col-5" v-if="activeStep > 1">
                    <Header :object="item" type="item"/>
                </div>
            </div>
        </div>
            
        <Steps :model="stepsItems" :activeStep="activeStep">
            <template #item="{ item, active }">
                <span :class="['inline-flex align-items-center justify-content-center align-items-center border-circle border-primary border-1 h-3rem w-3rem z-1 cursor-pointer', { 'bg-primary': active, 'surface-overlay text-primary': !active }]">
                    <i :class="[item.icon, 'text-xl']" />
                </span>
            </template>
        </Steps>
        <div class="mt-4">
            <div v-if="activeStep == 0">
                <div class="formgrid grid mb-5">
                    <div class="col-12 sm:col-6 mt-3">
                        <Button :label="$t('rent.add_new_tenant')" :severity="!selectedTenant ? 'info' : 'secondary'" :outlined="selectedTenant ? true : false" @click="addNewTenant"/>
                    </div>
                    <div class="col-12 sm:col-6 mt-3">
                        <Button :label="$t('rent.select_tenant')" :severity="selectedTenant ? 'info' : 'secondary'" :outlined="!selectedTenant ? true : false" @click="selectTenantModal"/>
                    </div>
                </div>
                
                <template v-if="selectedTenant && hasAccess('tenant:update')">
                    <div class="mt-3 mb-5 text-right">
                        <div class="flex align-items-center justify-content-end">
                            <span class="mr-2">
                                {{ $t('rent.update_tenant_data') }}
                            </span>
                            <InputSwitch v-model="tenant._update" :trueValue="1"/>
                        </div>
                    </div>
                </template>
                <TenantForm @submit-form="setTenantData" :tenant="tenant" source="rent:direct" :saving="saving" :loading="loading" :disabled="selectedTenant && !tenant._update" :errors="errors" />
            </div>
            <div v-else-if="activeStep == 1">
                <div class="formgrid grid mb-5">
                    <div class="col-12 sm:col-6 mt-3">
                        <Button :label="$t('rent.add_new_item')" :severity="!selectedItem ? 'info' : 'secondary'" :outlined="selectedItem ? true : false" @click="addNewItem"/>
                    </div>
                    <div class="col-12 sm:col-6 mt-3">
                        <Button :label="$t('rent.select_item')" :severity="selectedItem ? 'info' : 'secondary'" :outlined="!selectedItem ? true : false" @click="selectItemModal"/>
                    </div>
                </div>
                
                <template v-if="selectedItem && hasAccess('item:update')">
                    <div class="mt-3 mb-5 text-right">
                        <div class="flex align-items-center justify-content-end">
                            <span class="mr-2">
                                {{ $t('rent.update_item_data') }}
                            </span>
                            <InputSwitch v-model="item._update" :trueValue="1"/>
                        </div>
                    </div>
                </template>
                <ItemForm @submit-form="setItemData" :item="item" source="rent:direct" @back="back" :saving="saving" :loading="loading" :disabled="selectedItem && !item._update" :errors="errors" />
            </div>
            <div v-else-if="activeStep == 2">
                <RentForm @submit-form="setRentalData" :r="rent" :item="item" :errors="errors" @back="back"/>
            </div>
            <div v-else-if="activeStep == 3">
                <Summary @submit-form="confirmRental" :item="item" :tenant="tenant" :rent="rent" :errors="errors" @back="back"/>
            </div>
        </div>
    </div>
    
    <Dialog v-model:visible="selectTenantModalVisible" modal :header="$t('rent.select_tenant')" style="{ width: '50rem' }" :breakpoints="{ '1499px': '75vw', '999px': '95vw' }">
        <form v-on:submit.prevent="searchTenants">
            <div class="formgrid grid mb-1">
                <div class="col-12 md:col mb-3">
                    <InputText type="text" :placeholder="$t('tenants.name')" class="w-full" v-model="meta.tenants.search.name"/>
                </div>
                <div class="col-12 md:col mb-3">
                    <Dropdown v-model="meta.tenants.search.type" :showClear="this.meta.tenants.search.type ? true : false" :options="tenant_types" optionLabel="name" optionValue="id" :placeholder="$t('tenants.account_type')" class="w-full" />
                </div>
                <div class="col-12 md:col mb-3">
                    <InputText type="text" :placeholder="$t('tenants.nip_pesel')" class="w-full" v-model="meta.tenants.search.pesel_nip"/>
                </div>
                <div class="col-12  mb-3" style="width: 120px;">
                    <Button type="submit" iconPos="left" icon="pi pi-search" class="mr-2"/>
                    <Button severity="secondary" outlined iconPos="left" icon="pi pi-filter-slash" @click="resetSearchTenants"/>
                </div>
            </div>
        </form>
    
        <DataTable :value="tenants" stripedRows class="p-datatable-gridlines clickable" :totalRecords="meta.tenants.totalRecords" :rowHover="true" :lazy="true" :paginator="true" :pageCount="meta.tenants.totalPages" :rows="meta.tenants.list.size" :first="meta.tenants.list.first" @sort="sortTenants($event)" @page="changeTenantsPage" :loading="meta.tenants.loading" @row-click="selectTenant($event)" :sortField="meta.tenants.list.sort" :sortOrder="meta.tenants.list.order">
            <Column field="name" sortable :header="$t('tenants.name')" style="min-width: 300px;">
                <template #body="{ data }">
                    <Badge :value="getValueLabel('tenant_types', data.type)" class="font-normal" severity="info"></Badge>
                    <div class="mt-1">
                        {{ data.name }}
                        <div>
                            <small>
                                <Address :object="data" emptyChar="-"/>
                            </small>
                        </div>
                    </div>
                    <div class="mt-2" v-if="data.type == 'person' && data.pesel">
                        {{ $t('tenants.pesel') }}: {{ data.pesel }}
                    </div>
                    <div class="mt-2" v-if="data.type == 'firm' && data.nip">
                        {{ $t('tenants.nip') }}: {{ data.nip }}
                    </div>
                </template>
            </Column>
            <template #empty>
                {{ $t('tenants.empty_list') }}
            </template>
        </DataTable>
    </Dialog>
    
    <Dialog v-model:visible="selectItemModalVisible" modal :header="$t('rent.select_item')" style="{ width: '50rem' }" :breakpoints="{ '1499px': '75vw', '999px': '95vw' }">
        <form v-on:submit.prevent="searchItems">
            <div class="formgrid grid mb-1">
                <div class="col-12 md:col mb-3">
                    <InputText type="text" :placeholder="$t('items.name')" class="w-full" v-model="meta.items.search.name"/>
                </div>
                <div class="col-12 md:col mb-3">
                    <Dropdown v-model="meta.items.search.type" :showClear="this.meta.items.search.type ? true : false" :options="item_types" optionLabel="name" optionValue="id" :placeholder="$t('items.estate_type')" class="w-full" />
                </div>
                <div class="col-12 md:col mb-3">
                    <Dropdown v-model="meta.items.search.rented" :showClear="this.meta.items.search.rented != undefined ? true : false" :options="rented" optionLabel="name" optionValue="id" :placeholder="$t('items.status')" class="w-full" />
                </div>
                <div class="col-12  mb-3" style="width: 120px;">
                    <Button type="submit" iconPos="left" icon="pi pi-search" class="mr-2"/>
                    <Button severity="secondary" outlined iconPos="left" icon="pi pi-filter-slash" @click="resetSearchItems"/>
                </div>
            </div>
        </form>
    
        <DataTable :value="items" stripedRows class="p-datatable-gridlines clickable" :totalRecords="meta.items.totalRecords" :rowHover="true" :lazy="true" :paginator="true" :pageCount="meta.items.totalPages" :rows="meta.items.list.size" :first="meta.items.list.first" @sort="sortItems($event)" @page="changeItemsPage" :loading="meta.items.loading" @row-click="selectItem($event)" :sortField="meta.items.list.sort" :sortOrder="meta.items.list.order">
            <Column field="name" sortable :header="$t('items.name')" style="min-width: 300px;">
                <template #body="{ data }">
                    <Badge :value="getValueLabel('item_types', data.type)" class="font-normal" severity="info"></Badge>
                    <div class="mt-1">
                        {{ data.name }}
                        <div>
                            <small>
                                <Address :object="data" emptyChar="-"/>
                            </small>
                        </div>
                    </div>
                </template>
            </Column>
            <Column :header="$t('items.rented')" class="text-center" style="width: 120px;">
                <template #body="{ data }">
                    <Badge v-if="data.rented" severity="success" :value="$t('app.yes')"></Badge>
                    <Badge v-else severity="secondary" :value="$t('app.no')"></Badge>
                    
                    <template v-if="data.rentals">
                        <div class="text-sm text-gray-600 mt-2">
                            <div v-if="data.rentals.current">
                                {{ $t('items.end') }}: {{ data.rentals.current.end }}
                            </div>
                            <div v-if="data.rentals.next">
                                {{ $t('items.reservation_single') }}: {{ data.rentals.next.start }}-{{ data.rentals.next.end }}
                            </div>
                        </div>
                    </template>
                </template>
            </Column>
            <template #empty>
                {{ $t('items.empty_list') }}
            </template>
        </DataTable>
    </Dialog>
</template>