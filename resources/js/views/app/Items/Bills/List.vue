<script>
    import { hasAccess, setMetaTitle } from '@/utils/helper'
    import { appStore } from '@/store.js'
    
    import TabMenu from './../_TabMenu.vue'
    import ItemService from '@/service/ItemService'
    
    export default {
        components: { TabMenu },
        setup() {
            setMetaTitle('meta.title.items_list')
            
            const itemService = new ItemService()
            
            return {
                itemService,
                hasAccess,
            }
        },
        data() {
            return {
                loading: false,
                item: {},
                bills: [],
                displayConfirmation: false,
                deleteBillId: null,
                meta: {
                    search: {},
                    currentPage: 1,
                    perPage: this.rowsPerPage,
                    totalRecords: null,
                    totalPages: null,
                    breadcrumbItems: [
                        {'label' : this.$t('menu.estates'), disabled : true },
                        {'label' : this.$t('menu.estate_list'), route : { name : 'items'} },
                        {'label' : this.$t('items.bills'), disabled : true },
                    ],
                }
            }
        },
        beforeMount() {
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
                
                this.itemService.get(this.$route.params.itemId)
                    .then(
                        (response) => {
                            this.item = response.data
                            this.loading = false
                        },
                        (errors) => {
                            this.$toast.add({ severity: 'error', summary: this.$t('app.error'), detail: errors.response.data.message, life: 3000 });
                        }
                    );
                
                this.itemService.bills(this.$route.params.itemId, this.meta.perPage, this.meta.currentPage, null, null, this.meta.search)
                    .then(
                        (response) => {
                            this.bills = response.data.data
                            this.meta.totalRecords = response.data.total_rows
                            this.meta.totalPages = response.data.total_pages
                            this.loading = false
                        },
                        (errors) => {
                            this.$toast.add({ severity: 'error', summary: this.$t('app.error'), detail: errors.response.data.message, life: 3000 });
                        }
                    );
            },
            
            newBill() {
                //this.$router.push({name: 'item_new'})
            },
            
            showBill(billId) {
                //this.$router.push({name: 'item_show', params: { itemId : itemId }})
            },
            
            changePage(event) {
                this.meta.currentPage = event["page"] + 1;
                this.getList()
            },
            
            openConfirmation(id) {
                this.displayConfirmation = true
                this.deleteBillId = id
            },
            
            confirmDeleteBill() {
                this.itemService.removeBill(this.deleteBillId)
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
                this.deleteBillId = null
            },
            
            closeConfirmation() {
                this.displayConfirmation = false
            },
            
            rowClick(event) {
                this.showBill(event.data.id)
            },
            
            search() {
                this.meta.currentPage = 1
                this.getList()
            },
            
            resetSearch() {
                this.meta.currentPage = 1
                this.meta.search = {}
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
                <TabMenu activeIndex="2" :item="item" class="mb-5" :showEditButton="false" :showDivider="true"/>
                
                <div class="flex justify-content-between align-items-center mb-5">
                    <h4 class="inline-flex mb-0 text-color font-medium">{{ $t('items.bills') }}</h4>
                    <div class="text-right mb-0 inline-flex" v-if="hasAccess('item:update')">
                        <Button icon="pi pi-plus" v-tooltip.left="$t('items.add_bill')" @click="newBill" class="text-center"></Button>
                    </div>
                </div>
                
                <DataTable :value="bills" stripedRows class="p-datatable-gridlines clickable" :totalRecords="meta.totalRecords" :rowHover="true" :lazy="true" :paginator="true" :pageCount="meta.totalPages" :rows="meta.perPage" @page="changePage" :loading="loading" @row-click="rowClick($event)">
                    <Column :header="$t('items.name')" style="min-width: 300px;">
                        <template #body="{ data }">
                            <Badge :value="data.bill_type_id" class="font-normal" severity="info"></Badge>
                            <div class="mt-1">
                                <router-link :to="{name: 'item_show', params: { itemId : data.id }}">
                                    {{ data.cost }}
                                </router-link>
                            </div>
                        </template>
                    </Column>
                    <Column field="delete" v-if="hasAccess('item:update')" style="min-width: 60px; width: 60px" class="text-center">
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