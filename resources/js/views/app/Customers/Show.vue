<script>
    import { getValueLabel, getResponseErrors, hasAccess, setMetaTitle, getItemRowColor } from '@/utils/helper'
    import { appStore } from '@/store.js'
    
    import Address from '@/views/app/_partials/Address.vue'
    import Header from '@/views/app/_partials/Header.vue'
    import CustomerService from '@/service/CustomerService'
    import ItemService from '@/service/ItemService'
    import UserInvoiceService from '@/service/UserInvoiceService'
    
    export default {
        components: { Address, Header },
        setup() {
            setMetaTitle('meta.title.customers_show')
            
            const customerService = new CustomerService()
            const itemService = new ItemService()
            const userInvoiceService = new UserInvoiceService()
            
            return {
                customerService,
                itemService,
                userInvoiceService,
                hasAccess,
                getValueLabel,
                getItemRowColor
            }
        },
        data() {
            return {
                errors: [],
                customer: {},
                items: [],
                invoices: [],
                displayConfirmationItem: false,
                deleteItemId: null,
                meta: {
                    items: {
                        list: {
                            first: 0,
                            size: this.rowsPerPage,
                        },
                        totalRecords: null,
                        totalPages: null,
                        loading: true
                    },
                    invoices: {
                        list: {
                            first: 0,
                            size: this.rowsPerPage,
                        },
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
            
            this.customerService.get(this.$route.params.customerId)
                .then(
                    (response) => {
                        this.customer = response.data
                        this.loading = false
                        
                        this.getItemsList()
                        this.getInvoicesList()
                    },
                    (errors) => {
                        if(errors.response.status == 404)
                        {
                            appStore().setError404(errors.response.data.message);
                            this.$router.push({name: 'objectnotfound'})
                        }
                        else
                            this.$toast.add({ severity: 'error', summary: this.$t('app.error'), detail: errors.response.data.message, life: 3000 });
                    }
                );
        },
        methods: {
            addItem() {
                this.$router.push({name: 'item_new_customer', params: { customerId : this.$route.params.customerId }})
            },
            
            getItemsList() {
                this.meta.items.loading = true
                const search = {
                    customer_id : this.$route.params.customerId,
                    mode: 'all'
                };
                this.itemService.list(this.meta.items.list, search)
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
                this.meta.items.list.first = event["first"];
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
                        (errors) => {
                            this.$toast.add({ severity: 'error', summary: this.$t('app.error'), detail: errors.response.data.message, life: 3000 });
                        }
                    )
                
                this.displayConfirmationItem = false
                this.deleteItemId = null
            },
            
            closeConfirmationItem() {
                this.displayConfirmationItem = false
            },
            
            rowItemsClick(event) {
                this.$router.push({name: 'item_show', params: { itemId : event.data.id }})
            },
            
            getInvoicesList() {
                this.meta.invoices.loading = true
                const search = {
                    customer_id : this.$route.params.customerId,
                };
                this.userInvoiceService.invoices(this.meta.invoices.list, search)
                    .then(
                        (response) => {
                            this.invoices = response.data.data
                            this.meta.invoices.totalRecords = response.data.total_rows
                            this.meta.invoices.totalPages = response.data.total_pages
                            this.meta.invoices.loading = false
                        },
                        (errors) => {
                        }
                    )
            },
            
            changeInvoicesPage(event) {
                this.meta.invoices.list.first = event["first"];
                this.getInvoicesList()
            },
            
            downloadPDF(invoiceId) {
                this.userInvoiceService.getPDF(invoiceId)
                    .then(
                        (response) => {
                            const contentDisposition = response.headers['content-disposition'];
                            let fileName = 'file.pdf';
                            if (contentDisposition) {
                                const fileNameMatch = contentDisposition.match(/filename="(.+)"/);
                                if (fileNameMatch.length === 2)
                                    fileName = fileNameMatch[1];
                            }
                            
                            var fileURL = window.URL.createObjectURL(new Blob([response.data]));
                            var fileLink = document.createElement('a');
                            fileLink.href = fileURL;
                            fileLink.setAttribute('download', fileName);
                            document.body.appendChild(fileLink);
                            fileLink.click();
                        },
                        (errors) => {
                            
                        }
                    )
            },
        },
    }
</script>

<template>
    <Breadcrumb :model="meta.breadcrumbItems"/>
    
    <div class="grid mt-1">
        <div class="col col-12">
            <div class="card pt-4">
                <Help show="tenant_customer" mark="tenant_customer:customer" class="text-right mb-3"/>
                <Header :object="customer" type="customer" :showEditButton="true"/>
                
                <div class="mt-3">
                    <p class="m-0 mt-2" v-if="customer.type == 'person'">
                        <span class="font-medium">{{ $t('customers.pesel') }}: </span> <i v-if="customer.pesel">{{ customer.pesel }}</i><i v-else>-</i>
                    </p>
                    
                    <p class="m-0 mt-2" v-if="customer.type == 'firm'">
                        <span class="font-medium">{{ $t('customers.nip') }}: </span> <i v-if="customer.nip">{{ customer.nip }}</i><i v-else>-</i>
                    </p>
                </div>
                
                <div class="grid mt-3">
                    <div class="col-12 md:col-6">
                        <div class="font-normal mb-2 text-lg">{{ $t('customers.phone_list') }}:</div>
                        <div v-if="customer.contacts != undefined && customer.contacts.phone.length">
                            <ul class="list-unstyled mt-2">
                                <li v-for="phone in customer.contacts.phone" class="mb-1">
                                    <a href="tel:{{ phone.val }}">{{ phone.val }}</a>
                                </li>
                            </ul>
                        </div>
                        <div v-else><i>{{ $t('customers.empty_phone_list') }}</i></div>
                    </div>
                    <div class="col-12 md:col-6">
                        <div class="font-normal mb-2 text-lg">{{ $t('customers.email_list') }}:</div>
                        <div v-if="customer.contacts != undefined && customer.contacts.email.length">
                            <ul class="list-unstyled mt-2">
                                <li v-for="email in customer.contacts.email" class="mb-1">
                                    <a href="mailto:{{ email.val }}">{{ email.val }}</a>
                                </li>
                            </ul>
                        </div>
                        <div v-else><i>{{ $t('customers.empty_email_list') }}</i></div>
                    </div>
                </div>
                
                <p class="m-0 mt-3" v-if="customer.comments">
                    <span class="font-medium">{{ $t('customers.comments') }}: </span>
                    <br/>
                    <i class="text-sm">{{ customer.comments}}</i>
                </p>
            </div>
            
            
            <div class="card mt-5">
                <TabView class="mt-5">
                    <TabPanel :header="$t('customers.estates')">
                        <div class="flex justify-content-between align-items-center mb-3 text-color font-medium">
                            <h5 class="inline-flex mb-0 mt-2 text-color font-medium">
                                {{ $t('customers.items_list') }}
                            </h5>
                            <div v-if="hasAccess('item:create')">
                                <Button icon="pi pi-plus" @click="addItem" v-tooltip.left="$t('customers.add_new_item')"></Button>
                            </div>
                        </div>
                        <DataTable :rowClass="({ mode }) => getItemRowColor(mode)" :value="items" stripedRows class="p-datatable-gridlines clickable" :totalRecords="meta.items.totalRecords" :rowHover="true" :lazy="true" :paginator="true" :pageCount="meta.items.totalPages" :rows="meta.items.list.size" :first="meta.items.list.first" @page="changeItemsPage" :loading="meta.items.loading" @row-click="rowItemsClick($event)">
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
                            <Column field="delete" v-if="hasAccess('item:delete')" style="min-width: 60px; width: 60px" class="text-center">
                                <template #body="{ data }">
                                    <Button :disabled="!data.can_delete" icon="pi pi-trash" v-tooltip.bottom="$t('app.remove')" class="p-button-danger p-2" style="width: auto" @click="openConfirmationItem(data.id)"/>
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
                    </TabPanel>
                    <TabPanel :header="$t('customers.invoices')">
                        <div class="flex justify-content-between align-items-center mb-3 text-color font-medium">
                            <h5 class="inline-flex mb-0 mt-2 text-color font-medium">
                                {{ $t('customers.invoices_list') }}
                            </h5>
                        </div>
                        <DataTable :value="invoices" stripedRows class="p-datatable-gridlines" :totalRecords="meta.invoices.totalRecords" :rowHover="true" :lazy="true" :paginator="true" :pageCount="meta.invoices.totalPages" :rows="meta.invoices.list.size" :first="meta.invoices.list.first" @page="changeInvoicesPage" :loading="meta.invoices.loading">
                            <Column class="text-left" style="min-width: 60px; width: 60px;">
                                <template #body="{ data }">
                                    <Button icon="pi pi-download" v-tooltip.bottom="$t('customer_invoices.download_invoice')" class="p-button-info p-2" style="width: auto" @click="downloadPDF(data.id)"/>
                                </template>
                            </Column>
                            <Column :header="$t('customer_invoices.number')" field="full_number" style="min-width: 300px;">
                                <template #body="{ data }">
                                    <div class="font-medium mb-1 text-lg">{{ data.customer_name }}</div>
                                    <div class="text-sm text-gray-500">{{ data.full_number }}</div>
                                    <template v-if="data.proforma_number">
                                        <div class="mt-2 text-sm">
                                            {{ $t("customer_invoices.from_proforma") }}:
                                            <router-link :to="{name: 'customer_invoices_edit', params: { customerInvoiceId : data.proforma_id }}" v-if="hasAccess('dictionary:update')">
                                                 {{ data.proforma_number }}
                                            </router-link>
                                        </div>
                                    </template>
                                </template>
                            </Column>
                            <Column :header="$t('customer_invoices.document_date')" field="document_date" class="text-center"></Column>
                            <Column :header="$t('customer_invoices.net_amount')" field="net_amount" class="text-right">
                                <template #body="{ data }">
                                    {{ numeralFormat(data.net_amount, '0.00') }}
                                </template>
                            </Column>
                            <Column :header="$t('customer_invoices.gross_amount')" field="gross_amount" class="text-right">
                                <template #body="{ data }">
                                    {{ numeralFormat(data.gross_amount, '0.00') }}
                                </template>
                            </Column>
                            <Column :header="$t('customer_invoices.sale_register')">
                                <template #body="{ data }">
                                    {{ data.sale_register.name }}
                                </template>
                            </Column>
                            <template #empty>
                                {{ $t('customer_invoices.empty_list') }}
                            </template>
                        </DataTable>
                    </TabPanel>
                </TabView>
            </div>
        </div>
    </div>
</template>