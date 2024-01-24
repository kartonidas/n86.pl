<script>
    import { hasAccess, setMetaTitle } from '@/utils/helper'
    import { appStore } from '@/store.js'
    import UserInvoiceService from '@/service/UserInvoiceService'
    
    export default {
        setup() {
            setMetaTitle('meta.title.sale_registries_list')
            
            const userInvoiceService = new UserInvoiceService()
            
            return {
                userInvoiceService,
                hasAccess
            }
        },
        data() {
            return {
                loading: true,
                saleRegistries: [],
                displayConfirmation: false,
                deleteSaleRegisterId: null,
                meta: {
                    currentPage: 1,
                    perPage: this.rowsPerPage,
                    totalRecords: null,
                    totalPages: null,
                    breadcrumbItems: [
                        {'label' : this.$t('menu.settlements'), disabled : true },
                        {'label' : this.$t('menu.sale_registries'), disabled : true },
                    ]
                }
            }
        },
        beforeMount() {
            if(appStore().toastMessage) {
                let m = appStore().toastMessage
                this.$toast.add({ severity: m.severity, summary: m.summary, detail: m.detail, life: 3000 });
                appStore().setToastMessage(null);
            }
            this.getList()
        },
        methods: {
            getList() {
                this.loading = true
                this.userInvoiceService.saleRegisterList(this.meta.perPage, this.meta.currentPage)
                    .then(
                        (response) => {
                            this.saleRegistries = response.data.data
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
            
            newSaleRegister() {
                this.$router.push({name: 'sale_register_new'})
            },
            
            editSaleRegister(saleRegisterId) {
                this.$router.push({name: 'sale_register_edit', params: { saleRegisterId : saleRegisterId }})
            },
            
            openConfirmation(id) {
                this.displayConfirmation = true
                this.deleteSaleRegisterId = id
            },
            
            confirmDeleteSaleRegister() {
                this.userInvoiceService.saleRegisterRemove(this.deleteSaleRegisterId)
                    .then(
                        (response) => {
                            this.getList()
                            this.$toast.add({ severity: 'success', summary: this.$t('app.success'), detail: this.$t('customer_invoices.sale_register_deleted'), life: 3000 });
                        },
                        (errors) => {
                            this.$toast.add({ severity: 'error', summary: this.$t('app.error'), detail: errors.response.data.message, life: 3000 });
                        }
                    )
                
                this.displayConfirmation = false
                this.deleteSaleRegisterId = null
            },
            
            closeConfirmation() {
                this.displayConfirmation = false
            },
            
            rowClick(event) {
                if (hasAccess('config:update')) 
                    this.editSaleRegister(event.data.id)
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
                    <h4 class="inline-flex mb-0 text-color font-medium">{{ $t('menu.sale_registries') }}</h4>
                    <div class="text-right mb-0 inline-flex" v-if="hasAccess('config:update')">
                        <Button icon="pi pi-plus" v-tooltip.left="$t('customer_invoices.new_sale_register')" @click="newSaleRegister" class="text-center"></Button>
                    </div>
                </div>
                
                <DataTable :value="saleRegistries" stripedRows class="p-datatable-gridlines clickable" :totalRecords="meta.totalRecords" :rowHover="true" :lazy="true" :paginator="true" :pageCount="meta.totalPages" :rows="meta.perPage" @page="changePage" :loading="loading" @row-click="rowClick($event)">
                    <Column :header="$t('customer_invoices.name')" style="min-width: 300px;">
                        <template #body="{ data }">
                            <router-link :to="{name: 'dictionary_edit', params: { type : data.type, dictionaryId : data.id }}" v-if="hasAccess('dictionary:update')">
                                {{ data.name }}
                            </router-link>
                            <span v-else>
                                {{ data.name }}
                            </span>
                        </template>
                    </Column>
                    <Column field="delete" v-if="hasAccess('config:update')" style="min-width: 45px; width: 45px" class="text-center">
                        <template #body="{ data }">
                            <Button icon="pi pi-trash" v-tooltip.bottom="$t('app.remove')" class="p-button-danger p-2" style="width: auto" @click="openConfirmation(data.id)" :disabled="!data.can_delete"/>
                        </template>
                    </Column>
                    <template #empty>
                        {{ $t('customer_invoices.sale_register_empty_list') }}
                    </template>
                </DataTable>
                <Dialog :header="$t('app.confirmation')" v-model:visible="displayConfirmation" :style="{ width: '450px' }" :modal="true">
                    <div class="flex align-items-center justify-content-center">
                        <i class="pi pi-exclamation-triangle mr-3" style="font-size: 2rem" />
                        <span>{{ $t('app.remove_object_confirmation') }}</span>
                    </div>
                    <template #footer>
                        <Button :label="$t('app.no')" icon="pi pi-times" @click="closeConfirmation" class="p-button-text" />
                        <Button :label="$t('app.yes')" icon="pi pi-check" @click="confirmDeleteSaleRegister" class="p-button-danger" autofocus />
                    </template>
                </Dialog>
            </div>
        </div>
    </div>
</template>