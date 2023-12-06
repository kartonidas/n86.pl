<script>
    import { ref } from 'vue'
    import { getValueLabel, getValues, setMetaTitle } from '@/utils/helper'
    import ItemService from '@/service/ItemService'
    import RentalService from '@/service/RentalService'
    import TenantService from '@/service/TenantService'
    
    import Address from '@/views/app/_partials/Address.vue'
    import ItemForm from './../Items/_Form.vue'
    import RentForm from './_RentForm.vue'
    import Summary from './_Summary.vue'
    
    export default {
        components: { Address, ItemForm, RentForm, Summary },
        setup() {
            setMetaTitle('meta.title.rent_item')
            
            const item = ref({
                ownership_type: 'property',
                type: 'apartment',
                country : 'PL',
            })
            
            const itemService = new ItemService()
            const tenantService = new TenantService();
            const rentalService = new RentalService();
            
            return {
                itemService,
                tenantService,
                rentalService,
                item,
                getValueLabel
            }
        },
        data() {
            return {
                activeStep: 0,
                errors: [],
                loading: true,
                rent: {},
                tenant: {},
                saving: false,
                selectItemModalVisible: false,
                selectedItem: false,
                stepsItems: [
                    { label : this.$t('rent.tenant_data'), icon : 'pi pi-building'},
                    { label : this.$t('rent.rent_details'), icon : 'pi pi-dollar'},
                    { label : this.$t('rent.summary'), icon : 'pi pi-check'},
                ],
                item_types: getValues('item_types'),
                items: [],
                meta: {
                    breadcrumbItems: [
                        {'label' : this.$t('menu.estates'), disabled : true },
                        {'label' : this.$t('rent.new_rent'), disabled : true },
                    ],
                    items: {
                        currentPage: 1,
                        perPage: this.rowsPerPage,
                        totalRecords: null,
                        totalPages: null,
                        loading: false,
                        sortField: 'name',
                        sortOrder: 1,
                        search: {
                            name : '',
                            type : '',
                        }
                    }
                }
            }
        },
        beforeMount() {
            this.tenantService.get(this.$route.params.tenantId)
                .then(
                    (response) => {
                        this.tenant = response.data
                        this.loading = false
                    },
                    (response) => {
                        this.$toast.add({ severity: 'error', summary: this.$t('app.error'), detail: response.response.data.message, life: 3000 });
                    }
                );
        },
        methods: {
            changeStep(step) {
                this.activeStep = step
                scroll(0,0)
            },
            
            addNewItem() {
                if (this.selectedItem !== false) {
                    this.selectedItem = false
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
                            this.meta.items.loading = false
                            this.selectItemModalVisible = false
                            this.selectedItem = response.data.id
                        },
                        (response) => {
                            this.$toast.add({ severity: 'error', summary: this.$t('app.error'), detail: response.response.data.message, life: 3000 });
                            this.meta.items.loading = false
                        }
                    )
            },
            
            selectItemModal() {
                this.selectItemModalVisible = true
                this.getItemsList()
            },
            
            changeItemsPage(event) {
                this.meta.items.currentPage = event["page"] + 1;
                this.getItemsList()
            },
            
            sortItems(event) {
                this.meta.items.sortField = event['sortField']
                this.meta.items.sortOrder = event['sortOrder']
                this.meta.items.currentPage = 1
                this.getItemsList()
            },
            
            searchItems() {
                this.getItemsList()
            },
            
            resetSearchItems() {
                this.meta.items.search.name = '';
                this.meta.items.search.pesel_nip = '';
                this.meta.items.search.type = '';
                this.getItemsList()
            },
            
            getItemsList() {
                this.meta.items.loading = true
                this.itemService.list(this.meta.items.perPage, this.meta.items.currentPage, this.meta.items.sortField, this.meta.items.sortOrder, this.meta.items.search)
                    .then(
                        (response) => {
                            this.items = response.data.data
                            this.meta.totalRecords = response.data.total_rows
                            this.meta.totalPages = response.data.total_pages
                            this.meta.items.loading = false
                        },
                        (errors) => {
                            this.$toast.add({ severity: 'error', summary: this.$t('app.error'), detail: errors.response.data.message, life: 3000 });
                        }
                    )
            },
            
            setItemData() {
                this.itemService.validate(this.item)
                    .then(
                        (response) => {
                            this.changeStep(1)
                        },
                        (response) => {
                            this.$toast.add({ severity: 'error', summary: this.$t('app.error'), detail: response.response.data.message, life: 3000 });
                        }
                    )
            },
            
            setRentalData(rent) {
                this.rent = rent
                this.rentalService.validate(this.rent)
                    .then(
                        (response) => {
                            this.changeStep(2)
                        },
                        (response) => {
                            this.$toast.add({ severity: 'error', summary: this.$t('app.error'), detail: response.response.data.message, life: 3000 });
                        }
                    )
            },
            
            confirmRental() {
                console.log("confirmRental")
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
                        <Button :label="$t('rent.add_new_item')" :severity="!selectedItem ? 'info' : 'secondary'" :outlined="selectedItem ? true : false" @click="addNewItem"/>
                    </div>
                    <div class="col-12 sm:col-6 mt-3">
                        <Button :label="$t('rent.select_item')" :severity="selectedItem ? 'info' : 'secondary'" :outlined="!selectedItem ? true : false" @click="selectItemModal"/>
                    </div>
                </div>
                <ItemForm @submit-form="setItemData" :item="item" source="rent" :saving="saving" :loading="loading" :errors="errors" />
            </div>
            <div v-else-if="activeStep == 1">
                <RentForm @submit-form="setRentalData" :item="item" @back="back"/>
            </div>
            <div v-else-if="activeStep == 2">
                <Summary @submit-form="confirmRental" :item="item" :tenant="tenant" :rent="rent" @back="back"/>
            </div>
        </div>
    </div>
    
    <Dialog v-model:visible="selectItemModalVisible" modal :header="$t('rent.select_item')" style="{ width: '50rem' }" :breakpoints="{ '1499px': '75vw' }">
        <form v-on:submit.prevent="searchItems">
            <div class="formgrid grid mb-1">
                <div class="col-12 md:col mb-3">
                    <InputText type="text" :placeholder="$t('items.name')" class="w-full" v-model="meta.items.search.name"/>
                </div>
                <div class="col-12 md:col mb-3">
                    <Dropdown v-model="meta.items.search.type" :showClear="this.meta.items.search.type ? true : false" :options="item_types" optionLabel="name" optionValue="id" :placeholder="$t('items.estate_type')" class="w-full" />
                </div>
                <div class="col-12  mb-3" style="width: 120px;">
                    <Button type="submit" iconPos="left" icon="pi pi-search" class="mr-2"/>
                    <Button severity="secondary" outlined iconPos="left" icon="pi pi-filter-slash" @click="resetSearchItems"/>
                </div>
            </div>
        </form>
    
        <DataTable :value="items" class="p-datatable-gridlines" :totalRecords="meta.items.totalRecords" :rowHover="true" :lazy="true" :paginator="true" :pageCount="meta.items.totalPages" :rows="meta.items.perPage" @sort="sortItems($event)" @page="changeItemsPage" :loading="meta.items.loading" @row-click="selectItem($event)" :sortField="this.meta.items.sortField" :sortOrder="this.meta.items.sortOrder">
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
            <template #empty>
                {{ $t('items.empty_list') }}
            </template>
        </DataTable>
    </Dialog>
</template>