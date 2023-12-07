<script>
    import { ref } from 'vue'
    import { getResponseErrors, getValueLabel, getValues, setMetaTitle } from '@/utils/helper'
    import ItemService from '@/service/ItemService'
    import RentalService from '@/service/RentalService'
    import TenantService from '@/service/TenantService'
    
    import Address from '@/views/app/_partials/Address.vue'
    import TenantForm from './../Tenants/_Form.vue'
    import RentForm from './_RentForm.vue'
    import Summary from './_Summary.vue'
    
    export default {
        components: { Address, RentForm, Summary, TenantForm },
        setup() {
            setMetaTitle('meta.title.rent_item')
            
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
                itemService,
                tenantService,
                rentalService,
                tenant,
                getValueLabel
            }
        },
        data() {
            return {
                activeStep: 0,
                errors: [],
                item: {},
                itemExists: true,
                loading: true,
                rent: {},
                saving: false,
                selectTenantModalVisible: false,
                selectedTenant: false,
                stepsItems: [
                    { label : this.$t('rent.tenant_data'), icon : 'pi pi-user'},
                    { label : this.$t('rent.rent_details'), icon : 'pi pi-dollar'},
                    { label : this.$t('rent.summary'), icon : 'pi pi-check'},
                ],
                tenant_types: getValues('tenant_types'),
                tenants: [],
                meta: {
                    breadcrumbItems: [
                        {'label' : this.$t('menu.estates'), disabled : true },
                        {'label' : this.$t('rent.new_rent'), disabled : true },
                    ],
                    tenants: {
                        currentPage: 1,
                        perPage: this.rowsPerPage,
                        totalRecords: null,
                        totalPages: null,
                        loading: false,
                        sortField: 'name',
                        sortOrder: 1,
                        search: {
                            name : '',
                            pesel_nip : '',
                            type : '',
                        }
                    }
                }
            }
        },
        beforeMount() {
            this.itemService.get(this.$route.params.itemId)
                .then(
                    (response) => {
                        this.item = response.data
                        this.loading = false
                    },
                    (response) => {
                        this.$toast.add({ severity: 'error', summary: this.$t('app.error'), detail: response.response.data.message, life: 3000 });
                        this.itemExists = false
                    }
                );
        },
        methods: {
            changeStep(step) {
                this.activeStep = step
                scroll(0,0)
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
                            this.meta.tenants.loading = false
                            this.selectTenantModalVisible = false
                            this.selectedTenant = response.data.id
                        },
                        (response) => {
                            this.$toast.add({ severity: 'error', summary: this.$t('app.error'), detail: response.response.data.message, life: 3000 });
                            this.meta.tenants.loading = false
                        }
                    )
            },
            
            selectTenantModal() {
                this.selectTenantModalVisible = true
                this.getTenantsList()
            },
            
            changeTenantsPage(event) {
                this.meta.tenants.currentPage = event["page"] + 1;
                this.getTenantsList()
            },
            
            sortTenants(event) {
                this.meta.tenants.sortField = event['sortField']
                this.meta.tenants.sortOrder = event['sortOrder']
                this.meta.tenants.currentPage = 1
                this.getTenantsList()
            },
            
            searchTenants() {
                this.getTenantsList()
            },
            
            resetSearchTenants() {
                this.meta.tenants.search.name = '';
                this.meta.tenants.search.pesel_nip = '';
                this.meta.tenants.search.type = '';
                this.getTenantsList()
            },
            
            getTenantsList() {
                this.meta.tenants.loading = true
                this.tenantService.list(this.meta.tenants.perPage, this.meta.tenants.currentPage, this.meta.tenants.sortField, this.meta.tenants.sortOrder, this.meta.tenants.search)
                    .then(
                        (response) => {
                            this.tenants = response.data.data
                            this.meta.totalRecords = response.data.total_rows
                            this.meta.totalPages = response.data.total_pages
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
                        (response) => {
                            this.saving = false
                            this.$toast.add({ severity: 'error', summary: this.$t('app.error'), detail: response.response.data.message, life: 3000 });
                            this.errors = getResponseErrors(response)
                        }
                    )
            },
            
            setRentalData(rent) {
                this.saving = true
                this.errors = []
                
                this.rentalService.validate(rent)
                    .then(
                        (response) => {
                            this.saving = false
                            this.rent = rent
                            this.changeStep(2)
                        },
                        (response) => {
                            this.saving = false
                            this.$toast.add({ severity: 'error', summary: this.$t('app.error'), detail: response.response.data.message, life: 3000 });
                            this.errors = getResponseErrors(response)
                        }
                    )
            },
            
            confirmRental() {
                this.rentalService.rent(this.tenant, this.item, this.rent)
                    .then(
                        (response) => {
                            console.log(response);
                        },
                        (response) => {
                            this.$toast.add({ severity: 'error', summary: this.$t('app.error'), detail: response.response.data.message, life: 3000 });
                            this.errors = getResponseErrors(response)
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
    
    <div v-if="itemExists">
        <div class="card p-fluid mt-4">
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
                    <TenantForm @submit-form="setTenantData" :tenant="tenant" source="rent" :saving="saving" :loading="loading" :errors="errors" />
                </div>
                <div v-else-if="activeStep == 1">
                    <RentForm @submit-form="setRentalData" :r="rent" :item="item" :errors="errors" @back="back"/>
                </div>
                <div v-else-if="activeStep == 2">
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
        
            <DataTable :value="tenants" class="p-datatable-gridlines" :totalRecords="meta.tenants.totalRecords" :rowHover="true" :lazy="true" :paginator="true" :pageCount="meta.tenants.totalPages" :rows="meta.tenants.perPage" @sort="sortTenants($event)" @page="changeTenantsPage" :loading="meta.tenants.loading" @row-click="selectTenant($event)" :sortField="this.meta.tenants.sortField" :sortOrder="this.meta.tenants.sortOrder">
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
    </div>
    <div v-else>
        <Message severity="error">{{ $t("items.no_exists") }}</Message>
    </div>
</template>