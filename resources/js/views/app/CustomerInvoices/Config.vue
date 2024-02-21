<script>
    import { useVuelidate } from '@vuelidate/core'
    import { getResponseErrors, setMetaTitle } from '@/utils/helper'
    import { required, requiredIf } from '@/utils/i18n-validators'
    import countries from '@/data/countries.json'
    
    import UserInvoiceService from '@/service/UserInvoiceService'
    
    export default {
        setup() {
            setMetaTitle('meta.title.firm_data')
            
            const userInvoiceService = new UserInvoiceService();
            
            return {
                v$: useVuelidate(),
                userInvoiceService,
            }
        },
        data() {
            return {
                saving: false,
                loading: true,
                errors: [],
                invoicedata: {
                    type: 'person'
                },
                useInvoiceFirmData : false,
                countries: countries[this.$i18n.locale],
                accountTypes: [
                    {
                        "value" : "firm",
                        "name" : this.$t('profile.invoice_account_firm')
                    },
                    {
                        "value" : "person",
                        "name" : this.$t('profile.invoice_account_person')
                    }
                ],
                meta: {
                    breadcrumbItems: [
                        {'label' : this.$t('menu.settlements'), disabled : true },
                        {'label' : this.$t('menu.customer_invoices_config'), disabled : true },
                    ]
                }
            }
        },
        beforeMount() {
            this.userInvoiceService.getInvoiceData()
                .then(
                    (response) => {
                        if (response.data)
                        {
                            if (response.data.data != undefined && response.data.data.length)
                                this.invoicedata = response.data.data
                            this.useInvoiceFirmData = response.data.use_invoice_firm_data
                        }
                        this.loading = false
                    },
                    (response) => {
                        this.$toast.add({ severity: 'error', summary: this.$t('app.error'), detail: response.response.data.message, life: 3000 });
                    },
                )
        },
        methods: {
            async updateInvoiceData() {
                const result = await this.v$.$validate()
                if (result) {
                    this.saving = true
                    this.errors = []
                    
                    this.invoicedata.use_invoice_firm_data = this.useInvoiceFirmData
                    
                    this.userInvoiceService.updateInvoiceData(this.invoicedata)
                        .then(
                            (response) => {
                                this.$toast.add({ severity: 'success', summary: this.$t('app.success'), detail: this.$t('profile.invoice_data_updated'), life: 3000 });
                                this.saving = false;
                            },
                            (response) => {
                                this.$toast.add({ severity: 'error', summary: this.$t('app.form_error_title'), detail: this.$t('app.form_error_message'), life: 3000 });
                                this.errors = getResponseErrors(response)
                                this.saving = false
                            }
                    )
                } else
                    this.$toast.add({ severity: 'error', summary: this.$t('app.form_error_title'), detail: this.$t('app.form_error_message'), life: 3000 });
            },
        },
        validations () {
            return {
                invoicedata: {
                    type: { required },
                    firstname: { required: requiredIf(function() { return this.invoicedata.type == 'person' }) },
                    lastname: { required: requiredIf(function() { return this.invoicedata.type == 'person' }) },
                    nip: { required: requiredIf(function() { return this.invoicedata.type == 'firm' }) },
                    name: { required: requiredIf(function() { return this.invoicedata.type == 'firm' }) },
                    country: { required },
                    street: { required },
                    house_no: { required },
                    zip: { required },
                    city: { required },
                }
            }
        },
    }
</script>

