<script>
    import { ref, reactive, computed } from 'vue'
    import { useVuelidate } from '@vuelidate/core'
    import { required, helpers, maxLength, minValue } from '@/utils/i18n-validators'
    import { getValues, getValueLabel } from '@/utils/helper'
    import CustomerService from '@/service/CustomerService'
    import DictionaryService from '@/service/DictionaryService'
    import UserInvoiceService from '@/service/UserInvoiceService'
    import UsersService from '@/service/UsersService'
    
    export default {
        emits: ['submit-form', 'back'],
        setup() {
            const userInvoiceService = new UserInvoiceService()
            const dictionaryService = new DictionaryService()
            const customerService = new CustomerService()
            const usersService = new UsersService()
            
            return {
                userInvoiceService,
                dictionaryService,
                customerService,
                usersService,
                getValueLabel,
            }
        },
        watch: {
            invoice() {
                this.toValidate = this.invoice
            },
            'invoice.sale_register_id': function() {
                this.getNumber()
            },
            'invoice.type' : function() {
                this.getSaleRegistries()
            }
        },
        data() {
            const toValidate = ref(this.invoice)
            const state = reactive({ 'invoice' : toValidate })
            const rules = computed(() => {
                const rules = {
                    invoice: {
                        type: { required },
                        sale_register_id: { required },
                        document_date: { required },
                        sell_date: { required },
                        payment_date: { required },
                        payment_type_id: { required },
                        created_user_id: { required },
                        customer_id: { required },
                        comment: {maxLengthValue: maxLength(5000)},
                        account_number: {maxLengthValue: maxLength(60)},
                        swift_number: {maxLengthValue: maxLength(60)},
                    }
                }
                
                rules.invoice.items = {}
                if(state.invoice.items != undefined && state.invoice.items.length)
                {
                    rules.invoice.items = []
                    for(var i = 0; i < state.invoice.items.length; i++)
                        rules.invoice.items.push({
                            name : {required},
                            unit_type : {required},
                            quantity : {required, minValue: minValue(1)},
                            net_amount : {required, minValue: minValue(0.01)},
                            total_net_amount : {required, minValue: minValue(0.01)},
                            vat_value : {required},
                            total_gross_amount : {required, minValue: minValue(0.01)},
                        })
                }
                return rules
            })
            
            return {
                types: getValues('invoices.types_new'),
                vatValues: getValues('invoices.vat_values'),
                gtu: getValues('invoices.gtu'),
                v: useVuelidate(rules, state),
                toValidate: toValidate,
                saleRegistries: [],
                paymentTypes: [],
                customers: [],
                users: [],
                loadingSaleRegister: false,
                loadingPaymentDictionary: false,
                loadingInvoiceNumber: false,
                loadingCustomers: false,
                loadingUsers: false,
            }
        },
        props: {
            invoice: { type: Object },
            saving: { type: Boolean },
            loading: { type: Boolean },
            errors: { type: Array },
            source: { type: String, default: 'new' },
            block: { type: Boolean, default: false },
        },
        beforeMount() {
            this.getSaleRegistries()
            this.getPaymentTypes()
            this.getCustomers()
            this.getUsers()
        },
        methods: {
            getSaleRegistries() {
                this.loadingSaleRegister = true
                this.saleRegistries = []
                if (this.invoice.type != undefined) {
                    this.userInvoiceService.saleRegisterList(100, 1, {type : this.invoice.type})
                        .then(
                            (response) => {
                                if (response.data.data.length) {
                                    response.data.data.forEach((i) => {
                                        this.saleRegistries.push({"id" : i.id, "name" : i.name })
                                        this.loadingSaleRegister = false
                                    })
                                    if(this.source == "new" || this.source == "proforma")
                                        this.invoice.sale_register_id = response.data.data[0].id
                                }
                            },
                            (errors) => {
                                this.$toast.add({ severity: 'error', summary: this.$t('app.error'), detail: errors.response.data.message, life: 3000 });
                            }
                        )
                }
            },
            
            getCustomers() {
                this.loadingCustomers = true
                this.customers = []
                this.customerService.list(9999, 1)
                    .then(
                        (response) => {
                            if (response.data.data.length) {
                                response.data.data.forEach((i) => {
                                    this.customers.push({
                                        "id" : i.id,
                                        "name" : i.name,
                                        "type" : i.type,
                                        "nip" : i.nip,
                                    })
                                    this.loadingCustomers = false
                                })
                            }
                        },
                        (errors) => {
                            this.$toast.add({ severity: 'error', summary: this.$t('app.error'), detail: errors.response.data.message, life: 3000 });
                        }
                    )
            },
            
            getUsers() {
                this.loadingUsers = true
                this.users = []
                this.usersService.list(9999, 1)
                    .then(
                        (response) => {
                            if (response.data.data.length) {
                                response.data.data.forEach((i) => {
                                    this.users.push({
                                        "id" : i.id,
                                        "name" : i.firstname + " " + i.lastname,
                                    })
                                    this.loadingUsers = false
                                })
                            }
                        },
                        (errors) => {
                            this.$toast.add({ severity: 'error', summary: this.$t('app.error'), detail: errors.response.data.message, life: 3000 });
                        }
                    )
            },
            
            getPaymentTypes() {
                this.loadingPaymentDictionary = true
                this.paymentTypes = []
                this.dictionaryService.listByType("payment_types", 100, 1)
                    .then(
                        (response) => {
                            if (response.data.data.length) {
                                response.data.data.forEach((i) => {
                                    this.paymentTypes.push({"id" : i.id, "name" : i.name })
                                    this.loadingPaymentDictionary = false
                                })
                                
                                if(this.source == "new")
                                    this.invoice.payment_type_id = response.data.data[0].id
                            }
                        },
                        (errors) => {
                            this.$toast.add({ severity: 'error', summary: this.$t('app.error'), detail: errors.response.data.message, life: 3000 });
                        }
                    )
            },
            
            getNumber() {
                if (this.source == "new" || this.source == "proforma") {
                    this.loadingInvoiceNumber = true
                    this.userInvoiceService.getNumber(this.invoice.sale_register_id)
                        .then(
                            (response) => {
                                this.loadingInvoiceNumber = false
                                this.invoice.full_number = response.data
                            },
                            (errors) => {
                                this.$toast.add({ severity: 'error', summary: this.$t('app.error'), detail: errors.response.data.message, life: 3000 });
                            }
                        )
                }
            },
            
            async submitForm() {
                const result = await this.v.$validate()
                if (result)
                    this.$emit('submit-form')
                else
                    this.$toast.add({ severity: 'error', summary: this.$t('app.form_error_title'), detail: this.$t('app.form_error_message'), life: 3000 });
            },
            
            back() {
                this.$emit('back')
            },
            
            addNewItem() {
                this.invoice.items.push({
                    quantity: 1,
                    net_amount: 0,
                    total_net_amount: 0,
                    vat_amount: 0,
                    total_gross_amount: 0,
                    vat_value: "23"
                });
            },
            
            updateQuantity(data) {
                data.total_net_amount = data.quantity * data.net_amount;
                this.calculateVatFromNetValue(data);
            },
            
            updateNetAmount(data) {
                data.total_net_amount = data.quantity * data.net_amount;
                this.calculateVatFromNetValue(data);
            },
            
            updateTotalNetAmount(data) {
                data.net_amount = data.total_net_amount / data.quantity;
                this.calculateVatFromNetValue(data);
            },
            
            updateGrossAmount(data) {
                this.calculateVatFromGrossValue(data)
            },
            
            updateVatValue(data) {
                this.calculateVatFromNetValue(data);
            },
            
            calculateVatFromNetValue(data) {
                let vatValue = 0;
                if (!isNaN(data.vat_value))
                    vatValue = parseInt(data.vat_value);
                    
                data.total_gross_amount = data.total_net_amount * ((100+vatValue)/100)
                data.vat_amount = data.total_gross_amount - data.total_net_amount
                this.calculateTotal()
            },
            
            calculateVatFromGrossValue(data) {
                let vatValue = 0;
                if (!isNaN(data.vat_value))
                    vatValue = parseInt(data.vat_value);
                    
                data.total_net_amount = (data.total_gross_amount * 100) / (100 + data.vat_value)
                data.net_amount = data.total_net_amount / data.quantity
                data.vat_amount = data.total_gross_amount - data.total_net_amount
                this.calculateTotal()
            },
            
            removeItem(index) {
                this.invoice.items.splice(index, 1);
                
                if (!this.invoice.items.length)
                    this.addNewItem()
                
                this.calculateTotal();
            },
            
            calculateTotal() {
                this.invoice.net_amount = 0;
                this.invoice.gross_amount = 0;
                
                this.invoice.items.forEach((row) => {
                    this.invoice.net_amount += row.total_net_amount;
                    this.invoice.gross_amount += row.total_gross_amount;
                });
            }
        }
    };
