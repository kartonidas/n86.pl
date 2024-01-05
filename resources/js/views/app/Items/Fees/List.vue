<script>
    import { hasAccess, setMetaTitle, p } from '@/utils/helper'
    import { appStore } from '@/store.js'
    
    import TabMenu from './../_TabMenu.vue'
    import ItemService from '@/service/ItemService'
    
    export default {
        components: { TabMenu },
        props: {
            item: { type: Object },
        },
        setup() {
            setMetaTitle('meta.title.items_fee_cyclical_list')
            
            const itemService = new ItemService()
            
            return {
                itemService,
                hasAccess
            }
        },
        data() {
            return {
                fees: [],
                displayConfirmation: false,
                deleteFeeId: null,
                meta: {
                    search: {},
                    currentPage: 1,
                    perPage: this.rowsPerPage,
                    loading: false,
                    totalRecords: null,
                    totalPages: null,
                },
                p
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
                    items.push({'label' : this.$t('items.cyclical_fees'), disabled : true })
                }
                    
                return items
            },
            
            getList() {
                this.meta.loading = true
                
                this.itemService.cyclicalFees(this.$route.params.itemId, this.meta.perPage, this.meta.currentPage, null, null, this.meta.search)
                    .then(
                        (response) => {
                            this.fees = response.data.data
                            this.meta.totalRecords = response.data.total_rows
                            this.meta.totalPages = response.data.total_pages
                            this.meta.loading = false
                        },
                        (errors) => {
                            this.$toast.add({ severity: 'error', summary: this.$t('app.error'), detail: errors.response.data.message, life: 3000 });
                        }
                    );
            },
            
            newCyclicalFee() {
                this.$router.push({name: 'item_cyclical_fee_new'})
            },
            
            editCyclicalFee(feeId) {
                this.$router.push({name: 'item_cyclical_fee_edit', params: { feeId : feeId }})
            },
            
            changePage(event) {
                this.meta.currentPage = event["page"] + 1;
                this.getList()
            },
            
            openConfirmation(id) {
                this.displayConfirmation = true
                this.deleteFeeId = id
            },
            
            confirmDeleteCyclicalFee() {
                this.itemService.removeCyclicalFee(this.$route.params.itemId, this.deleteFeeId)
                    .then(
                        (response) => {
                            this.getList()
                            this.$toast.add({ severity: 'success', summary: this.$t('app.success'), detail: this.$t('items.cyclical_fee_deleted'), life: 3000 });
                        },
                        (errors) => {
                            this.$toast.add({ severity: 'error', summary: this.$t('app.error'), detail: errors.response.data.message, life: 3000 });
                        }
                    )
                
                this.displayConfirmation = false
                this.deleteFeeId = null
            },
            
            closeConfirmation() {
                this.displayConfirmation = false
            },
            
            rowClick(event) {
                this.editCyclicalFee(event.data.id)
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
    <Breadcrumb :model="getBreadcrumbs()"/>
    
    <div class="grid mt-1">
        <div class="col-12">
            <div class="card">
                <TabMenu activeIndex="fees:const" :item="item" :showEditButton="false" :showDivider="true"/>
                
                <div class="mb-5 font-italic font-light line-height-3">
                    {{ $t('help.cyclical_fee_desc')}} 
                </div>
                
                <div class="text-right mb-4" v-if="hasAccess('item:update')">
                    <Button icon="pi pi-plus" :label="$t('items.add_cyclical_fee_short')" size="small" v-tooltip.left="$t('items.add_cyclical_fee')" @click="newCyclicalFee" class="text-center"></Button>
                </div>
                
                <DataTable :value="fees" stripedRows class="p-datatable-gridlines clickable" :totalRecords="meta.totalRecords" :rowHover="true" :lazy="true" :paginator="true" :pageCount="meta.totalPages" :rows="meta.perPage" @page="changePage" :loading="meta.loading" @row-click="rowClick($event)">
                    <Column :header="$t('items.bill_type')" style="min-width: 300px;">
                        <template #body="{ data }">
                            <div class="mb-1">
                                {{ data.bill_type.name }}
                            </div>
                            <small class="font-italic">
                                {{ $t('items.payment') }} {{ data.payment_day }}{{ $t('items.th') }},
                                {{ $t('items.every') }} {{ data.repeat_months }}                                 
                                {{ p(data.repeat_months, $t('items.1months'), $t('items.2months'), $t('items.3months')) }}
                            </small>
                        </template>
                    </Column>
                    <Column :header="$t('items.recipient_name')">
                        <template #body="{ data }">
                            {{ data.recipient_name ?? "" }}
                        </template>
                    </Column>
                    <Column :header="$t('items.payer')">
                        <template #body="{ data }">
                            <span v-if="data.rental_id > 0">{{ $t('items.currently_tenant') }}</span>
                            <span v-else>{{ $t('items.owner') }}</span>
                        </template>
                    </Column>
                    <Column :header="$t('items.cost')" class="text-right">
                        <template #body="{ data }">
                            {{ numeralFormat(data.cost, '0.00') }}
                        </template>
                    </Column>
                    <Column field="delete" v-if="hasAccess('item:update')" style="min-width: 60px; width: 60px" class="text-center">
                        <template #body="{ data }">
                            <Button :disabled="!data.can_delete" icon="pi pi-trash" v-tooltip.bottom="$t('app.remove')" class="p-button-danger p-2" style="width: auto" @click="openConfirmation(data.id)"/>
                        </template>
                    </Column>
                    <template #empty>
                        {{ $t('items.empty_cyclical_fee_list') }}
                    </template>
                </DataTable>
                <Dialog :header="$t('app.confirmation')" v-model:visible="displayConfirmation" :style="{ width: '450px' }" :modal="true">
                    <div class="flex align-items-center justify-content-center">
                        <i class="pi pi-exclamation-triangle mr-3" style="font-size: 2rem" />
                        <span>{{ $t('app.remove_object_confirmation') }}</span>
                    </div>
                    <template #footer>
                        <Button :label="$t('app.no')" icon="pi pi-times" @click="closeConfirmation" class="p-button-text" />
                        <Button :label="$t('app.yes')" icon="pi pi-check" @click="confirmDeleteCyclicalFee" class="p-button-danger" autofocus />
                    </template>
                </Dialog>
            </div>
        </div>
    </div>
</template>