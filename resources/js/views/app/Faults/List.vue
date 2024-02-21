<script>
    import { ref } from 'vue'
    import { hasAccess, setMetaTitle, getValues, getValueLabel } from '@/utils/helper'
    import { appStore } from '@/store.js'
    
    import Address from '@/views/app/_partials/Address.vue'
    import FaultService from '@/service/FaultService'
    import DictionaryService from '@/service/DictionaryService'
    import moment from 'moment'
    
    export default {
        components: { Address },
        setup() {
            setMetaTitle('meta.title.faults_list')
            
            const faultService = new FaultService()
            const dictionaryService = new DictionaryService()
            
            return {
                hasAccess,
                faultService,
                dictionaryService,
                getValueLabel
            }
        },
        data() {
            return {
                loading: false,
                errors: [],
                faults: [],
                displayConfirmation: false,
                deleteFaultId: null,
                loadingFaultStatusesDictionary: false,
                faultStatuses: [],
                priorities: getValues('faults.priorities'),
                meta: {
                    list: {
                        first: appStore().getDTSessionStateFirst("dt-state-faults-table"),
                        size: this.rowsPerPage,
                        sort: 'full_number',
                        order: -1,
                    },
                    search: {},
                    totalRecords: null,
                    totalPages: null,
                    breadcrumbItems: [
                        {'label' : this.$t('menu.estates'), disabled : true },
                        {'label' : this.$t('menu.faults'), disabled : true },
                    ],
                }
            }
        },
        beforeMount() {
            let order = appStore().getTableOrder('faults');
            if (order != undefined) {
                this.meta.list.sort = order.col;
                this.meta.list.order = order.dir;
            }
            
            let filter = appStore().getTableFilter('faults');
            if (filter != undefined)
            {
                this.meta.search = filter;
                this.meta.search.start = this.meta.search.start ? new Date(this.meta.search.start) : null
                this.meta.search.end = this.meta.search.end ? new Date(this.meta.search.end) : null
            }
                
            if(appStore().toastMessage) {
                let m = appStore().toastMessage
                this.$toast.add({ severity: m.severity, summary: m.summary, detail: m.detail, life: 3000 });
                appStore().setToastMessage(null)
            }
            
            
            this.loadingFaultStatusesDictionary = true
            this.dictionaryService.listByType('fault_statuses', {size: 999, first: 0})
                .then(
                    (response) => {
                        if (response.data.data.length) {
                            response.data.data.forEach((i) => {
                                this.faultStatuses.push({"id" : i.id, "name" : i.name })
                                this.loadingFaultStatusesDictionary = false
                            })
                        }
                    },
                    (error) => {
                        this.$toast.add({ severity: 'error', summary: this.$t('app.error'), detail: errors.response.data.message, life: 3000 });
                    },
                )
            
            this.getList()
        },
        methods: {
            getList() {
                this.loading = true
                
                let search = Object.assign({}, this.meta.search)
                search.start = search.start ? moment(search.start).format("YYYY-MM-DD") : null
                search.end = search.end ? moment(search.end).format("YYYY-MM-DD") : null
                
                this.faultService.list(this.meta.list, search)
                    .then(
                        (response) => {
                            this.faults = response.data.data
                            this.meta.totalRecords = response.data.total_rows
                            this.meta.totalPages = response.data.total_pages
                            this.loading = false
                        },
                        (errors) => {
                            this.$toast.add({ severity: 'error', summary: this.$t('app.error'), detail: errors.response.data.message, life: 3000 });
                        }
                    );
            },
            
            newFault() {
                this.$router.push({name: 'fault_new'})
            },
            
            showFault(faultId) {
                this.$router.push({name: 'fault_show', params: { faultId : faultId }})
            },
            
            changePage(event) {
                this.meta.list.first = event["first"];
                this.getList()
            },
            
            sort(event) {
                this.meta.list.sort = event['sortField']
                this.meta.list.order = event['sortOrder']
                this.meta.list.first = 0
                
                appStore().setTableOrder('faults', this.meta.list.sort, this.meta.list.order);
                
                this.getList()
            },
            
            rowClick(event) {
                this.showFault(event.data.id)
            },
            
            search() {
                this.meta.list.first = 0
                appStore().setTableFilter('faults', this.meta.search)
                this.getList()
            },
            
            resetSearch() {
                this.meta.list.first = 0
                this.meta.search = {}
                appStore().setTableFilter('faults', this.meta.search)
                this.getList()
            },
            
            openConfirmation(id) {
                this.displayConfirmation = true
                this.deleteFaultId = id
            },
            
            confirmDeleteFault() {
                this.faultService.remove(this.deleteFaultId)
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
                this.deleteFaultId = null
            },
            
            closeConfirmation() {
                this.displayConfirmation = false
            },
        },
    }
</script>

