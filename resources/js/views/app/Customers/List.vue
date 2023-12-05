<script>
    import { useRoute } from 'vue-router'
    import { hasAccess, setMetaTitle } from '@/utils/helper'
    import { appStore } from '@/store.js'
    
    import Address from '@/views/app/_partials/Address.vue'
    import CustomerService from '@/service/CustomerService'
    
    export default {
        components: { Address },
        setup() {
            setMetaTitle('meta.title.customers_list')
            
            const route = useRoute()
            const customerService = new CustomerService()
            
            return {
                customerService,
                hasAccess,
                route,
            }
        },
        data() {
            return {
                loading: true,
                customers: [],
                displayConfirmation: false,
                deleteCustomerId: null,
                meta: {
                    currentPage: 1,
                    perPage: this.rowsPerPage,
                    totalRecords: null,
                    totalPages: null,
                    sortField: 'name',
                    sortOrder: 1,
                    breadcrumbItems: [
                        {'label' : this.$t('menu.estates'), disabled : true },
                        {'label' : this.$t('menu.customer_list'), disabled : true },
                    ]
                }
            }
        },
        beforeMount() {
            let order = appStore().getTableOrder('customers');
            if (order != undefined) {
                this.meta.sortField = order.col;
                this.meta.sortOrder = order.dir;
            }
            
            if(appStore().toastMessage) {
                let m = appStore().toastMessage
                this.$toast.add({ severity: m.severity, summary: m.summary, detail: m.detail, life: 3000 });
                appStore().setToastMessage(null)
            }
            this.getList()
        },
        updated() {
            this.type = this.route.params.type
            this.currentPage = 1
        },
        methods: {
            getList() {
                this.loading = true
                this.customerService.list(this.meta.perPage, this.meta.currentPage, this.meta.sortField, this.meta.sortOrder)
                    .then(
                        (response) => {
                            this.customers = response.data.data
                            this.meta.totalRecords = response.data.total_rows
                            this.meta.totalPages = response.data.total_pages
                            this.loading = false
                        },
                        (errors) => {
                            this.$toast.add({ severity: 'error', summary: this.$t('app.error'), detail: errors.response.data.message, life: 3000 });
                        }
                    )
            },
            
            changePage(event) {
                this.meta.currentPage = event["page"] + 1;
                this.getList()
            },
            
            sort(event) {
                this.meta.sortField = event['sortField']
                this.meta.sortOrder = event['sortOrder']
                this.meta.currentPage = 1
                
                appStore().setTableOrder('customers', this.meta.sortField, this.meta.sortOrder);
                
                this.getList()
            },
            
            newCustomer() {
                this.$router.push({name: 'customer_new'})
            },
            
            showCustomer(customerId) {
                this.$router.push({name: 'customer_show', params: { customerId : customerId }})
            },
            
            openConfirmation(id) {
                this.displayConfirmation = true
                this.deleteCustomerId = id
            },
            
            confirmDeleteCustomer() {
                this.customerService.remove(this.deleteCustomerId)
                    .then(
                        (response) => {
                            this.getList()
                            this.$toast.add({ severity: 'success', summary: this.$t('app.success'), detail: this.$t('customers.deleted'), life: 3000 });
                        },
                        (response) => {
                            this.$toast.add({ severity: 'error', summary: this.$t('app.error'), detail: response.response.data.message, life: 3000 });
                        }
                    )
                
                this.displayConfirmation = false
                this.deleteCustomerId = null
            },
            
            closeConfirmation() {
                this.displayConfirmation = false
            },
            
            rowClick(event) {
                this.showCustomer(event.data.id)
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
                    <h4 class="inline-flex mb-0 text-color font-medium">{{ $t('menu.customer_list') }}</h4>
                    <div class="text-right mb-0 inline-flex" v-if="hasAccess('customer:create')">
                        <Button icon="pi pi-plus" v-tooltip.left="$t('customers.add_customer')" @click="newCustomer" class="text-center"></Button>
                    </div>
                </div>
                
                <DataTable :value="customers" class="p-datatable-gridlines" :totalRecords="meta.totalRecords" :rowHover="true" :lazy="true" :paginator="true" :pageCount="meta.totalPages" :rows="meta.perPage" @sort="sort($event)" @page="changePage" :loading="loading" @row-click="rowClick($event)" :sortField="this.meta.sortField" :sortOrder="this.meta.sortOrder">
                    <Column field="name" sortable :header="$t('customers.name')" style="min-width: 300px;">
                        <template #body="{ data }">
                            <router-link :to="{name: 'customer_show', params: { customerId : data.id }}" v-if="hasAccess('customer:update')">
                                {{ data.name }}
                            </router-link>
                            <span v-else>
                                {{ data.name }}
                            </span>
                            
                            <div>
                                <small>
                                    <Address :object="data" :newline="true" emptyChar=""/>
                                </small>
                            </div>
                        </template>
                    </Column>
                    <Column field="nip" sortable :header="$t('customers.nip')"></Column>
                    <Column field="delete" v-if="hasAccess('customer:delete')" style="min-width: 60px; width: 60px" class="text-center">
                        <template #body="{ data }">
                            <Button v-tooltip.bottom="$t('app.remove')" icon="pi pi-trash" class="p-button-danger p-2" style="width: auto" @click="openConfirmation(data.id)"/>
                        </template>
                    </Column>
                    <template #empty>
                        {{ $t('customers.empty_list') }}
                    </template>
                </DataTable>
                <Dialog :header="$t('app.confirmation')" v-model:visible="displayConfirmation" :style="{ width: '450px' }" :modal="true">
                    <div class="flex align-items-center justify-content-center">
                        <i class="pi pi-exclamation-triangle mr-3" style="font-size: 2rem" />
                        <span>{{ $t('app.remove_object_confirmation') }}</span>
                    </div>
                    <template #footer>
                        <Button :label="$t('app.no')" icon="pi pi-times" @click="closeConfirmation" class="p-button-text" />
                        <Button :label="$t('app.yes')" icon="pi pi-check" @click="confirmDeleteCustomer" class="p-button-danger" autofocus />
                    </template>
                </Dialog>
            </div>
        </div>
    </div>
</template>