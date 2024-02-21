<script>
    import { ref, reactive, computed } from 'vue'
    import { useVuelidate } from '@vuelidate/core'
    import { required, helpers, maxLength, minValue } from '@/utils/i18n-validators'
    import { nip } from '@/utils/validators'
    import { getValues, getValueLabel } from '@/utils/helper'
    import Countries from '@/data/countries.json'
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
                        comment: {maxLengthValue: maxLength(5000)},
                        account_number: {maxLengthValue: maxLength(60)},
                        swift_number: {maxLengthValue: maxLength(60)},
                        customer_type: { required },
                        customer_name: { required, maxLengthValue: maxLength(80) },
                        customer_street: { required, maxLengthValue: maxLength(80) },
                        customer_house_no: { maxLengthValue: maxLength(20) },
                        customer_apartment_no: { maxLengthValue: maxLength(20) },
                        customer_city: { required, maxLengthValue: maxLength(120) },
                        customer_zip: { required, maxLengthValue: maxLength(10) },
                    }
                }
                
                rules.invoice.customer_nip = state.invoice.customer_type == "firm" ? { nip: helpers.withMessage(this.$t('validations.nip'), nip) } : {};
                
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
                countries: Countries[this.$i18n.locale],
                customerTypes: getValues('customer_types'),
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
        computed: {
            labelCustomerTypeName: {
                get: function () {
                    return this.invoice.customer_type == 'person' ? this.$t('customer_invoices.firstname_lastname') : this.$t('customer_invoices.firm_name')
                },
            }
        },
        methods: {
            getSaleRegistries() {
                this.loadingSaleRegister = true
                this.saleRegistries = []
                if (this.invoice.type != undefined) {
                    this.userInvoiceService.saleRegisterList({size: 100, first: 0}, {type : this.invoice.type})
                        .then(
                            (response) => {
                                this.loadingSaleRegister = false
                                if(this.source == "new" || this.source == "proforma")
                                    this.invoice.sale_register_id = null
                                if (response.data.data.length) {
                                    response.data.data.forEach((i) => {
                                        this.saleRegistries.push({"id" : i.id, "name" : i.name })
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
                this.customerService.list({size: 9999, first: 0})
                    .then(
                        (response) => {
                            this.loadingCustomers = false
                            if (response.data.data.length) {
                                response.data.data.forEach((i) => {
                                    this.customers.push({
                                        "id" : i.id,
                                        "name" : i.name,
                                        "nip" : i.nip,
                                        "data" : {
                                            "customer_id" : i.id,
                                            "customer_name" : i.name,
                                            "customer_type" : i.type,
                                            "customer_nip" : i.nip,
                                            "customer_street" : i.street,
                                            "customer_house_no" : i.house_no,
                                            "customer_apartment_no" : i.apartment_no,
                                            "customer_zip" : i.zip,
                                            "customer_city" : i.city,
                                            "customer_country" : i.country
                                        }
                                    })
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
                this.usersService.list({size: 9999, first: 0})
                    .then(
                        (response) => {
                            this.loadingUsers = false
                            if (response.data.data.length) {
                                response.data.data.forEach((i) => {
                                    this.users.push({
                                        "id" : i.id,
                                        "name" : i.firstname + " " + i.lastname,
                                    })
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
                this.dictionaryService.listByType("payment_types", {size: 100, first: 0})
                    .then(
                        (response) => {
                            this.loadingPaymentDictionary = false
                            if (response.data.data.length) {
                                response.data.data.forEach((i) => {
                                    this.paymentTypes.push({"id" : i.id, "name" : i.name })
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
                if ((this.source == "new" || this.source == "proforma") && this.invoice.sale_register_id) {
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
            },
            
            selectCustomer(event) {
                if(event.value != undefined)
                {
                    let id = parseInt(event.value);
                    this.customers.every((c) => {
                        if (c.id == id) {
                            this.invoice.customer_id = c.id
                            this.invoice.customer_type = c.data.customer_type
                            this.invoice.customer_name = c.data.customer_name
                            this.invoice.customer_nip = c.data.customer_nip
                            this.invoice.customer_street = c.data.customer_street
                            this.invoice.customer_house_no = c.data.customer_house_no
                            this.invoice.customer_apartment_no = c.data.customer_apartment_no
                            this.invoice.customer_country = c.data.customer_country
                            this.invoice.customer_zip = c.data.customer_zip
                            this.invoice.customer_city = c.data.customer_city
                            
                            return false
                        }
                        return true
                    })
                }
                else
                    this.invoice.customer_id = null
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
                    <div class="field col-12 xl:col-6 mb-4">
                        <label for="number" v-required class="block text-900 font-medium mb-2">{{ $t('customer_invoices.number') }}</label>
                        <InputText id="number" type="text" :placeholder="$t('customer_invoices.number')" class="w-full" v-model="invoice.full_number" :disabled="true"/>
                    </div>
                    
                    <div class="field col-12 lg:col-6 xl:col-3 mb-4">
                        <label for="type" v-required class="block text-900 font-medium mb-2">{{ $t('customer_invoices.document_type') }}</label>
                        <Dropdown id="type" v-model="invoice.type" :options="types" optionLabel="name" :class="{'p-invalid' : v.invoice.type.$error}" optionValue="id" :placeholder="$t('customer_invoices.sale_register')" class="w-full" :disabled="saving || loading || source == 'edit' || source == 'proforma' || block"/>
                        <div v-if="v.invoice.type.$error">
                            <small class="p-error">{{ v.invoice.type.$errors[0].$message }}</small>
                        </div>
                    </div>
                    
                    <div class="field col-12 lg:col-6 xl:col-3 mb-4">
                        <label for="sale_register_id" v-required class="block text-900 font-medium mb-2">{{ $t('customer_invoices.sale_register') }}</label>
                        <Dropdown id="sale_register_id" v-model="invoice.sale_register_id" :loading="loadingSaleRegister" :options="saleRegistries" :class="{'p-invalid' : v.invoice.sale_register_id.$error}" optionLabel="name" optionValue="id" :placeholder="$t('customer_invoices.sale_register')" class="w-full" :disabled="saving || loading || source == 'edit' || block"/>
                        <div v-if="v.invoice.sale_register_id.$error">
                            <small class="p-error">{{ v.invoice.sale_register_id.$errors[0].$message }}</small>
                        </div>
                    </div>
                    
                    <div class="field col-12 sm:col-6 xl:col-4 mb-4">
                        <label for="documentDate" v-required class="block text-900 font-medium mb-2">{{ $t('customer_invoices.document_date') }}</label>
                        <Calendar id="documentDate" v-model="invoice.document_date" :placeholder="$t('customer_invoices.document_date')" :class="{'p-invalid' : v.invoice.document_date.$error}" showIcon :disabled="saving || loading || block"/>
                        <div v-if="v.invoice.document_date.$error">
                            <small class="p-error">{{ v.invoice.document_date.$errors[0].$message }}</small>
                        </div>
                    </div>
                    
                    <div class="field col-12 sm:col-6 xl:col-4 mb-4">
                        <label for="sellDate" v-required class="block text-900 font-medium mb-2">{{ $t('customer_invoices.sell_date') }}</label>
                        <Calendar id="sellDate" v-model="invoice.sell_date" :placeholder="$t('customer_invoices.sell_date')" :class="{'p-invalid' : v.invoice.sell_date.$error}" showIcon :disabled="saving || loading || block"/>
                        <div v-if="v.invoice.sell_date.$error">
                            <small class="p-error">{{ v.invoice.sell_date.$errors[0].$message }}</small>
                        </div>
                    </div>
                    
                    <div class="field col-12 sm:col-6 xl:col-4 mb-4">
                        <label for="paymentDate" v-required class="block text-900 font-medium mb-2">{{ $t('customer_invoices.payment_date') }}</label>
                        <Calendar id="paymentDate" v-model="invoice.payment_date" :placeholder="$t('customer_invoices.payment_date')" :class="{'p-invalid' : v.invoice.payment_date.$error}" showIcon :disabled="saving || loading || block"/>
                        <div v-if="v.invoice.payment_date.$error">
                            <small class="p-error">{{ v.invoice.payment_date.$errors[0].$message }}</small>
                        </div>
                    </div>
                    
                    <div class="field col-12 sm:col-6 md:col-6 mb-4">
                        <label for="payment_type_id" v-required class="block text-900 font-medium mb-2">{{ $t('customer_invoices.payment_type') }}</label>
                        <Dropdown id="payment_type_id" v-model="invoice.payment_type_id" :loading="loadingPaymentDictionary" :options="paymentTypes" :class="{'p-invalid' : v.invoice.payment_type_id.$error}" optionLabel="name" optionValue="id" :placeholder="$t('customer_invoices.payment_type')" class="w-full" :disabled="saving || loading || block"/>
                        <div v-if="v.invoice.payment_type_id.$error">
                            <small class="p-error">{{ v.invoice.payment_type_id.$errors[0].$message }}</small>
                        </div>
                    </div>
                    
                    <div class="field col-12 xl:col-6 mb-4">
                        <label for="created_user_id" v-required class="block text-900 font-medium mb-2">{{ $t('customer_invoices.issued') }}</label>
                        <Dropdown id="created_user_id" v-model="invoice.created_user_id" filter :loading="loadingUsers" :options="users" :class="{'p-invalid' : v.invoice.created_user_id.$error}" optionLabel="name" optionValue="id" :placeholder="$t('customer_invoices.issued')" class="w-full" :disabled="saving || loading || block"/>
                        <div v-if="v.invoice.created_user_id.$error">
                            <small class="p-error">{{ v.invoice.created_user_id.$errors[0].$message }}</small>
                        </div>
                    </div>
                    
                    <div class="field col-12 mb-4">
                        <label for="comment" class="block text-900 font-medium mb-2">{{ $t('customer_invoices.comment') }}</label>
                        <Textarea id="comment" type="text" :placeholder="$t('customer_invoices.comment')" rows="3" class="w-full" :class="{'p-invalid' : v.invoice.comment.$error}" v-model="invoice.comment" :disabled="loading || saving || block"/>
                        <div v-if="v.invoice.comment.$error">
                            <small class="p-error">{{ v.invoice.comment.$errors[0].$message }}</small>
                        </div>
                    </div>
                </div>
                
                <div class="formgrid grid ">
                    <div class="col-12">
                        <Divider align="left" class="mb-5">
                            {{ $t('customer_invoices.customer_data') }}
                        </Divider>
                    </div>
                </div>
                
                <div class="formgrid grid mb-3">
                    <div class="field col-12 md:col-12 mb-4">
                        <label for="customer_id" class="block text-900 font-medium mb-2">{{ $t('customer_invoices.customer') }}</label>
                        <Dropdown id="customer_id" v-model="invoice.customer_id" :showClear="invoice.customer_id ? true : false" filter :filterFields="['name','nip']" :loading="loadingCustomers" :options="customers" optionLabel="name" optionValue="id" :placeholder="$t('customer_invoices.customer')" class="w-full" @change="selectCustomer" :disabled="saving || loading || block">
                            <template #option="slotProps">
                                <div class="">
                                    <div>
                                        {{ slotProps.option.data.customer_name }}
                                    </div>
                                    <small class="font-italic text-gray-500">
                                        {{ getValueLabel('tenant_types', slotProps.option.data.customer_type) }}
                                        <span v-if="slotProps.option.data.customer_type=='firm'">
                                            : {{ slotProps.option.data.customer_nip }}
                                        </span>
                                    </small>
                                </div>
                            </template>
                        </Dropdown>
                    </div>
                    
                    <div class="field col-12 md:col-3 mb-4">
                        <label for="customer_type" v-required class="block text-900 font-medium mb-2">{{ $t('customer_invoices.customer_type') }}</label>
                        <Dropdown id="customer_type" v-model="invoice.customer_type" :options="customerTypes" optionLabel="name" optionValue="id" :placeholder="$t('customer_invoices.customer_type')" class="w-full" :disabled="saving || loading || block"/>
                        <div v-if="v.invoice.customer_type.$error">
                            <small class="p-error">{{ v.invoice.customer_type.$errors[0].$message }}</small>
                        </div>
                    </div>
                    
                    <div class="field col-12 mb-4" :class="[invoice.customer_type == 'firm' ? 'md:col-5' : 'md:col-9']">
                        <label for="customer_name" v-required class="block text-900 font-medium mb-2">{{ labelCustomerTypeName }}</label>
                        <InputText id="customer_name" type="text" :placeholder="$t('customer_invoices.customer_name')" class="w-full" :class="{'p-invalid' : v.invoice.customer_name.$error}" v-model="invoice.customer_name" :disabled="saving || loading || block"/>
                        <div v-if="v.invoice.customer_name.$error">
                            <small class="p-error">{{ v.invoice.customer_name.$errors[0].$message }}</small>
                        </div>
                    </div>
                    
                    <div class="field col-12 md:col-4 mb-4" v-if="invoice.customer_type == 'firm'">
                        <label for="customer_nip" v-required class="block text-900 font-medium mb-2">{{ $t('customer_invoices.customer_nip') }}</label>
                        <InputText id="customer_nip" type="text" :placeholder="$t('customer_invoices.customer_nip')" class="w-full" :class="{'p-invalid' : v.invoice.customer_nip.$error}" v-model="invoice.customer_nip" :disabled="saving || loading || block" />
                        <div v-if="v.invoice.customer_nip.$error">
                            <small class="p-error">{{ v.invoice.customer_nip.$errors[0].$message }}</small>
                        </div>
                    </div>
                    
                    <div class="field col-12 md:col-6 mb-4">
                        <label for="customer_street" v-required class="block text-900 font-medium mb-2">{{ $t('customer_invoices.customer_street') }}</label>
                        <InputText id="customer_street" type="text" :placeholder="$t('customer_invoices.customer_street')" class="w-full" :class="{'p-invalid' : v.invoice.customer_street.$error}" v-model="invoice.customer_street" :disabled="saving || loading || block" />
                        <div v-if="v.invoice.customer_street.$error">
                            <small class="p-error">{{ v.invoice.customer_street.$errors[0].$message }}</small>
                        </div>
                    </div>
                    
                    <div class="field col-12 md:col-3 sm:col-6 mb-4">
                        <label for="customer_house_no" class="block text-900 font-medium mb-2">{{ $t('customer_invoices.customer_house_no') }}</label>
                        <InputText id="customer_house_no" type="text" :placeholder="$t('customer_invoices.customer_house_no')" class="w-full" :class="{'p-invalid' : v.invoice.customer_house_no.$error}" v-model="invoice.customer_house_no" :disabled="saving || loading || block" />
                        <div v-if="v.invoice.customer_house_no.$error">
                            <small class="p-error">{{ v.invoice.customer_house_no.$errors[0].$message }}</small>
                        </div>
                    </div>
                    
                    <div class="field col-12 md:col-3 sm:col-6 mb-4">
                        <label for="customer_apartment_no" class="block text-900 font-medium mb-2">{{ $t('customer_invoices.customer_apartment_no') }}</label>
                        <InputText id="customer_apartment_no" type="text" :placeholder="$t('customer_invoices.customer_apartment_no')" class="w-full" :class="{'p-invalid' : v.invoice.customer_apartment_no.$error}" v-model="invoice.customer_apartment_no" :disabled="saving || loading || block" />
                        <div v-if="v.invoice.customer_apartment_no.$error">
                            <small class="p-error">{{ v.invoice.customer_apartment_no.$errors[0].$message }}</small>
                        </div>
                    </div>
                    
                    <div class="field col-12 md:col-4 sm:col-12 mb-4">
                        <label for="customer_country" v-required class="block text-900 font-medium mb-2">{{ $t('customer_invoices.customer_country') }}</label>
                        <Dropdown id="customer_country" v-model="invoice.customer_country" filter :options="countries" optionLabel="name" optionValue="code" :placeholder="$t('customer_invoices.select_country')" class="w-full" :disabled="saving || loading || block"/>
                    </div>
                    
                    <div class="field col-12 md:col-3 sm:col-4 mb-4">
                        <label for="customer_zip" v-required class="block text-900 font-medium mb-2">{{ $t('customer_invoices.customer_zip') }}</label>
                        <InputText id="customer_zip" type="text" :placeholder="$t('customer_invoices.customer_zip')" class="w-full" :class="{'p-invalid' : v.invoice.customer_zip.$error}" v-model="invoice.customer_zip" :disabled="saving || loading || block" />
                        <div v-if="v.invoice.customer_zip.$error">
                            <small class="p-error">{{ v.invoice.customer_zip.$errors[0].$message }}</small>
                        </div>
                    </div>
                    
                    <div class="field col-12 md:col-5 sm:col-8 mb-4">
                        <label for="customer_city" v-required class="block text-900 font-medium mb-2">{{ $t('customer_invoices.customer_city') }}</label>
                        <InputText id="customer_city" type="text" :placeholder="$t('customer_invoices.customer_city')" class="w-full" :class="{'p-invalid' : v.invoice.customer_city.$error}" v-model="invoice.customer_city" :disabled="saving || loading || block"/>
                        <div v-if="v.invoice.customer_city.$error">
                            <small class="p-error">{{ v.invoice.customer_city.$errors[0].$message }}</small>
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