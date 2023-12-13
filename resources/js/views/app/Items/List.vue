<script>
    import { hasAccess, setMetaTitle, getValueLabel, getValues } from '@/utils/helper'
    import { appStore } from '@/store.js'
    
    import Address from '@/views/app/_partials/Address.vue'
    import ItemService from '@/service/ItemService'
    
    export default {
        components: { Address },
        setup() {
            setMetaTitle('meta.title.items_list')
            
            const itemService = new ItemService()
            
            return {
                itemService,
                hasAccess,
                getValueLabel
            }
        },
        data() {
            return {
                items: [],
                displayConfirmation: false,
                deleteItemId: null,
                item_types: getValues('item_types'),
                rented: [
                    {"id": 0, "name" : this.$t('items.free')},
                    {"id": 1, "name" : this.$t('items.rented')},
                ],
                meta: {
                    search: {},
                    loading: false,
                    currentPage: 1,
                    perPage: this.rowsPerPage,
                    totalRecords: null,
                    totalPages: null,
                    breadcrumbItems: [
                        {'label' : this.$t('menu.estates'), disabled : true },
                        {'label' : this.$t('menu.estate_list'), disabled : true },
                    ],
                }
            }
        },
        beforeMount() {
            let filter = appStore().getTableFilter('items');
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
                this.meta.loading = true
                this.itemService.list(this.meta.perPage, this.meta.currentPage, null, null, this.meta.search)
                    .then(
                        (response) => {
                            this.items = response.data.data
                            this.meta.totalRecords = response.data.total_rows
                            this.meta.totalPages = response.data.total_pages
                            this.meta.loading = false
                        },
                        (errors) => {
                            this.$toast.add({ severity: 'error', summary: this.$t('app.error'), detail: errors.response.data.message, life: 3000 });
                        }
                    );
            },
            
            newItem() {
                this.$router.push({name: 'item_new'})
            },
            
            showItem(itemId) {
                this.$router.push({name: 'item_show', params: { itemId : itemId }})
            },
            
            changePage(event) {
                this.meta.currentPage = event["page"] + 1;
                this.getList()
            },
            
            openConfirmation(id) {
                this.displayConfirmation = true
                this.deleteItemId = id
            },
            
            confirmDeleteItem() {
                this.itemService.remove(this.deleteItemId)
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
                this.deleteItemId = null
            },
            
            closeConfirmation() {
                this.displayConfirmation = false
            },
            
            rowClick(event) {
                this.showItem(event.data.id)
            },
            
            search() {
                this.meta.currentPage = 1
                appStore().setTableFilter('items', this.meta.search)
                this.getList()
            },
            
            resetSearch() {
                this.meta.currentPage = 1
                this.meta.search = {}
                appStore().setTableFilter('items', this.meta.search)
                this.getList()
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
                    <h4 class="inline-flex mb-0 text-color font-medium">{{ $t('menu.estate_list') }}</h4>
                    <div class="text-right mb-0 inline-flex" v-if="hasAccess('item:create')">
                        <Button icon="pi pi-plus" v-tooltip.left="$t('items.add_estate')" @click="newItem" class="text-center"></Button>
                    </div>
                </div>
                
                <form v-on:submit.prevent="search">
                    <div class="formgrid grid mb-1">
                        <div class="col-12 md:col-6 mb-3">
                            <Dropdown v-model="meta.search.type" :showClear="this.meta.search.type ? true : false" :options="item_types" optionLabel="name" optionValue="id" :placeholder="$t('items.estate_type')" class="w-full" />
                        </div>
                        
                        <div class="col-12 md:col-6 mb-3">
                            <InputText type="text" :placeholder="$t('items.name')" class="w-full" v-model="meta.search.name"/>
                        </div>
                        
                        <div class="col-12 md:col-6 mb-3">
                            <InputText type="text" :placeholder="$t('items.address')" class="w-full" v-model="meta.search.address"/>
                        </div>
                        
                        <div class="col-12 md:col-3 sm:col-8 mb-3">
                            <Dropdown v-model="meta.search.rented" :showClear="this.meta.search.rented != undefined ? true : false" :options="rented" optionLabel="name" optionValue="id" :placeholder="$t('items.status')" class="w-full" />
                        </div>
                        
                        <div class="col-12 mb-3" style="width: 120px;">
                            <Button type="submit" iconPos="left" icon="pi pi-search" class="mr-2"/>
                            <Button severity="secondary" outlined iconPos="left" icon="pi pi-filter-slash" @click="resetSearch"/>
                        </div>
                    </div>
                </form>
                
                <DataTable :value="items" stripedRows class="p-datatable-gridlines clickable" :totalRecords="meta.totalRecords" :rowHover="true" :lazy="true" :paginator="true" :pageCount="meta.totalPages" :rows="meta.perPage" @page="changePage" :loading="meta.loading" @row-click="rowClick($event)">
                    <Column field="name" :header="$t('items.name')" style="min-width: 300px;">
                        <template #body="{ data }">
                            <Badge :value="getValueLabel('item_types', data.type)" class="font-normal" severity="info"></Badge>
                            <div class="mt-1">
                                <router-link :to="{name: 'item_show', params: { itemId : data.id }}">
                                    {{ data.name }}
                                </router-link>
                                
                                <div>
                                    <small>
                                        <Address :object="data" :newline="true" emptyChar=""/>
                                    </small>
                                </div>
                            </div>
                        </template>
                    </Column>
                    <Column :header="$t('items.rented')" class="text-center" style="width: 120px;">
                        <template #body="{ data }">
                            <Badge v-if="data.rented" severity="success" :value="$t('app.yes')"></Badge>
                            <Badge v-else severity="secondary" :value="$t('app.no')"></Badge>
                        </template>
                    </Column>
                    <Column field="delete" v-if="hasAccess('item:delete')" style="min-width: 60px; width: 60px" class="text-center">
                        <template #body="{ data }">
                            <Button :disabled="!data.can_delete" icon="pi pi-trash" v-tooltip.bottom="$t('app.remove')" class="p-button-danger p-2" style="width: auto" @click="openConfirmation(data.id)"/>
                        </template>
                    </Column>
                    <template #empty>
                        {{ $t('items.empty_list') }}
                    </template>
                </DataTable>
                <Dialog :header="$t('app.confirmation')" v-model:visible="displayConfirmation" :style="{ width: '450px' }" :modal="true">
                    <div class="flex align-items-center justify-content-center">
                        <i class="pi pi-exclamation-triangle mr-3" style="font-size: 2rem" />
                        <span>{{ $t('app.remove_object_confirmation') }}</span>
                    </div>
                    <template #footer>
                        <Button :label="$t('app.no')" icon="pi pi-times" @click="closeConfirmation" class="p-button-text" />
                        <Button :label="$t('app.yes')" icon="pi pi-check" @click="confirmDeleteItem" class="p-button-danger" autofocus />
                    </template>
                </Dialog>
            </div>
        </div>
    </div>
</template>