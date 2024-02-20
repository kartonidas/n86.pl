<script>
    import { hasAccess, setMetaTitle, getValueLabel, getValues, getItemRowColor } from '@/utils/helper'
    import { appStore } from '@/store.js'
    
    import Address from '@/views/app/_partials/Address.vue'
    import CustomerService from '@/service/CustomerService'
    import ItemService from '@/service/ItemService'
    
    export default {
        components: { Address },
        setup() {
            setMetaTitle('meta.title.items_list')
            
            const customerService = new CustomerService()
            const itemService = new ItemService()
            
            return {
                itemService,
                customerService,
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
                lockConfirmationModal: false,
                unlockConfirmationModal: false,
                lockItemId: null,
                unlockItemId: null,
                item_types: getValues('item_types'),
                rented: [
                    {"id": 0, "name" : this.$t('items.free')},
                    {"id": 1, "name" : this.$t('items.rented')},
                ],
                ownership_types: [
                    {"id": "all", "name" : this.$t('items.all')},
                    {"id": "property", "name" : this.$t('items.own')},
                    {"id": "manage", "name" : this.$t('items.i_managed')},
                ],
                customers: [],
                loadingCustomers: false,
                meta: {
                    list: {
                        first: appStore().getDTSessionStateFirst("dt-state-items-table"),
                        size: this.rowsPerPage,
                    },
                    loading: false,
                    totalRecords: null,
                    totalPages: null,
                    breadcrumbItems: [
                        {'label' : this.$t('menu.estates'), disabled : true },
                        {'label' : this.$t('menu.estate_list'), disabled : true },
                    ],
                    search: {}
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
            this.getCustomers()
        },
        methods: {
            getCustomers() {
                this.loadingCustomers = true
                this.customers = []
                this.customerService.list({size: 9999, first: 0})
                    .then(
                        (response) => {
                            this.loadingCustomers = false
                            if (response.data.data.length) {
                                response.data.data.forEach((i) => {
                                    this.customers.push({
                                        "id" : i.id,
                                        "name" : i.name,
                                        "type" : i.type,
                                        "nip" : i.nip,
                                    })
                                })
                            }
                        },
                        (errors) => {
                            this.$toast.add({ severity: 'error', summary: this.$t('app.error'), detail: errors.response.data.message, life: 3000 });
                        }
                    )
            },
            
            getList() {
                this.meta.loading = true
                this.itemService.list(this.meta.list, this.meta.search)
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
                this.meta.list.first = event["first"];
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
                this.meta.list.first = 0
                appStore().setTableFilter('items', this.meta.search)
                this.getList()
            },
            
            resetSearch() {
                this.meta.list.first = 0
                this.meta.search = {}
                appStore().setTableFilter('items', this.meta.search)
                this.getList()
            },
            
            openUnlockConfirmation(id) {
                this.unlockConfirmationModal = true
                this.unlockItemId = id
            },
            
            closeUnlockConfirmationModal() {
                this.unlockConfirmationModal = false
                this.unlockItemId = null
            },
            
            confirmUnlockItem() {
                this.itemService.unlock(this.unlockItemId)
                    .then(
                        (response) => {
                            this.getList();
                            this.unlockConfirmationModal = false
                            this.unlockItemId = null
                            this.$toast.add({ severity: 'success', summary: this.$t('app.success'), detail: this.$t('items.item_unlocked'), life: 3000 });
                        },
                        (errors) => {
                            this.$toast.add({ severity: 'error', summary: this.$t('app.error'), detail: errors.response.data.message, life: 3000 });
                        },
                    )
            },
            
            openLockConfirmation(id) {
                this.lockConfirmationModal = true
                this.lockItemId = id
            },
            
            closeLockConfirmationModal() {
                this.lockConfirmationModal = false
                this.lockItemId = null
            },
            
            confirmLockItem() {
                this.itemService.lock(this.lockItemId)
                    .then(
                        (response) => {
                            this.getList();
                            this.lockConfirmationModal = false
                            this.lockItemId = null
                            this.$toast.add({ severity: 'success', summary: this.$t('app.success'), detail: this.$t('items.item_locked'), life: 3000 });
                        },
                        (errors) => {
                            this.$toast.add({ severity: 'error', summary: this.$t('app.error'), detail: errors.response.data.message, life: 3000 });
                        },
                    )
            },
            
            archive(id) {
                this.$router.push({name: 'item_archive', params: { itemId : id } })
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
                    <h4 class="inline-flex mb-0 text-color font-medium">{{ $t('menu.estate_list') }}</h4>
                    <div class="text-right mb-0 inline-flex" v-if="hasAccess('item:create')">
                        <Button icon="pi pi-plus" v-tooltip.left="$t('items.add_estate')" @click="newItem" class="text-center"></Button>
                    </div>
                </div>
                
                <form v-on:submit.prevent="search">
                    <div class="formgrid grid mb-1">
                        <div class="col-12 md:col-4 mb-3">
                            <Dropdown v-model="meta.search.type" :showClear="this.meta.search.type ? true : false" :options="item_types" optionLabel="name" optionValue="id" :placeholder="$t('items.estate_type')" class="w-full" />
                        </div>
                        
                        <div class="col-12 md:col-4 mb-3">
                            <InputText type="text" :placeholder="$t('items.name')" class="w-full" v-model="meta.search.name"/>
                        </div>
                        
                        <div class="col-12 md:col-4 mb-3">
                            <InputText type="text" :placeholder="$t('items.address')" class="w-full" v-model="meta.search.address"/>
                        </div>
                        
                        <div class="col-12 md:col-3 mb-3">
                            <Dropdown id="ownership_type" v-model="meta.search.ownership_type" :options="ownership_types" optionLabel="name" optionValue="id" :placeholder="$t('items.ownership_type')" class="w-full"/>
                        </div>
                        
                        <div class="col-12 md:col-4 mb-3">
                            <Dropdown id="customer_id" v-model="meta.search.customer_id" filter :filterFields="['name','nip']" :loading="loadingCustomers" :options="customers" optionLabel="name" optionValue="id" :placeholder="$t('items.customer')" class="w-full">
                                <template #option="slotProps">
                                    <div class="">
                                        <div>
                                            {{ slotProps.option.name }}
                                        </div>
                                        <small class="font-italic text-gray-500">
                                            {{ getValueLabel('tenant_types', slotProps.option.type) }}
                                            <span v-if="slotProps.option.type=='firm'">
                                                : {{ slotProps.option.nip }}
                                            </span>
                                        </small>
                                    </div>
                                </template>
                            </Dropdown>
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
                
                <DataTable :rowClass="({ mode }) => getItemRowColor(mode)" :value="items" stripedRows class="p-datatable-gridlines clickable" :totalRecords="meta.totalRecords" :rowHover="true" :lazy="true" :paginator="true" :pageCount="meta.totalPages" :rows="meta.list.size" :first="meta.list.first" @page="changePage" :loading="meta.loading" @row-click="rowClick($event)" stateStorage="session" stateKey="dt-state-items-table">
                    <Column field="name" :header="$t('items.name')" style="min-width: 300px;">
                        <template #body="{ data }">
                            <Badge :value="getValueLabel('item_types', data.type)" class="font-normal" severity="info"></Badge>
                            <div class="mt-1">
                                <i class="pi pi-lock pr-1" v-if="data.mode == 'locked'" v-tooltip.top="$t('items.locked')"></i>
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
                    <Column :header="$t('items.owner')">
                        <template #body="{ data }">
                            <template v-if="data.ownership_type == 'manage'">
                                <Badge :value="getValueLabel('customer_types', data.customer.type)" class="font-normal" severity="info"></Badge>
                                <div class="mt-1">
                                    <router-link :to="{name: 'customer_show', params: { customerId : data.id }}">
                                        {{ data.customer.name }}
                                    </router-link>
                                    
                                    <div>
                                        <small>
                                            <Address :object="data.customer" :newline="true" emptyChar=""/>
                                        </small>
                                    </div>
                                </div>
                            </template>
                            <template v-else>
                                {{ $t('items.own') }}
                            </template> 
                        </template>
                    </Column>
                    <Column :header="$t('items.rented')" class="text-center" style="width: 120px;">
                        <template #body="{ data }">
                            <Badge v-if="data.rented" severity="success" :value="$t('app.yes')"></Badge>
                            <Badge v-else severity="secondary" :value="$t('app.no')"></Badge>
                            
                            <template v-if="data.rentals">
                                <div class="text-sm text-gray-600 mt-2">
                                    <div v-if="data.rentals.current">
                                        {{ $t('items.end') }}: {{ data.rentals.current.end }}
                                    </div>
                                    <div v-if="data.rentals.next">
                                        {{ $t('items.reservation_single') }}: {{ data.rentals.next.start }}-{{ data.rentals.next.end }}
                                    </div>
                                </div>
                            </template>
                        </template>
                    </Column>
                    <Column field="delete" v-if="hasAccess('item:delete') || hasAccess('item:update')" style="min-width: 60px; width: 60px" class="text-center">
                        <template #body="{ data }">
                            <template v-if="data.mode != 'archived'">
                                <template v-if="hasAccess('item:delete')">
                                    <Button :disabled="!data.can_delete" icon="pi pi-trash" v-tooltip.bottom="$t('app.remove')" class="p-button-danger p-2 mr-2" style="width: auto" @click="openConfirmation(data.id)"/>
                                </template>
                                <template v-if="hasAccess('item:update')">
                                    <Button v-if="data.mode == 'locked'" :disabled="!data.can_unlock" icon="pi pi-unlock" v-tooltip.bottom="$t('items.unlock')" severity="warning" class="p-2" style="width: auto" @click="openUnlockConfirmation(data.id)"/>
                                    <Button v-if="data.mode == 'normal'" :disabled="!data.can_lock" icon="pi pi-lock" v-tooltip.bottom="$t('items.lock')" severity="danger" class="p-2" style="width: auto" @click="openLockConfirmation(data.id)"/>
                                    <Button :disabled="!data.can_archive" icon="pi pi-inbox" v-tooltip.bottom="$t('items.archive')" severity="contrast" class="p-2 ml-2" style="width: auto" @click="archive(data.id)"/>
                                </template>
                            </template>
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
                <Dialog v-model:visible="lockConfirmationModal" :closable="false" :style="{ width: '450px' }" :modal="true">
                    <div class="text-center" style="color: var(--red-600)">
                        <i class="pi pi-exclamation-triangle" style="font-size: 4rem" />
                        <p class="line-height-3 mt-3">
                            {{ $t('items.lock_confirmation_text') }}
                        </p>
                    </div>
                    <template #footer>
                        <Button :label="$t('app.no')" icon="pi pi-times" @click="closeLockConfirmationModal" class="p-button-secondary" autofocus/>
                        <Button :label="$t('items.lock')" icon="pi pi-check" @click="confirmLockItem" class="p-button-danger"/>
                    </template>
                </Dialog>
                <Dialog v-model:visible="unlockConfirmationModal" :closable="false" :style="{ width: '450px' }" :modal="true">
                    <div class="text-center" style="color: var(--red-600)">
                        <i class="pi pi-exclamation-triangle" style="font-size: 4rem" />
                        <p class="line-height-3 mt-3">
                            {{ $t('items.unlock_confirmation_text') }}
                        </p>
                    </div>
                    <template #footer>
                        <Button :label="$t('app.no')" icon="pi pi-times" @click="closeUnlockConfirmationModal" class="p-button-secondary" autofocus />
                        <Button :label="$t('items.unlock')" icon="pi pi-check" @click="confirmUnlockItem" class="p-button-danger"/>
                    </template>
                </Dialog>
            </div>
        </div>
    </div>
</template>