</script>

<template>
    <form v-on:submit.prevent="submitForm" class="sticky-footer-form">
        <Message severity="error" :closable="false" v-if="errors.length" class="mb-5">
            <ul class="list-unstyled">
                <li v-for="error of errors">
                    {{ error }}
                </li>
            </ul>
        </Message>
        <div class="mb-4">
            <div class="p-fluid">
                <div class="formgrid grid">
                    <div class="field col-12 md:col-6 mb-4">
                        <label for="number" v-required class="block text-900 font-medium mb-2">{{ $t('customer_invoices.number') }}</label>
                        <InputText id="number" type="text" :placeholder="$t('customer_invoices.number')" class="w-full" v-model="invoice.full_number" :disabled="true"/>
                    </div>
                    
                    <div class="field col-12 md:col-3 mb-4">
                        <label for="type" v-required class="block text-900 font-medium mb-2">{{ $t('customer_invoices.document_type') }}</label>
                        <Dropdown id="type" v-model="invoice.type" :options="types" optionLabel="name" :class="{'p-invalid' : v.invoice.type.$error}" optionValue="id" :placeholder="$t('customer_invoices.sale_register')" class="w-full" :disabled="saving || loading || source == 'edit' || source == 'proforma' || block"/>
                        <div v-if="v.invoice.type.$error">
                            <small class="p-error">{{ v.invoice.type.$errors[0].$message }}</small>
                        </div>
                    </div>
                    
                    <div class="field col-12 md:col-3 mb-4">
                        <label for="sale_register_id" v-required class="block text-900 font-medium mb-2">{{ $t('customer_invoices.sale_register') }}</label>
                        <Dropdown id="sale_register_id" v-model="invoice.sale_register_id" :loading="loadingSaleRegister" :options="saleRegistries" :class="{'p-invalid' : v.invoice.sale_register_id.$error}" optionLabel="name" optionValue="id" :placeholder="$t('customer_invoices.sale_register')" class="w-full" :disabled="saving || loading || source == 'edit' || block"/>
                        <div v-if="v.invoice.sale_register_id.$error">
                            <small class="p-error">{{ v.invoice.sale_register_id.$errors[0].$message }}</small>
                        </div>
                    </div>
                    
                    <div class="field col-12 md:col-6 mb-4">
                        <label for="customer_id" v-required class="block text-900 font-medium mb-2">{{ $t('customer_invoices.customer') }}</label>
                        <Dropdown id="customer_id" v-model="invoice.customer_id" filter :filterFields="['name','nip']" :loading="loadingCustomers" :options="customers" :class="{'p-invalid' : v.invoice.customer_id.$error}" optionLabel="name" optionValue="id" :placeholder="$t('customer_invoices.customer')" class="w-full" :disabled="saving || loading || block">
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
                        <div v-if="v.invoice.customer_id.$error">
                            <small class="p-error">{{ v.invoice.customer_id.$errors[0].$message }}</small>
                        </div>
                    </div>
                    
                    <div class="field col-12 md:col-6 mb-4">
                        <label for="created_user_id" v-required class="block text-900 font-medium mb-2">{{ $t('customer_invoices.issued') }}</label>
                        <Dropdown id="created_user_id" v-model="invoice.created_user_id" filter :loading="loadingUsers" :options="users" :class="{'p-invalid' : v.invoice.created_user_id.$error}" optionLabel="name" optionValue="id" :placeholder="$t('customer_invoices.issued')" class="w-full" :disabled="saving || loading || block"/>
                        <div v-if="v.invoice.created_user_id.$error">
                            <small class="p-error">{{ v.invoice.created_user_id.$errors[0].$message }}</small>
                        </div>
                    </div>
                    
                    <div class="field col-12 sm:col-6 xl:col-3 mb-4">
                        <label for="documentDate" v-required class="block text-900 font-medium mb-2">{{ $t('customer_invoices.document_date') }}</label>
                        <Calendar id="documentDate" v-model="invoice.document_date" :placeholder="$t('customer_invoices.document_date')" :class="{'p-invalid' : v.invoice.document_date.$error}" showIcon :disabled="saving || loading || block"/>
                        <div v-if="v.invoice.document_date.$error">
                            <small class="p-error">{{ v.invoice.document_date.$errors[0].$message }}</small>
                        </div>
                    </div>
                    
                    <div class="field col-12 sm:col-6 xl:col-3 mb-4">
                        <label for="sellDate" v-required class="block text-900 font-medium mb-2">{{ $t('customer_invoices.sell_date') }}</label>
                        <Calendar id="sellDate" v-model="invoice.sell_date" :placeholder="$t('customer_invoices.sell_date')" :class="{'p-invalid' : v.invoice.sell_date.$error}" showIcon :disabled="saving || loading || block"/>
                        <div v-if="v.invoice.sell_date.$error">
                            <small class="p-error">{{ v.invoice.sell_date.$errors[0].$message }}</small>
                        </div>
                    </div>
                    
                    <div class="field col-12 sm:col-6 xl:col-3 mb-4">
                        <label for="paymentDate" v-required class="block text-900 font-medium mb-2">{{ $t('customer_invoices.payment_date') }}</label>
                        <Calendar id="paymentDate" v-model="invoice.payment_date" :placeholder="$t('customer_invoices.payment_date')" :class="{'p-invalid' : v.invoice.payment_date.$error}" showIcon :disabled="saving || loading || block"/>
                        <div v-if="v.invoice.payment_date.$error">
                            <small class="p-error">{{ v.invoice.payment_date.$errors[0].$message }}</small>
                        </div>
                    </div>
                    
                    <div class="field col-12 sm:col-6 xl:col-3 mb-4">
                        <label for="payment_type_id" v-required class="block text-900 font-medium mb-2">{{ $t('customer_invoices.payment_type') }}</label>
                        <Dropdown id="payment_type_id" v-model="invoice.payment_type_id" :loading="loadingPaymentDictionary" :options="paymentTypes" :class="{'p-invalid' : v.invoice.payment_type_id.$error}" optionLabel="name" optionValue="id" :placeholder="$t('customer_invoices.payment_type')" class="w-full" :disabled="saving || loading || block"/>
                        <div v-if="v.invoice.payment_type_id.$error">
                            <small class="p-error">{{ v.invoice.payment_type_id.$errors[0].$message }}</small>
                        </div>
                    </div>
                    
                    <div class="field col-12 mb-4">
                        <label for="comment" v-required class="block text-900 font-medium mb-2">{{ $t('customer_invoices.comment') }}</label>
                        <Textarea id="comment" type="text" :placeholder="$t('customer_invoices.comment')" rows="3" class="w-full" :class="{'p-invalid' : v.invoice.comment.$error}" v-model="invoice.comment" :disabled="loading || saving || block"/>
                        <div v-if="v.invoice.comment.$error">
                            <small class="p-error">{{ v.invoice.comment.$errors[0].$message }}</small>
                        </div>
                    </div>
                </div>
                
                <div class="flex justify-content-between align-items-center mb-4">
                    <h5 class="text-gray-600 font-medium m-0">{{ $t('customer_invoices.items') }}</h5>
                    <Button severity="secondary" class="w-auto" :disabled="block" size="small" :label="$t('customer_invoices.add_invoice_item')" @click="addNewItem"/>
                </div>
                
                <DataTable :value="invoice.items" size="small" stripedRows class="p-datatable-gridlines" :rowHover="true" :loading="loading">
                    <Column field="delete">
                        <template #body="{ data, index }">
                            <Button icon="pi pi-trash" :disabled="block" v-tooltip.bottom="$t('app.remove')" class="p-button-danger p-2" style="width: auto" @click="removeItem(index)"/>
                        </template>
                    </Column>
                    <Column :header="$t('customer_invoices.item_name')" class="text-left" style="min-width: 250px; width: 250px">
                        <template #body="{ data, index }">
                            <InputText type="text" class="w-full" v-model="data.name" :class="{'p-invalid' : v.invoice.items[index].name.$error}" :disabled="saving || loading || block"/>
                        </template>
                    </Column>
                    
                    <Column :header="$t('customer_invoices.gtu')" class="text-left">
                        <template #body="{ data }">
                            <Dropdown v-model="data.gtu" :options="gtu" optionLabel="id" optionValue="id" :disabled="saving || loading || block">
                                <template #option="slotProps">
                                    <div class="">
                                        <div>
                                            {{ slotProps.option.id }}
                                        </div>
                                        <small class="font-italic text-gray-500">
                                            {{ slotProps.option.name }}
                                        </small>
                                    </div>
                                </template>
                            </Dropdown>
                        </template>
                    </Column>
                    
                    <Column :header="$t('customer_invoices.unit_type_short')" class="text-center" style="min-width: 75px; width: 75px">
                        <template #body="{ data, index }">
                            <InputText type="text" class="w-full" v-model="data.unit_type" :class="{'p-invalid' : v.invoice.items[index].unit_type.$error}" :disabled="saving || loading || block"/>
                        </template>
                    </Column>
                    <Column :header="$t('customer_invoices.quantity')" class="text-center" style="min-width: 75px; width: 75px">
                        <template #body="{ data, index }">
                            <InputNumber :useGrouping="false" locale="pl-PL" :min="1" :max="10000" v-model="data.quantity" class="text-right" :class="{'p-invalid' : v.invoice.items[index].quantity.$error}" @update:modelValue="updateQuantity(data)" :disabled="saving || loading || block"/>
                        </template>
                    </Column>
                    <Column :header="$t('customer_invoices.net_amount')" class="text-right" style="min-width: 120px; width: 120px">
                        <template #body="{ data, index }">
                            <InputNumber :useGrouping="false" locale="pl-PL" :minFractionDigits="2" :maxFractionDigits="2" v-model="data.net_amount" class="text-right" :class="{'p-invalid' : v.invoice.items[index].net_amount.$error}" @update:modelValue="updateNetAmount(data)" :disabled="saving || loading || block"/>
                        </template>
                    </Column>
                    <Column :header="$t('customer_invoices.total_net_amount')" class="text-right" style="min-width: 120px; width: 120px">
                        <template #body="{ data, index }">
                            <InputNumber :useGrouping="false" locale="pl-PL" :minFractionDigits="2" :maxFractionDigits="2" v-model="data.total_net_amount" class="text-right" :class="{'p-invalid' : v.invoice.items[index].total_net_amount.$error}" @update:modelValue="updateTotalNetAmount(data)" :disabled="saving || loading || block"/>
                        </template>
                    </Column>
                    <Column :header="$t('customer_invoices.vat_rate')" class="text-center">
                        <template #body="{ data, index }">
                            <Dropdown v-model="data.vat_value" :options="vatValues" optionLabel="name" optionValue="id" :class="{'p-invalid' : v.invoice.items[index].vat_value.$error}" @update:modelValue="updateVatValue(data)" :disabled="saving || loading || block"/>
                        </template>
                    </Column>
                    <Column :header="$t('customer_invoices.vat_amount')" class="text-right" style="min-width: 120px; width: 120px">
                        <template #body="{ data }">
                            <InputNumber :useGrouping="false" locale="pl-PL" :minFractionDigits="2" :maxFractionDigits="2" v-model="data.vat_amount" class="text-right" :disabled="true"/>
                        </template>
                    </Column>
                    <Column :header="$t('customer_invoices.total_gross_amount')" class="text-right" style="min-width: 120px; width: 120px">
                        <template #body="{ data, index }">
                            <InputNumber :useGrouping="false" locale="pl-PL" :minFractionDigits="2" :maxFractionDigits="2" v-model="data.total_gross_amount" class="text-right" :class="{'p-invalid' : v.invoice.items[index].total_gross_amount.$error}" @update:modelValue="updateGrossAmount(data)" :disabled="saving || loading || block"/>
                        </template>
                    </Column>
                    <template #empty>
                        {{ $t('customer_invoices.empty_item_list') }}
                    </template>
                </DataTable>
                <div class="text-right mt-3 mb-3">
                    <div>
                        <span class="text-sm uppercase">{{ $t('customer_invoices.total') }}:</span>
                        <span class="text-4xl">{{ numeralFormat(invoice.gross_amount, '0.00') }}</span> {{ invoice.currency }}
                    </div>
                    <div class="text-sm">
                        ({{ $t('customer_invoices.netto') }}: {{ numeralFormat(invoice.net_amount, '0.00') }} {{ invoice.currency }})
                    </div>
                </div>
            </div>
        </div>
        
        <div class="form-footer">
            <div v-if="source == 'new'">
                <div class="flex justify-content-between align-items-center">
                    <Button type="button" :label="$t('app.cancel')" iconPos="left" icon="pi pi-angle-left" @click="back" class="p-button-secondary w-auto text-center"></Button>
                    <Button type="submit" :label="$t('customer_invoices.issue_invoice')" :loading="saving" iconPos="right" icon="pi pi-save" class="w-auto text-center"></Button>
                </div>
            </div>
            <div v-else>
                <div v-if="loading">
                    <ProgressSpinner style="width: 25px; height: 25px"/>
                </div>
                
                <div class="flex justify-content-between align-items-center">
                    <Button type="button" :label="$t('app.cancel')" iconPos="left" icon="pi pi-angle-left" @click="back" class="p-button-secondary w-auto text-center"></Button>
                    <Button type="submit" :label="$t('app.save')" v-if="!loading" :loading="saving" :disabled="block" iconPos="right" icon="pi pi-save" class="w-auto text-center"></Button>
                </div>
            </div>
        </div>
    </form>
</template>