<template>
    <Breadcrumb :model="meta.breadcrumbItems"/>
    <div class="card p-fluid mt-4 pt-4">
        <Help show="settlement" mark="settlement:invoice" class="text-right mb-3"/>
        <h4 class="mb-5 header-border-bottom pb-2 text-color">{{ $t('menu.customer_invoices_config') }}</h4>
        <form v-on:submit.prevent="updateInvoiceData" class="sticky-footer-form">
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
                        <div class="field col-12 mb-4">
                            <div class="flex align-items-center">
                                <Checkbox inputId="useInvoiceFirmData" v-model="useInvoiceFirmData" :binary="true" />
                                <label for="useInvoiceFirmData" class="ml-2">
                                    {{ $t('customer_invoices.use_invoice_firm_data_info_1') }}
                                    <router-link :to="{name: 'firm_data'}" target="_blank">
                                        {{ $t('customer_invoices.use_invoice_firm_data_info_link') }}
                                    </router-link>
                                    {{ $t('customer_invoices.use_invoice_firm_data_info_2') }}
                                </label>
                            </div>
                        </div>
                            
                        <template v-if="!useInvoiceFirmData">
                            <div class="field col-12 sm:col-4 mb-4">
                                <label for="type" v-required class="block text-900 font-medium mb-2">{{ $t('profile.invoice_account_type') }}</label>
                                <Dropdown id="type" v-model="invoicedata.type" :options="accountTypes" optionLabel="name" optionValue="value" :placeholder="$t('profile.select_invoice_account_type')" class="w-full" :disabled="loading || saving"/>
                                <div v-if="v$.invoicedata.type.$error">
                                    <small class="p-error">{{ v$.invoicedata.type.$errors[0].$message }}</small>
                                </div>
                            </div>
                            <div class="field col-12 sm:col-4 mb-4" v-if="invoicedata.type == 'person'">
                                <label for="invoice_firstname" v-required class="block text-900 font-medium mb-2">{{ $t('profile.firstname') }}</label>
                                <InputText id="invoice_firstname" type="text" :placeholder="$t('profile.firstname')" :class="{'p-invalid' : v$.invoicedata.firstname.$error}" class="w-full" v-model="invoicedata.firstname" :disabled="loading || saving"/>
                                <div v-if="v$.invoicedata.firstname.$error">
                                    <small class="p-error">{{ v$.invoicedata.firstname.$errors[0].$message }}</small>
                                </div>
                            </div>
                            <div class="field col-12 sm:col-4 mb-4" v-if="invoicedata.type == 'person'">
                                <label for="invoice_lastname" v-required class="block text-900 font-medium mb-2">{{ $t('profile.lastname') }}</label>
                                <InputText id="invoice_lastname" type="text" :placeholder="$t('profile.lastname')" :class="{'p-invalid' : v$.invoicedata.lastname.$error}" class="w-full" v-model="invoicedata.lastname" :disabled="loading || saving"/>
                                <div v-if="v$.invoicedata.lastname.$error">
                                    <small class="p-error">{{ v$.invoicedata.lastname.$errors[0].$message }}</small>
                                </div>
                            </div>
                            <div class="field col-12 sm:col-4 mb-4" v-if="invoicedata.type == 'firm'">
                                <label for="invoice_nip" v-required class="block text-900 font-medium mb-2">{{ $t('profile.nip') }}</label>
                                <InputText id="invoice_nip" type="text" :placeholder="$t('profile.nip')" :class="{'p-invalid' : v$.invoicedata.nip.$error}" class="w-full" v-model="invoicedata.nip" :disabled="loading || saving"/>
                                <div v-if="v$.invoicedata.nip.$error">
                                    <small class="p-error">{{ v$.invoicedata.nip.$errors[0].$message }}</small>
                                </div>
                            </div>
                            <div class="field col-12 sm:col-4 mb-4" v-if="invoicedata.type == 'firm'">
                                <label for="invoice_name" v-required class="block text-900 font-medium mb-2">{{ $t('profile.firm_name') }}</label>
                                <InputText id="invoice_name" type="text" :placeholder="$t('profile.firm_name')" :class="{'p-invalid' : v$.invoicedata.name.$error}" class="w-full" v-model="invoicedata.name" :disabled="loading || saving"/>
                                <div v-if="v$.invoicedata.name.$error">
                                    <small class="p-error">{{ v$.invoicedata.name.$errors[0].$message }}</small>
                                </div>
                            </div>
                            <div class="field col-12 md:col-6 mb-4">
                                <label for="invoice_street" v-required class="block text-900 font-medium mb-2">{{ $t('profile.street') }}</label>
                                <InputText id="invoice_street" type="text" :placeholder="$t('profile.street')" :class="{'p-invalid' : v$.invoicedata.street.$error}" class="w-full" v-model="invoicedata.street" :disabled="loading || saving"/>
                                <div v-if="v$.invoicedata.street.$error">
                                    <small class="p-error">{{ v$.invoicedata.street.$errors[0].$message }}</small>
                                </div>
                            </div>
                            <div class="field col-12 md:col-3 sm:col-6 mb-4">
                                <label for="invoice_house_no" v-required class="block text-900 font-medium mb-2">{{ $t('profile.house_no') }}</label>
                                <InputText id="invoice_house_no" type="text" :placeholder="$t('profile.house_no')" :class="{'p-invalid' : v$.invoicedata.house_no.$error}" class="w-full" v-model="invoicedata.house_no" :disabled="loading || saving"/>
                                <div v-if="v$.invoicedata.house_no.$error">
                                    <small class="p-error">{{ v$.invoicedata.house_no.$errors[0].$message }}</small>
                                </div>
                            </div>
                            <div class="field col-12 md:col-3 sm:col-6 mb-4">
                                <label for="invoice_apartment_no" class="block text-900 font-medium mb-2">{{ $t('profile.apartment_no') }}</label>
                                <InputText id="invoice_apartment_no" type="text" :placeholder="$t('profile.apartment_no')" class="w-full" v-model="invoicedata.apartment_no" :disabled="loading || saving"/>
                            </div>
                            <div class="field col-12 md:col-4 sm:col-12 mb-4">
                                <label for="invoice_country" v-required class="block text-900 font-medium mb-2">{{ $t('profile.country') }}</label>
                                <Dropdown id="invoice_country" v-model="invoicedata.country" filter :options="countries" optionLabel="name" optionValue="code" :class="{'p-invalid' : v$.invoicedata.country.$error}" :placeholder="$t('profile.select_country')" class="w-full" :disabled="loading || saving"/>
                                <div v-if="v$.invoicedata.country.$error">
                                    <small class="p-error">{{ v$.invoicedata.country.$errors[0].$message }}</small>
                                </div>
                            </div>
                            <div class="field col-12 md:col-3 sm:col-4 mb-4">
                                <label for="invoice_zip" v-required class="block text-900 font-medium mb-2">{{ $t('profile.zip') }}</label>
                                <InputText id="invoice_zip" type="text" :placeholder="$t('profile.zip')" :class="{'p-invalid' : v$.invoicedata.zip.$error}" class="w-full" v-model="invoicedata.zip" :disabled="loading || saving"/>
                                <div v-if="v$.invoicedata.zip.$error">
                                    <small class="p-error">{{ v$.invoicedata.zip.$errors[0].$message }}</small>
                                </div>
                            </div>
                            
                            <div class="field col-12 md:col-5 sm:col-8 mb-4">
                                <label for="invoice_city" v-required  class="block text-900 font-medium mb-2">{{ $t('profile.city') }}</label>
                                <InputText id="invoice_city" type="text" :placeholder="$t('profile.city')" :class="{'p-invalid' : v$.invoicedata.city.$error}" class="w-full" v-model="invoicedata.city" :disabled="loading || saving"/>
                                <div v-if="v$.invoicedata.city.$error">
                                    <small class="p-error">{{ v$.invoicedata.city.$errors[0].$message }}</small>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
            
            <div class="form-footer">
                <div class="text-right">
                    <Button type="submit" :label="$t('app.save')" v-if="!loading" :loading="saving" iconPos="right" icon="pi pi-save" class="w-auto text-center"></Button>
                </div>
            </div>
        </form>
    </div>
</template>