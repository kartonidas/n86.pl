<script>
    import { useRoute } from 'vue-router'
    import { getValueLabel, getResponseErrors, hasAccess, setMetaTitle } from '@/utils/helper'
    import { appStore } from '@/store.js'
    
    import Address from '@/views/app/_partials/Address.vue'
    import CustomerService from '@/service/CustomerService'
    import ItemService from '@/service/ItemService'
    
    export default {
        components: { Address },
        setup() {
            setMetaTitle('meta.title.customers_show')
            
            const route = useRoute()
            const customerService = new CustomerService()
            const itemService = new ItemService()
            
            return {
                customerService,
                itemService,
                route,
                hasAccess,
                getValueLabel
            }
        },
        data() {
            return {
                errors: [],
                customer: {},
                items: [],
                displayConfirmationItem: false,
                deleteItemId: null,
                meta: {
                    items: {
                        currentPage: 1,
                        perPage: this.rowsPerPage,
                        totalRecords: null,
                        totalPages: null,
                        loading: true
                    },
                    breadcrumbItems: [
                        {'label' : this.$t('menu.estates'), disabled : true },
                        {'label' : this.$t('menu.customer_list'), route : { name : 'customers'} },
                        {'label' : this.$t('customers.card'), disabled : true },
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
            
            this.customerService.get(this.route.params.customerId)
                .then(
                    (response) => {
                        this.customer = response.data
                        this.loading = false
                        
                        this.getItemsList()
                    },
                    (response) => {
                        this.$toast.add({ severity: 'error', summary: this.$t('app.error'), detail: response.response.data.message, life: 3000 });
                    }
                );
        },
        methods: {
            editCustomer() {
                this.$router.push({name: 'customer_edit', params: { customerId : this.route.params.customerId }})
            },
            
            addItem() {
                this.$router.push({name: 'item_new_customer', params: { customerId : this.route.params.customerId }})
            },
            
            getItemsList() {
                const search = {
                    customer_id : this.route.params.customerId
                };
                this.itemService.list(this.meta.items.perPage, this.meta.items.currentPage, search)
                    .then(
                        (response) => {
                            this.items = response.data.data
                            this.meta.items.totalRecords = response.data.total_rows
                            this.meta.items.totalPages = response.data.total_pages
                            this.meta.items.loading = false
                        },
                        (errors) => {
                        }
                    )
            },
            
            changeItemsPage(event) {
                this.meta.items.currentPage = event["page"] + 1;
                this.getItemsList()
            },
            
            openConfirmationItem(id) {
                this.displayConfirmationItem = true
                this.deleteItemId = id
            },
            
            confirmDeleteItem() {
                this.itemService.remove(this.deleteItemId)
                    .then(
                        (response) => {
                            this.getItemsList()
                            this.$toast.add({ severity: 'success', summary: this.$t('app.success'), detail: this.$t('items.deleted'), life: 3000 });
                        },
                        (response) => {
                            this.$toast.add({ severity: 'error', summary: this.$t('app.error'), detail: response.response.data.message, life: 3000 });
                        }
                    )
                
                this.displayConfirmationItem = false
                this.deleteItemId = null
            },
            
            closeConfirmationItem() {
                this.displayConfirmationItem = false
            },
            
            rowItemsClick(event) {
                //this.showItem(event.data.id)
            }
        },
    }
</script>

<template>
    <Breadcrumb :model="meta.breadcrumbItems"/>
    
    <div class="grid mt-1">
        <div class="col col-12">
             <Card class="mb-3">
                <template #title>
                    <div class="flex justify-content-between align-items-center mb-3 text-color font-medium">
                        {{ customer.name }}
                        <div v-if="hasAccess('customer:update')">
                            <Button icon="pi pi-pencil" @click="editCustomer" v-tooltip.left="$t('app.edit')"></Button>
                        </div>
                    </div>
                </template>
                
                <template #content pt="item">
                    <div class="grid formgrid">
                        <div class="col-12 md:col-6">
                            <p class="m-0 mt-2">
                                <span class="font-medium">{{ $t('customers.account_type') }}: </span> <i>{{ getValueLabel('customer_types', customer.type) }}</i>
                            </p>
                            <p class="m-0 mt-2">
                                <span class="font-medium">{{ $t('customers.address') }}: </span> <i><Address :object="customer"/></i>
                            </p>
                            
                            <p class="m-0 mt-2" v-if="customer.type == 'person'">
                                <span class="font-medium">{{ $t('customers.pesel') }}: </span> <i v-if="customer.pesel">{{ customer.pesel }}</i><i v-else>-</i>
                            </p>
                            
                            <p class="m-0 mt-2" v-if="customer.type == 'firm'">
                                <span class="font-medium">{{ $t('customers.nip') }}: </span> <i v-if="customer.nip">{{ customer.nip }}</i><i v-else>-</i>
                            </p>
                        </div>
                        <div class="col-12 md:col-6">
                            <div class="font-medium mt-2">{{ $t('customers.phone_list') }}:</div>
                            <div v-if="customer.contacts != undefined && customer.contacts.phone.length">
                                <ul class="list-unstyled mt-2">
                                    <li v-for="phone in customer.contacts.phone" class="mb-1">
                                        <a href="tel:{{ phone.val }}">{{ phone.val }}</a>
                                    </li>
                                </ul>
                            </div>
                            <div v-else><i>-</i></div>
                            
                            <div class="font-medium mt-3">{{ $t('customers.email_list') }}:</div>
                            <div v-if="customer.contacts != undefined && customer.contacts.email.length">
                                <ul class="list-unstyled mt-2">
                                    <li v-for="email in customer.contacts.email" class="mb-1">
                                        <a href="mailto:{{ email.val }}">{{ email.val }}</a>
                                    </li>
                                </ul>
                            </div>
                            <div v-else><i>-</i></div>
                        </div>
                    </div>
                    <p class="m-0 mt-2" v-if="customer.comments">
                        <span class="font-medium">{{ $t('customers.comments') }}: </span>
                        <br/>
                        <i class="text-sm">{{ customer.comments}}</i>
                    </p>
                </template>
            </Card>
            
            <Card class="mb-3 mt-5">
                <template #title>
                    <div class="flex justify-content-between align-items-center mb-3 text-color font-medium">
                        {{ $t('customers.items_list') }}
                        <div v-if="hasAccess('item:create')">
                            <Button icon="pi pi-plus" @click="addItem" v-tooltip.left="$t('customers.add_new_item')"></Button>
                        </div>
                    </div>
                </template>
                <template #content pt="item">
                    <DataTable :value="items" class="p-datatable-gridlines" :totalRecords="meta.items.totalRecords" :rowHover="true" :lazy="true" :paginator="true" :pageCount="meta.items.totalPages" :rows="meta.items.perPage" @page="changeItemsPage" :loading="meta.items.loading" @row-click="rowItemsClick($event)">
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
                                <Button icon="pi pi-trash" v-tooltip.bottom="$t('app.remove')" class="p-button-danger p-2" style="width: auto" @click="openConfirmationItem(data.id)"/>
                            </template>
                        </Column>
                        <template #empty>
                            {{ $t('items.empty_list') }}
                        </template>
                    </DataTable>
                    <Dialog :header="$t('app.confirmation')" v-model:visible="displayConfirmationItem" :style="{ width: '450px' }" :modal="true">
                        <div class="flex align-items-center justify-content-center">
                            <i class="pi pi-exclamation-triangle mr-3" style="font-size: 2rem" />
                            <span>{{ $t('app.remove_object_confirmation') }}</span>
                        </div>
                        <template #footer>
                            <Button :label="$t('app.no')" icon="pi pi-times" @click="closeConfirmationItem" class="p-button-text" />
                            <Button :label="$t('app.yes')" icon="pi pi-check" @click="confirmDeleteItem" class="p-button-danger" autofocus />
                        </template>
                    </Dialog>
                </template>
            </Card>
        </div>
    </div>
</template>