<template>
    <Breadcrumb :model="meta.breadcrumbItems"/>
    
    <div class="grid mt-1">
        <div class="col-12">
            <div class="card pt-4">
                <Help show="item:fault" class="text-right mb-3"/>
                <div class="flex justify-content-between align-items-center mb-5">
                    <h4 class="inline-flex mb-0 text-color font-medium">{{ $t('menu.faults') }}</h4>
                    <div class="text-right mb-0 inline-flex" v-if="hasAccess('fault:create')">
                        <Button icon="pi pi-plus" v-tooltip.left="$t('faults.add_fault')" @click="newFault" class="text-center"></Button>
                    </div>
                </div>
                <form v-on:submit.prevent="search">
                    <div class="formgrid grid mb-1">
                        <div class="col-12 md:col-4 mb-3">
                            <InputText type="text" :placeholder="$t('faults.item_name')" class="w-full" v-model="meta.search.item_name"/>
                        </div>
                        <div class="col-12 md:col-4 mb-3">
                            <InputText type="text" :placeholder="$t('faults.item_address')" class="w-full" v-model="meta.search.item_address"/>
                        </div>
                        <div class="col-12 md:col-4 mb-3">
                            <Dropdown id="priority" v-model="meta.search.priority" :options="priorities" optionLabel="name" optionValue="id" :placeholder="$t('faults.priority')" class="w-full"/>
                        </div>
                        
                        <div class="col-12 md:col-4 sm:col-12 mb-3">
                            <Dropdown id="status_id" v-model="meta.search.status" :loading="loadingFaultStatusesDictionary" :options="faultStatuses" optionLabel="name" optionValue="id" :placeholder="$t('faults.status')" class="w-full"/>
                        </div>
                        
                        <div class="col-12 md:col-6 sm:col-9 mb-3">
                            <div class="flex">
                                <div class="col-6 p-0 pr-1">
                                    <Calendar id="start" v-model="meta.search.start" :placeholder="$t('faults.start_date_list')" class="w-auto" showIcon/>
                                </div>
                                <div class="col-6 p-0 pl-1">
                                    <Calendar id="end" v-model="meta.search.end" :placeholder="$t('faults.end_date_list')" showIcon/>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-12 mb-3" style="width: 120px;">
                            <Button type="submit" iconPos="left" icon="pi pi-search" class="mr-2"/>
                            <Button severity="secondary" outlined iconPos="left" icon="pi pi-filter-slash" @click="resetSearch"/>
                        </div>
                    </div>
                </form>
                <DataTable :value="faults" stripedRows class="p-datatable-gridlines clickable" :totalRecords="meta.totalRecords" :rowHover="true" :lazy="true" :paginator="true" :pageCount="meta.totalPages" :rows="meta.list.size" :first="meta.list.first" @sort="sort($event)" @page="changePage" :loading="loading" @row-click="rowClick($event)" :sortField="meta.list.sort" :sortOrder="meta.list.order" stateStorage="session" stateKey="dt-state-faults-table">
                    <Column :header="$t('faults.status')">
                        <template #body="{ data }">
                            <i class="pi pi-arrow-down" v-if="data.priority == 'low'" style="font-size: 0.8rem; color: var(--gray-300)"></i>
                            <i class="pi pi-arrow-up" v-if="data.priority == 'normal'" style="font-size: 0.8rem; color: var(--base-500)"></i>
                            <i class="pi pi-exclamation-circle" v-if="data.priority == 'high'" style="font-size: 0.8rem; color: var(--red-500)"></i>
                            {{ data.status.name }}
                        </template>
                    </Column>
                    <Column :header="$t('faults.estate')">
                        <template #body="{ data }">
                            <div :class="data.item.mode == 'archived' ? 'archived-item' : ''">
                                <Badge :value="getValueLabel('item_types', data.item.type)" class="font-normal" severity="info"></Badge>
                                <div class="mt-1">
                                    <i class="pi pi-lock pr-1" v-if="data.item.mode == 'locked'" v-tooltip.top="$t('items.locked')"></i>
                                    {{ data.item.name }}
                                            
                                    <div>
                                        <small>
                                            <Address :object="data.item" :newline="true" emptyChar=""/>
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </Column>
                    <Column :header="$t('faults.add_date')" class="text-center" sortable field="created_at">
                        <template #body="{ data }">
                            {{ data.created_at }}
                        </template>
                    </Column>
                    <Column :header="$t('faults.description')">
                        <template #body="{ data }">
                            <div style="white-space: normal">
                                {{ data.description_short }}
                            </div>
                        </template>
                    </Column>
                    <Column field="delete" v-if="hasAccess('fault:delete')" style="min-width: 60px; width: 60px" class="text-center">
                        <template #body="{ data }">
                            <Button icon="pi pi-trash" v-tooltip.bottom="$t('app.remove')" class="p-button-danger p-2" style="width: auto" @click="openConfirmation(data.id)"/>
                        </template>
                    </Column>
                    <template #empty>
                        {{ $t('faults.empty_list') }}
                    </template>
                </DataTable>
                <Dialog :header="$t('app.confirmation')" v-model:visible="displayConfirmation" :style="{ width: '450px' }" :modal="true">
                    <div class="flex align-items-center justify-content-center">
                        <i class="pi pi-exclamation-triangle mr-3" style="font-size: 2rem" />
                        <span>{{ $t('app.remove_object_confirmation') }}</span>
                    </div>
                    <template #footer>
                        <Button :label="$t('app.no')" icon="pi pi-times" @click="closeConfirmation" class="p-button-text" />
                        <Button :label="$t('app.yes')" icon="pi pi-check" @click="confirmDeleteFault" class="p-button-danger" autofocus />
                    </template>
                </Dialog>
            </div>
        </div>
    </div>
    
</template>