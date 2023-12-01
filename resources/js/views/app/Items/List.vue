<script>
    import { useRouter } from 'vue-router'
    import { useToast } from 'primevue/usetoast';
    import { hasAccess, setMetaTitle } from '@/utils/helper'
    import { appStore } from '@/store.js'
    
    import Address from '@/views/app/_partials/Address.vue'
    import ItemService from '@/service/ItemService'
    
    export default {
        components: { Address },
        setup() {
            setMetaTitle('meta.title.items_list')
            
            const router = useRouter()
            const itemService = new ItemService()
            const toast = useToast();
            
            return {
                router,
                itemService,
                toast,
                hasAccess
            }
        },
        data() {
            return {
                loading: false,
                items: [],
                displayConfirmation: false,
                deleteItemId: null,
                meta: {
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
        mounted() {
            if(appStore().toastMessage) {
                let m = appStore().toastMessage
                this.toast.add({ severity: m.severity, summary: m.summary, detail: m.detail, life: 3000 });
                appStore().setToastMessage(null)
            }
            this.getList()
        },
        methods: {
            getList() {
                this.loading = true
                this.itemService.list(this.meta.perPage, this.meta.currentPage)
                    .then(
                        (response) => {
                            this.items = response.data.data
                            this.meta.totalRecords = response.data.total_rows
                            this.meta.totalPages = response.data.total_pages
                            this.loading = false
                        },
                        (response) => {
                            this.toast.add({ severity: 'error', summary: this.$t('app.error'), detail: errors.response.data.message, life: 3000 });
                        }
                    );
            },
            
            newItem() {
                this.router.push({name: 'item_new'})
            },
            
            showItem(itemId) {
                this.router.push({name: 'item_show', params: { itemId : itemId }})
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
                            this.toast.add({ severity: 'success', summary: this.$t('app.success'), detail: this.$t('items.deleted'), life: 3000 });
                        },
                        (response) => {
                            this.toast.add({ severity: 'error', summary: this.$t('app.error'), detail: response.response.data.message, life: 3000 });
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
                    <h5 class="inline-flex mb-0">{{ $t('menu.estate_list') }}</h5>
                    <div class="text-right mb-0 inline-flex" v-if="hasAccess('item:create')">
                        <Button icon="pi pi-plus" v-tooltip.left="$t('items.add_estate')" @click="newItem" class="text-center"></Button>
                    </div>
                </div>
                
                <DataTable :value="items" class="p-datatable-gridlines" :totalRecords="meta.totalRecords" :rowHover="true" :lazy="true" :paginator="true" :pageCount="meta.totalPages" :rows="meta.perPage" @page="changePage" :loading="loading" @row-click="rowClick($event)">
                    <Column field="name" :header="$t('items.name')" style="min-width: 300px;">
                        <template #body="{ data }">
                            <router-link :to="{name: 'item_show', params: { itemId : data.id }}">
                                {{ data.name }}
                            </router-link>
                            
                            <div>
                                <small>
                                    <Address :object="data" :newline="true" emptyChar=""/>
                                </small>
                            </div>
                        </template>
                    </Column>
                    <Column field="delete" v-if="hasAccess('item:delete')" style="min-width: 60px; width: 60px" class="text-center">
                        <template #body="{ data }">
                            <Button icon="pi pi-trash" v-tooltip.bottom="$t('app.remove')" class="p-button-danger p-2" style="width: auto" @click="openConfirmation(data.id)"/>
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