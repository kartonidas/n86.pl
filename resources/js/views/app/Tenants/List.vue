<script>
    import { useRouter, useRoute } from 'vue-router'
    import { hasAccess, setMetaTitle, getValueLabel, getValues } from '@/utils/helper'
    import { appStore } from '@/store.js'
    
    import Address from '@/views/app/_partials/Address.vue'
    import TenantService from '@/service/TenantService'
    
    export default {
        components: { Address },
        setup() {
            setMetaTitle('meta.title.tenants_list')
            
            const router = useRouter()
            const route = useRoute()
            const tenantService = new TenantService()
            
            return {
                router,
                route,
                tenantService,
                hasAccess,
                getValueLabel
            }
        },
        data() {
            return {
                loading: true,
                tenants: [],
                displayConfirmation: false,
                deleteTenantId: null,
                tenant_types: getValues('tenant_types'),
                meta: {
                    list: {
                        first: appStore().getDTSessionStateFirst("dt-state-tenants-table"),
                        size: this.rowsPerPage,
                        sort: 'name',
                        order: 1,
                    },
                    search: {},
                    totalRecords: null,
                    totalPages: null,
                    breadcrumbItems: [
                        {'label' : this.$t('menu.estates'), disabled : true },
                        {'label' : this.$t('menu.tenant_list'), disabled : true },
                    ],
                }
            }
        },
        beforeMount() {
            let order = appStore().getTableOrder('tenants');
            if (order != undefined) {
                this.meta.list.sort = order.col;
                this.meta.list.order = order.dir;
            }
            
            let filter = appStore().getTableFilter('tenants');
            if (filter != undefined)
                this.meta.search = filter;
            
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
                this.tenantService.list(this.meta.list, this.meta.search)
                    .then(
                        (response) => {
                            this.tenants = response.data.data
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
                this.meta.list.first = event["first"];
                this.getList()
            },
            
            sort(event) {
                this.meta.list.sort = event['sortField']
                this.meta.list.order = event['sortOrder']
                this.meta.list.first = 0
                
                appStore().setTableOrder('tenants', this.meta.list.sort, this.meta.list.order);
                
                this.getList()
            },
            
            newTenant() {
                this.router.push({name: 'tenant_new'})
            },
            
            showTenant(tenantId) {
                this.router.push({name: 'tenant_show', params: { tenantId : tenantId }})
            },
            
            openConfirmation(id) {
                this.displayConfirmation = true
                this.deleteTenantId = id
            },
            
            confirmDeleteTenant() {
                this.tenantService.remove(this.deleteTenantId)
                    .then(
                        (response) => {
                            this.getList()
                            this.$toast.add({ severity: 'success', summary: this.$t('app.success'), detail: this.$t('tenants.deleted'), life: 3000 });
                        },
                        (errors) => {
                            this.$toast.add({ severity: 'error', summary: this.$t('app.error'), detail: errors.response.data.message, life: 3000 });
                        }
                    )
                
                this.displayConfirmation = false
                this.deleteTenantId = null
            },
            
            closeConfirmation() {
                this.displayConfirmation = false
            },
            
            rowClick(event) {
                if (hasAccess('tenant:update')) 
                    this.showTenant(event.data.id)
            },
            
            search() {
                this.meta.list.first = 0
                appStore().setTableFilter('tenants', this.meta.search)
                this.getList()
            },
            
            resetSearch() {
                this.meta.list.first = 0
                this.meta.search = {}
                appStore().setTableFilter('tenants', this.meta.search)
                this.getList()
            }
        },
    }
</script>

<template>
    <Breadcrumb :model="meta.breadcrumbItems"/>
    
    <div class="grid mt-1">
        <div class="col-12">
            <div class="card pt-4">
                <Help show="tenant_customer" mark="tenant_customer:tenant" class="text-right mb-3"/>
                <div class="flex justify-content-between align-items-center mb-5">
                    <h4 class="inline-flex mb-0 text-color font-medium">{{ $t('menu.tenant_list') }}</h4>
                    <div class="text-right mb-0 inline-flex" v-if="hasAccess('tenant:create')">
                        <Button icon="pi pi-plus" v-tooltip.left="$t('tenants.add_tenant')" @click="newTenant" class="text-center"></Button>
                    </div>
                </div>
                
                <form v-on:submit.prevent="search">
                    <div class="formgrid grid mb-1">
                        <div class="col-12 md:col-6 mb-3">
                            <Dropdown v-model="meta.search.type" :showClear="this.meta.search.type ? true : false" :options="tenant_types" optionLabel="name" optionValue="id" :placeholder="$t('tenants.account_type')" class="w-full" />
                        </div>
                        
                        <div class="col-12 md:col-6 mb-3">
                            <InputText type="text" :placeholder="$t('tenants.name')" class="w-full" v-model="meta.search.name"/>
                        </div>
                        
                        <div class="col-12 md:col-6 mb-3">
                            <InputText type="text" :placeholder="$t('tenants.address')" class="w-full" v-model="meta.search.address"/>
                        </div>
                        
                        <div class="col-12 md:col-4 mb-3">
                            <InputText type="text" :placeholder="$t('tenants.nip_pesel')" class="w-full" v-model="meta.search.pesel_nip"/>
                        </div>
                        
                        <div class="col-12 mb-3" style="width: 120px;">
                            <Button type="submit" iconPos="left" icon="pi pi-search" class="mr-2"/>
                            <Button severity="secondary" outlined iconPos="left" icon="pi pi-filter-slash" @click="resetSearch"/>
                        </div>
                    </div>
                </form>
                
                <DataTable :value="tenants" stripedRows class="p-datatable-gridlines clickable" :totalRecords="meta.totalRecords" :rowHover="true" :lazy="true" :paginator="true" :pageCount="meta.totalPages" :rows="meta.list.size" :first="meta.list.first" @sort="sort($event)" @page="changePage" :loading="loading" @row-click="rowClick($event)" :sortField="this.meta.list.sort" :sortOrder="this.meta.list.order" stateStorage="session" stateKey="dt-state-tenants-table">
                    <Column field="name" sortable :header="$t('tenants.name')" style="min-width: 300px;">
                        <template #body="{ data }">
                            <Badge :value="getValueLabel('tenant_types', data.type)" class="font-normal" severity="info"></Badge>
                            <div class="mt-1">
                                <router-link :to="{name: 'tenant_show', params: { tenantId : data.id }}" v-if="hasAccess('tenant:update')">
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
                            </div>
                        </template>
                    </Column>
                    <Column :header="$t('tenants.nip_pesel')">
                        <template #body="{ data }">
                            <span v-if="data.type == 'firm' && data.nip">{{ data.nip }}</span>
                            <span v-if="data.type == 'person' && data.pesel">{{ data.pesel }}</span>
                        </template>
                    </Column>
                    <Column field="delete" v-if="hasAccess('tenant:delete')" style="min-width: 60px; width: 60px" class="text-center">
                        <template #body="{ data }">
                            <Button :disabled="!data.can_delete" v-tooltip.bottom="$t('app.remove')" icon="pi pi-trash" class="p-button-danger p-2" style="width: auto" @click="openConfirmation(data.id)"/>
                        </template>
                    </Column>
                    <template #empty>
                        {{ $t('tenants.empty_list') }}
                    </template>
                </DataTable>
                <Dialog :header="$t('app.confirmation')" v-model:visible="displayConfirmation" :style="{ width: '450px' }" :modal="true">
                    <div class="flex align-items-center justify-content-center">
                        <i class="pi pi-exclamation-triangle mr-3" style="font-size: 2rem" />
                        <span>{{ $t('app.remove_object_confirmation') }}</span>
                    </div>
                    <template #footer>
                        <Button :label="$t('app.no')" icon="pi pi-times" @click="closeConfirmation" class="p-button-text" />
                        <Button :label="$t('app.yes')" icon="pi pi-check" @click="confirmDeleteTenant" class="p-button-danger" autofocus />
                    </template>
                </Dialog>
            </div>
        </div>
    </div>
</template>