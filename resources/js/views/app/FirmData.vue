<script>
    import { ref } from 'vue'
    import { useVuelidate } from '@vuelidate/core'
    import { getResponseErrors, setMetaTitle } from '@/utils/helper'
    import { required, requiredIf, email } from '@/utils/i18n-validators'
    import countries from '@/data/countries.json'
    
    import UserService from '@/service/UserService'
    
    export default {
        setup() {
            setMetaTitle('meta.title.firm_data')
            
            const userService = new UserService();
            
            return {
                v$: useVuelidate(),
                userService,
            }
        },
        data() {
            return {
                saving: false,
                loading: true,
                currentTabIndex: 0,
                errors: [],
                firmdata: {},
                invoicedata: {
                    type: 'person'
                },
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
                        {'label' : this.$t('menu.firm_data'), disabled : true}
                    ]
                }
            }
        },
        beforeMount() {
            this.userService.firmData()
                .then(
                    (response) => {
                        this.firmdata = response.data
                        this.loading = false
                    },
                    (response) => {
                        this.$toast.add({ severity: 'error', summary: this.$t('app.error'), detail: response.response.data.message, life: 3000 });
                    },
                )
                
            this.userService.invoiceData()
                .then(
                    (response) => {
                        if (response.data)
                            this.invoicedata = response.data
                        this.loading = false
                    },
                    (response) => {
                        this.$toast.add({ severity: 'error', summary: this.$t('app.error'), detail: response.response.data.message, life: 3000 });
                    },
                )
        },
        methods: {
            async updateFirmData() {
                const result = await this.v$.$validate()
                if (result) {
                    this.saving = true
                    this.errors = []
                    this.userService.updateFirmData(this.firmdata)
                        .then(
                            (response) => {
                                this.$toast.add({ severity: 'success', summary: this.$t('app.success'), detail: this.$t('profile.firm_data_updated'), life: 3000 });
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
            
            async updateInvoiceData() {
                const result = await this.v$.$validate()
                if (result) {
                    this.saving = true
                    this.errors = []
                    this.userService.updateInvoiceData(this.invoicedata)
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
            
            onTabChange(event) {
                this.currentTabIndex = event.index
                this.errors = []
                this.v$.$reset()
            }
        },
        validations () {
            return {
                firmdata: {
                    firstname: { required: requiredIf(function() { return this.currentTabIndex == 0 }) },
                    lastname: { required: requiredIf(function() { return this.currentTabIndex == 0 }) },
                    email: { required: requiredIf(function() { return this.currentTabIndex == 0 }), email },
                    name: { required: requiredIf(function() { return this.currentTabIndex == 0 }) },
                },
                invoicedata: {
                    type: { required: requiredIf(function() { return this.currentTabIndex == 1 }) },
                    firstname: { required: requiredIf(function() { return this.invoicedata.type == 'person' && this.currentTabIndex == 1 }) },
                    lastname: { required: requiredIf(function() { return this.invoicedata.type == 'person' && this.currentTabIndex == 1 }) },
                    nip: { required: requiredIf(function() { return this.invoicedata.type == 'firm' && this.currentTabIndex == 1 }) },
                    name: { required: requiredIf(function() { return this.invoicedata.type == 'firm' && this.currentTabIndex == 1 }) },
                    country: { required: requiredIf(function() { return this.currentTabIndex == 1 }) },
                    street: { required: requiredIf(function() { return this.currentTabIndex == 1 }) },
                    house_no: { required: requiredIf(function() { return this.currentTabIndex == 1 }) },
                    zip: { required: requiredIf(function() { return this.currentTabIndex == 1 }) },
                    city: { required: requiredIf(function() { return this.currentTabIndex == 1 }) },
                }
            }
        },
    }
</script>

<template>
    <Breadcrumb :model="meta.breadcrumbItems"/>
    <TabView @tab-change="onTabChange">
        <TabPanel :header="$t('profile.firm_data')">
            <form v-on:submit.prevent="updateFirmData" class="sticky-footer-form">
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
                            <div class="field col-12 md:col-4 sm:col-6 mb-4">
                                <label for="firstname" v-required class="block text-900 font-medium mb-2">{{ $t('profile.firstname') }}</label>
                                <InputText id="firstname" type="text" :placeholder="$t('profile.firstname')" class="w-full" :class="{'p-invalid' : v$.firmdata.firstname.$error}" v-model="firmdata.firstname" :disabled="loading || saving"/>
                                <div v-if="v$.firmdata.firstname.$error">
                                    <small class="p-error">{{ v$.firmdata.firstname.$errors[0].$message }}</small>
                                </div>
                            </div>
                            
                            <div class="field col-12 md:col-4 sm:col-6 mb-4">
                                <label for="lastname" v-required class="block text-900 font-medium mb-2">{{ $t('profile.lastname') }}</label>
                                <InputText id="lastname" type="text" :placeholder="$t('profile.lastname')" class="w-full" :class="{'p-invalid' : v$.firmdata.lastname.$error}" v-model="firmdata.lastname" :disabled="loading || saving"/>
                                <div v-if="v$.firmdata.lastname.$error">
                                    <small class="p-error">{{ v$.firmdata.lastname.$errors[0].$message }}</small>
                                </div>
                            </div>
                            
                            <div class="field col-12 md:col-4 sm:col-6 mb-4">
                                <label for="name" v-required class="block text-900 font-medium mb-2">{{ $t('profile.firm_name') }}</label>
                                <InputText id="name" type="text" :placeholder="$t('profile.firm_name')" :class="{'p-invalid' : v$.firmdata.name.$error}" class="w-full" v-model="firmdata.name" :disabled="loading || saving"/>
                                <div v-if="v$.firmdata.name.$error">
                                    <small class="p-error">{{ v$.firmdata.name.$errors[0].$message }}</small>
                                </div>
                            </div>
                            
                            <div class="field col-12 md:col-6 mb-4">
                                <label for="email" v-required class="block text-900 font-medium mb-2">{{ $t('profile.email') }}</label>
                                <InputText id="email" type="text" :placeholder="$t('profile.email')" class="w-full" :class="{'p-invalid' : v$.firmdata.email.$error}" v-model="firmdata.email" :disabled="loading || saving"/>
                                <div v-if="v$.firmdata.email.$error">
                                    <small class="p-error">{{ v$.firmdata.email.$errors[0].$message }}</small>
                                </div>
                            </div>
                            
                            <div class="field col-12 md:col-6 mb-4">
                                <label for="phone" class="block text-900 font-medium mb-2">{{ $t('profile.phone') }}</label>
                                <InputText id="phone" type="text" :placeholder="$t('profile.phone')" class="w-full" v-model="firmdata.phone" :disabled="loading || saving"/>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="form-footer">
                    <div class="text-right">
                        <Button type="submit" :label="$t('app.save')" v-if="!loading" :loading="saving" iconPos="right" icon="pi pi-save" class="w-auto text-center"></Button>
                    </div>
                </div>
            </form>
        </TabPanel>
        <TabPanel :header="$t('profile.invoice_data')">
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
                        </div>
                    </div>
                </div>
                
                <div class="form-footer">
                    <div class="text-right">
                        <Button type="submit" :label="$t('app.save')" v-if="!loading" :loading="saving" iconPos="right" icon="pi pi-save" class="w-auto text-center"></Button>
                    </div>
                </div>
            </form>
        </TabPanel>
    </TabView>
    
</template>