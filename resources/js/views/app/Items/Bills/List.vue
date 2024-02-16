<script>
    import { hasAccess, setMetaTitle } from '@/utils/helper'
    import { appStore } from '@/store.js'
    
    import TabMenu from './../_TabMenu.vue'
    import ItemService from '@/service/ItemService'
    
    export default {
        components: { TabMenu },
        props: {
            item: { type: Object },
        },
        setup() {
            setMetaTitle('meta.title.items_bill_list')
            
            const itemService = new ItemService()
            
            return {
                itemService,
                hasAccess,
            }
        },
        data() {
            return {
                bills: [],
                displayConfirmation: false,
                deleteBillId: null,
                meta: {
                    list: {
                        first: 0,
                        size: this.rowsPerPage,
                    },
                    search: {},
                    loading: false,
                    totalRecords: null,
                    totalPages: null,
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
            getBreadcrumbs() {
                let items = [
                    {'label' : this.$t('menu.estates'), disabled : true },
                    {'label' : this.$t('menu.estate_list'), route : { name : 'items'} },
                ]
                
                if(this.item.name != undefined)
                {
                    items.push({'label' : this.item.name, route : { name : 'item_show'} })
                    items.push({'label' : this.$t('items.bills'), disabled : true })
                }
                    
                return items
            },
            
            getList() {
                this.meta.loading = true
                
                this.itemService.bills(this.$route.params.itemId, this.meta.list, this.meta.search)
                    .then(
                        (response) => {
                            this.bills = response.data.data
                            this.meta.totalRecords = response.data.total_rows
                            this.meta.totalPages = response.data.total_pages
                            this.meta.loading = false
                        },
                        (errors) => {
                            this.$toast.add({ severity: 'error', summary: this.$t('app.error'), detail: errors.response.data.message, life: 3000 });
                        }
                    );
            },
            
            newBill() {
                this.$router.push({name: 'item_bill_new'})
            },
            
            showBill(billId) {
                this.$router.push({name: 'item_bill_show', params: { billId : billId }})
            },
            
            changePage(event) {
                this.meta.list.first = event["first"];
                this.getList()
            },
            
            openConfirmation(id) {
                this.displayConfirmation = true
                this.deleteBillId = id
            },
            
            confirmDeleteBill() {
                this.itemService.removeBill(this.$route.params.itemId, this.deleteBillId)
                    .then(
                        (response) => {
                            this.getList()
                            this.$toast.add({ severity: 'success', summary: this.$t('app.success'), detail: this.$t('items.bill_deleted'), life: 3000 });
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
                this.meta.list.first = 0
                this.getList()
            },
            
            resetSearch() {
                this.meta.list.first = 0
                this.meta.search = {}
                this.getList()
            }
        },
    }
</script>

<template>
    <Breadcrumb :model="getBreadcrumbs()"/>
    <div class="grid mt-1">
        <div class="col-12">
            <div class="card">
                <TabMenu activeIndex="fees:bills" :item="item" :showEditButton="false" :showDivider="true"/>
                
                <div class="mb-5 font-italic font-light line-height-3">
                    {{ $t('help.bill_desc')}} 
                </div>
                
                <div class="text-right mb-4" v-if="hasAccess('item:update') && item.can_edit">
                    <Button icon="pi pi-plus" :label="$t('items.add_bill_short')" size="small" v-tooltip.left="$t('items.add_bill')" @click="newBill" class="text-center"></Button>
                </div>
                
                <DataTable :rowClass="({ out_off_date }) => out_off_date ? 'bg-red-100': null" :value="bills" stripedRows class="p-datatable-gridlines clickable" :totalRecords="meta.totalRecords" :rowHover="true" :lazy="true" :paginator="true" :pageCount="meta.totalPages" :rows="meta.list.size" :first="meta.list.first" @page="changePage" :loading="meta.loading" @row-click="rowClick($event)">
                    <Column :header="$t('items.bill_type')" style="min-width: 300px;">
                        <template #body="{ data }">
                            <div class="mb-1 flex">
                                <span v-if="data.out_off_date" class="mr-1" v-tooltip.top="$t('items.bill_out_off_date')">
                                    <i class="pi pi-exclamation-circle" style="font-size: 1.2rem; color: var(--red-600)"></i>
                                </span>
                                {{ data.bill_type.name }}
                            </div>
                            <div class="mt-1" v-if="data.cyclical">
                                <small class="font-italic">
                                    <i class="pi pi-replay"></i>
                                    {{ $t("items.cyclical_fee") }}
                                </small>
                            </div>
                        </template>
                    </Column>
                    <Column :header="$t('items.payer')">
                        <template #body="{ data }">
                            <span v-if="data.rental_id > 0">
                                <span v-if="data.rental">
                                    <div>{{ $t('items.tenant') }}</div>
                                    <small>
                                        <router-link :to="{name: 'rental_show', params: { rentalId : data.rental_id }}" v-if="hasAccess('rent:list')" target="_blank">
                                            {{ data.rental.full_number }}
                                        </router-link>
                                    </small>
                                </span>
                                <span v-else>
                                    {{ $t('items.currently_tenant') }}
                                </span>
                            </span>
                            <span v-else>{{ $t('items.owner') }}</span>
                        </template>
                    </Column>
                    <Column :header="$t('items.cost')" class="text-right">
                        <template #body="{ data }">
                            {{ numeralFormat(data.cost, '0.00') }} {{ data.currency }}
                        </template>
                    </Column>
                    <Column :header="$t('items.payment_date')" class="text-center">
                        <template #body="{ data }">
                            {{ data.payment_date }}
                        </template>
                    </Column>
                    <Column :header="$t('items.paid')" class="text-center">
                        <template #body="{ data }">
                            <Badge :value="$t('app.yes')" class="uppercase font-normal" severity="success" v-if="data.paid"></Badge>
                            <Badge :value="$t('app.no')" class="uppercase font-normal" severity="danger" v-if="!data.paid"></Badge>
                            <div v-if="data.paid">
                                <small>
                                    {{ data.paid_date }}
                                </small>
                            </div>
                        </template>
                    </Column>
                    <Column field="delete" v-if="hasAccess('item:update') && item.can_edit" style="min-width: 60px; width: 60px" class="text-center">
                        <template #body="{ data }">
                            <Button :disabled="!data.can_delete" icon="pi pi-trash" v-tooltip.bottom="$t('app.remove')" class="p-button-danger p-2" style="width: auto" @click="openConfirmation(data.id)"/>
                        </template>
                    </Column>
                    <template #empty>
                        {{ $t('items.empty_bills_list') }}
                    </template>
                </DataTable>
                <Dialog :header="$t('app.confirmation')" v-model:visible="displayConfirmation" :style="{ width: '450px' }" :modal="true">
                    <div class="flex align-items-center justify-content-center">
                        <i class="pi pi-exclamation-triangle mr-3" style="font-size: 2rem" />
                        <span>{{ $t('app.remove_object_confirmation') }}</span>
                    </div>
                    <template #footer>
                        <Button :label="$t('app.no')" icon="pi pi-times" @click="closeConfirmation" class="p-button-text" />
                        <Button :label="$t('app.yes')" icon="pi pi-check" @click="confirmDeleteBill" class="p-button-danger" autofocus />
                    </template>
                </Dialog>
            </div>
        </div>
    </div>
</template>