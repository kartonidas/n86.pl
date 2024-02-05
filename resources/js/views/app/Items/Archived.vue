<script>
    import { hasAccess, setMetaTitle, getValueLabel, getValues, getItemRowColor } from '@/utils/helper'
    import { appStore } from '@/store.js'
    
    import Address from '@/views/app/_partials/Address.vue'
    import ItemService from '@/service/ItemService'
    
    export default {
        components: { Address },
        setup() {
            setMetaTitle('meta.title.items_list_archived')
            
            const itemService = new ItemService()
            
            return {
                itemService,
                hasAccess,
                getValueLabel,
                getItemRowColor
            }
        },
        data() {
            return {
                items: [],
                displayConfirmation: false,
                deleteItemId: null,
                item_types: getValues('item_types'),
                meta: {
                    search: {},
                    loading: false,
                    currentPage: 1,
                    perPage: this.rowsPerPage,
                    totalRecords: null,
                    totalPages: null,
                    breadcrumbItems: [
                        {'label' : this.$t('menu.estates'), disabled : true },
                        {'label' : this.$t('menu.items_list_archived'), disabled : true },
                    ],
                }
            }
        },
        beforeMount() {
            let filter = appStore().getTableFilter('items_archived');
            if (filter != undefined)
                this.meta.search = filter;
            
            this.meta.search.mode = "archived";
            
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
            
            showItem(itemId) {
                this.$router.push({name: 'item_show', params: { itemId : itemId }})
            },
            
            changePage(event) {
                this.meta.currentPage = event["page"] + 1;
                this.getList()
            },
            
            rowClick(event) {
                this.showItem(event.data.id)
            },
            
            search() {
                this.meta.currentPage = 1
                appStore().setTableFilter('items_archived', this.meta.search)
                this.getList()
            },
            
            resetSearch() {
                this.meta.currentPage = 1
                this.meta.search = {}
                appStore().setTableFilter('items_archived', this.meta.search)
                this.getList()
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
                    <h4 class="inline-flex mb-0 text-color font-medium">{{ $t('menu.items_list_archived') }}</h4>
                </div>
                
                <form v-on:submit.prevent="search">
                    <div class="formgrid grid mb-1">
                        <div class="col-12 md:col-4 mb-3">
                            <Dropdown v-model="meta.search.type" :showClear="this.meta.search.type ? true : false" :options="item_types" optionLabel="name" optionValue="id" :placeholder="$t('items.estate_type')" class="w-full" />
                        </div>
                        
                        <div class="col-12 md:col-3 mb-3">
                            <InputText type="text" :placeholder="$t('items.name')" class="w-full" v-model="meta.search.name"/>
                        </div>
                        
                        <div class="col-12 md:col-3 mb-3">
                            <InputText type="text" :placeholder="$t('items.address')" class="w-full" v-model="meta.search.address"/>
                        </div>
                        
                        <div class="col-12 mb-3" style="width: 120px;">
                            <Button type="submit" iconPos="left" icon="pi pi-search" class="mr-2"/>
                            <Button severity="secondary" outlined iconPos="left" icon="pi pi-filter-slash" @click="resetSearch"/>
                        </div>
                    </div>
                </form>
                
                <DataTable :rowClass="({ mode }) => getItemRowColor(mode)" :value="items" stripedRows class="p-datatable-gridlines clickable" :totalRecords="meta.totalRecords" :rowHover="true" :lazy="true" :paginator="true" :pageCount="meta.totalPages" :rows="meta.perPage" @page="changePage" :loading="meta.loading" @row-click="rowClick($event)">
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
                    <template #empty>
                        {{ $t('items.empty_list') }}
                    </template>
                </DataTable>
            </div>
        </div>
    </div>
</template>